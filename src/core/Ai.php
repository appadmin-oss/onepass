<?php

/**
 * Ai — multi-provider AI client.
 *
 * We run TWO free, key-less text providers in parallel and take the
 * first usable response. This gives us redundancy (if one upstream
 * is down or rate-limiting, the other still answers) and slightly
 * lower median latency than a serial fallback.
 *
 *   1. Pollinations.ai   — POST https://text.pollinations.ai/
 *   2. Hack Club AI      — POST https://ai.hackclub.com/chat/completions
 *
 * Both speak an OpenAI-style /messages payload (or a tolerant variant)
 * and return plain text we can hand to the caller. Set
 *   AI_ENABLED=0          → disable AI everywhere
 *   AI_PRIMARY=hackclub   → flip the preferred provider (default: pollinations)
 *   AI_RACE=0             → fall back to serial-with-fallback instead of racing
 */
class Ai {
    private const PROV_POLLINATIONS = 'pollinations';
    private const PROV_HACKCLUB     = 'hackclub';

    public static function enabled(): bool {
        $env = getenv('AI_ENABLED');
        if ($env === '0' || $env === 'false') return false;
        return true;
    }

    public static function providers(): array {
        return [self::PROV_POLLINATIONS, self::PROV_HACKCLUB];
    }

    private static function primary(): string {
        $p = getenv('AI_PRIMARY') ?: self::PROV_POLLINATIONS;
        return in_array($p, self::providers(), true) ? $p : self::PROV_POLLINATIONS;
    }

    private static function racing(): bool {
        $env = getenv('AI_RACE');
        if ($env === '0' || $env === 'false') return false;
        return true;
    }

    /**
     * Run a chat-style completion. $messages is an array of
     * ['role' => 'system'|'user'|'assistant', 'content' => '...']
     * Returns ['ok' => bool, 'text' => string, 'error' => ?string,
     *         'provider' => string].
     */
    public static function chat(array $messages, array $opts = []): array {
        if (!self::enabled()) {
            return ['ok' => false, 'text' => '', 'error' => 'ai_disabled', 'provider' => ''];
        }
        if (!self::checkRateLimit()) {
            return ['ok' => false, 'text' => '', 'error' => 'rate_limited',
                    'provider' => '',
                    'message' => 'Slow down — you can ask again in a minute.'];
        }

        if (self::racing()) {
            return self::race($messages, $opts);
        }
        $order = self::primary() === self::PROV_HACKCLUB
            ? [self::PROV_HACKCLUB, self::PROV_POLLINATIONS]
            : [self::PROV_POLLINATIONS, self::PROV_HACKCLUB];
        foreach ($order as $prov) {
            $r = self::callProvider($prov, $messages, $opts);
            if ($r['ok']) return $r;
        }
        return ['ok' => false, 'text' => '', 'error' => 'upstream',
                'provider' => '',
                'message' => 'AI service is busy — try again in a moment.'];
    }

    /** Convenience: single-turn prompt with optional system instruction. */
    public static function ask(string $prompt, ?string $system = null, array $opts = []): array {
        $messages = [];
        if ($system) $messages[] = ['role' => 'system', 'content' => $system];
        $messages[] = ['role' => 'user', 'content' => $prompt];
        return self::chat($messages, $opts);
    }

    /**
     * The single entry point every caller should use going forward.
     *
     * Composes the cache + budget + log layers around the existing
     * chat() race. The returned shape mirrors chat() so a refactor is
     * a one-line replacement at every callsite (search for "Ai::ask" or
     * "Ai::chat" outside this class — every match becomes "Ai::run").
     *
     * Resolution order:
     *   1. Resolve intent from AiRegistry (system prompt + cap + policy)
     *   2. Honour the per-intent context cap; reject anything bigger
     *   3. If cacheable + cache hit → return immediately (logged as cached)
     *   4. AiBudget::allow() check → short-circuit with {error: 'budget'}
     *   5. Call chat() with the composed system + user pair
     *   6. On success: cache (if cacheable + not PII) + record budget
     *   7. Always: log one NDJSON line
     *   8. Return {ok, text, provider, cached, error?}
     */
    public static function run(string $intent, string $context, array $opts = []): array {
        $cfg     = AiRegistry::resolve($intent);
        $context = trim($context);
        $bytesIn = strlen($context);

        // Hard reject if context exceeds the per-intent cap.
        if ($context === '' || $bytesIn > $cfg['cap']) {
            AiLog::record([
                'intent' => $cfg['intent'], 'ok' => false,
                'bytes_in' => $bytesIn, 'error' => 'bad_input',
                'budget_remaining' => AiBudget::remaining(),
            ]);
            return [
                'ok' => false, 'text' => $cfg['fallback'],
                'error' => 'bad_input', 'fallback' => $cfg['fallback'],
            ];
        }

        // Cache hit (only cacheable + non-PII intents are ever stored).
        if ($cfg['cacheable'] && !$cfg['pii']) {
            $hit = AiCache::get($cfg['intent'], $context);
            if ($hit && $hit['ok']) {
                AiLog::record([
                    'intent' => $cfg['intent'], 'ok' => true, 'cached' => true,
                    'bytes_in' => $bytesIn, 'bytes_out' => strlen($hit['text']),
                    'provider' => 'cache',
                    'budget_remaining' => AiBudget::remaining(),
                ]);
                return [
                    'ok' => true, 'text' => $hit['text'],
                    'provider' => 'cache', 'cached' => true,
                    'fallback' => $cfg['fallback'],
                ];
            }
        }

        // Budget gate. Note: we check BEFORE the upstream call, so a
        // tripped budget never even reaches Pollinations / Hack Club.
        if (!AiBudget::allow()) {
            AiLog::record([
                'intent' => $cfg['intent'], 'ok' => false,
                'bytes_in' => $bytesIn, 'error' => 'budget',
                'budget_remaining' => 0,
            ]);
            return [
                'ok' => false, 'text' => $cfg['fallback'],
                'error' => 'budget', 'fallback' => $cfg['fallback'],
            ];
        }

        // Upstream call. Race lives inside chat().
        $t0  = microtime(true);
        $res = self::ask($context, $cfg['system'], $opts);
        $ms  = (int) round((microtime(true) - $t0) * 1000);

        AiBudget::record();
        AiLog::record([
            'intent'   => $cfg['intent'],
            'provider' => $res['provider'] ?? '',
            'ms'       => $ms,
            'ok'       => (bool)$res['ok'],
            'cached'   => false,
            'bytes_in' => $bytesIn,
            'bytes_out'=> isset($res['text']) ? strlen((string)$res['text']) : 0,
            'error'    => $res['ok'] ? null : ($res['error'] ?? 'upstream'),
            'budget_remaining' => AiBudget::remaining(),
        ]);

        if ($res['ok']) {
            // Strip the surrounding quotes some models leave around answers.
            $text = trim((string)$res['text']);
            $text = preg_replace('/^["\'""\']+|["\'""\']+$/u', '', $text) ?? $text;
            $res['text'] = $text;
            if ($cfg['cacheable'] && !$cfg['pii']) {
                AiCache::put($cfg['intent'], $context, $text, $cfg['ttl']);
            }
        } else {
            // Always carry the fallback in the response so the UI can
            // render the canned line without a second look-up.
            $res['fallback'] = $cfg['fallback'];
            if (($res['text'] ?? '') === '') $res['text'] = $cfg['fallback'];
        }
        $res['cached'] = false;
        return $res;
    }

    /** Brand system prompt used for almost every AI call across the site. */
    public static function brandSystem(): string {
        return "You are Afrostrength's in-house writing & UX assistant. "
             . "Voice: direct, warm, confident; African business excellence; "
             . "uses sentence case; never marketing-speak. Brand tagline: "
             . "'Building Brands, Strengthening Legacies.' "
             . "Studio in Lagos (CACENTRE Egbeda + CACENTRE Ayobo). "
             . "Avoid emojis. Keep replies concise unless the user asks for length.";
    }

    /** Light status report for the health endpoint and admin dashboard. */
    public static function status(): array {
        return [
            'enabled'   => self::enabled(),
            'racing'    => self::racing(),
            'primary'   => self::primary(),
            'providers' => array_map(fn($p) => [
                'name'     => $p,
                'endpoint' => self::endpointFor($p),
            ], self::providers()),
        ];
    }

    /**
     * Hard round-trip check — actually pings the AI providers with a one-token
     * prompt and reports which one(s) answered. Used by /api/health/ai?deep=1
     * to verify the integration end-to-end rather than just "config looks ok".
     * Bypasses the session rate-limit so an unattended monitor can poll.
     */
    public static function selfTest(): array {
        if (!self::enabled()) {
            return ['ok' => false, 'detail' => 'AI_ENABLED is off.', 'providers' => []];
        }
        $messages = [
            ['role' => 'system', 'content' => 'Reply with one word: ping.'],
            ['role' => 'user',   'content' => 'ping?'],
        ];
        $results = [];
        foreach (self::providers() as $prov) {
            $t0  = microtime(true);
            $res = self::callProvider($prov, $messages, []);
            $ms  = (int) round((microtime(true) - $t0) * 1000);
            $results[] = [
                'name'    => $prov,
                'ok'      => (bool)$res['ok'],
                'ms'      => $ms,
                'detail'  => $res['ok']
                    ? trim(mb_substr($res['text'], 0, 40))
                    : ($res['error'] ?? 'unknown'),
            ];
        }
        $okCount = count(array_filter($results, fn($r) => $r['ok']));
        return [
            'ok'        => $okCount > 0,
            'detail'    => $okCount === count($results)
                ? 'All providers answered.'
                : ($okCount === 0 ? 'No provider answered.' : 'Mixed: ' . $okCount . ' / ' . count($results) . ' answered.'),
            'providers' => $results,
        ];
    }

    // ----- Provider plumbing ----------------------------------------

    private static function endpointFor(string $prov): string {
        return $prov === self::PROV_HACKCLUB
            ? 'https://ai.hackclub.com/chat/completions'
            : 'https://text.pollinations.ai/';
    }

    private static function buildHandle(string $prov, array $messages, array $opts) {
        $endpoint = self::endpointFor($prov);
        if ($prov === self::PROV_HACKCLUB) {
            $payload = ['messages' => $messages];
            if (!empty($opts['model'])) $payload['model'] = $opts['model'];
            if (!empty($opts['json']))  $payload['response_format'] = ['type' => 'json_object'];
        } else {
            $payload = [
                'model'    => $opts['model']    ?? 'openai',
                'seed'     => $opts['seed']     ?? null,
                'jsonMode' => $opts['json']     ?? false,
                'private'  => true,
                'messages' => $messages,
            ];
        }
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: text/plain, application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        return $ch;
    }

    private static function parseResponse(string $prov, $body, int $code, array $opts): array {
        if ($body === false || $code >= 400) {
            return ['ok' => false, 'text' => '', 'error' => 'upstream',
                    'provider' => $prov,
                    'message' => 'AI service is busy — try again in a moment.'];
        }
        $body = (string)$body;
        if ($prov === self::PROV_HACKCLUB) {
            $parsed = json_decode($body, true);
            $text = $parsed['choices'][0]['message']['content'] ?? '';
            if (!is_string($text) || trim($text) === '') {
                return ['ok' => false, 'text' => '', 'error' => 'empty', 'provider' => $prov];
            }
            return ['ok' => true, 'text' => trim($text), 'provider' => $prov];
        }
        $text = trim($body);
        if (!empty($opts['json'])) {
            $parsed = json_decode($text, true);
            if (is_array($parsed)) $text = $parsed['text'] ?? json_encode($parsed);
        }
        if ($text === '') {
            return ['ok' => false, 'text' => '', 'error' => 'empty', 'provider' => $prov];
        }
        return ['ok' => true, 'text' => $text, 'provider' => $prov];
    }

    private static function callProvider(string $prov, array $messages, array $opts): array {
        $ch = self::buildHandle($prov, $messages, $opts);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($body === false || $code >= 400) {
            error_log('[ai:' . $prov . '] http ' . $code . ' ' . substr((string)$err . (string)$body, 0, 300));
        }
        return self::parseResponse($prov, $body, (int)$code, $opts);
    }

    /**
     * Race both providers; return the first usable response, abandon the
     * slower. Uses curl_multi so both sockets are in flight simultaneously.
     */
    private static function race(array $messages, array $opts): array {
        $mh = curl_multi_init();
        $handles = [];
        foreach (self::providers() as $prov) {
            $ch = self::buildHandle($prov, $messages, $opts);
            curl_multi_add_handle($mh, $ch);
            $handles[(int)$ch] = ['prov' => $prov, 'ch' => $ch];
        }
        $winner = null;
        $errors = [];
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) curl_multi_select($mh, 0.5);
            while ($info = curl_multi_info_read($mh)) {
                $ch  = $info['handle'];
                $key = (int)$ch;
                $meta = $handles[$key] ?? null;
                if (!$meta) continue;
                $prov   = $meta['prov'];
                $body   = curl_multi_getcontent($ch);
                $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err    = curl_error($ch);
                $parsed = self::parseResponse($prov, $body, (int)$code, $opts);
                if ($parsed['ok'] && $winner === null) {
                    $winner = $parsed;
                } elseif (!$parsed['ok']) {
                    $errors[$prov] = $err ?: ($parsed['error'] ?? 'unknown');
                }
            }
            if ($winner !== null && $running > 0) {
                foreach ($handles as $h) {
                    @curl_multi_remove_handle($mh, $h['ch']);
                    @curl_close($h['ch']);
                }
                break;
            }
        } while ($running > 0 && $status === CURLM_OK);

        foreach ($handles as $h) {
            @curl_multi_remove_handle($mh, $h['ch']);
            @curl_close($h['ch']);
        }
        curl_multi_close($mh);

        if ($winner) return $winner;
        error_log('[ai:race] both providers failed: ' . json_encode($errors));
        return ['ok' => false, 'text' => '', 'error' => 'upstream',
                'provider' => '',
                'message' => 'AI service is busy — try again in a moment.'];
    }

    /** Per-session soft rate limit: 30 requests per 5 minutes. */
    private static function checkRateLimit(): bool {
        $now = time();
        $win = $now - 300;
        if (!isset($_SESSION['ai_calls'])) $_SESSION['ai_calls'] = [];
        $_SESSION['ai_calls'] = array_values(array_filter(
            $_SESSION['ai_calls'],
            fn($t) => $t >= $win
        ));
        if (count($_SESSION['ai_calls']) >= 30) return false;
        $_SESSION['ai_calls'][] = $now;
        return true;
    }
}
