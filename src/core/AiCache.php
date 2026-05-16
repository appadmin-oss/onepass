<?php

/**
 * AiCache — file-backed cache for deterministic AI intents.
 *
 * The cost-savings story: AiRegistry marks `cacheable` intents (slug
 * meta, career-path, promo titles, postmortem drafts, faq_search,
 * verify_plain, status_translate). For those, the same context yields
 * the same answer and there's no reason to pay an upstream provider
 * twice in 24 hours.
 *
 * Key shape:    sha256(intent . "\0" . normalised_context)
 * Storage:      one small JSON file per key under storage/aicache/
 * TTL:          per-intent, from the registry (default 24h)
 * Trim policy:  on write, if the cache directory has > MAX_ENTRIES
 *               files, delete the oldest by mtime (FIFO).
 *
 * Crucially, this is a STORAGE layer, not a policy layer. PII-bearing
 * intents are filtered out by Ai::run() BEFORE this code is reached —
 * we never even consider caching them. So this class can stay simple.
 */
class AiCache {
    private const MAX_ENTRIES = 5000;

    /** Returns ['ok'=>true,'text'=>string] on hit, null on miss/expired. */
    public static function get(string $intent, string $context): ?array {
        $path = self::path($intent, $context);
        if (!is_file($path)) return null;
        $raw = @file_get_contents($path);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['exp']) || empty($data['text'])) return null;
        if ((int)$data['exp'] < time()) {
            @unlink($path); // expired — clean up lazily
            return null;
        }
        return ['ok' => true, 'text' => (string)$data['text']];
    }

    /** Persist a response. Cheap on its own; trim runs ~1 in 100 writes. */
    public static function put(string $intent, string $context, string $text, int $ttl): void {
        if ($text === '' || $ttl <= 0) return;
        $path = self::path($intent, $context);
        $dir  = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents(
            $path,
            json_encode([
                'intent' => $intent,
                'exp'    => time() + $ttl,
                'text'   => $text,
            ], JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
        if (random_int(1, 100) === 1) self::trim();
    }

    public static function clear(): int {
        $dir = AFS_ROOT . '/storage/aicache';
        if (!is_dir($dir)) return 0;
        $n = 0;
        foreach (glob($dir . '/*.json') ?: [] as $f) {
            if (@unlink($f)) $n++;
        }
        return $n;
    }

    private static function path(string $intent, string $context): string {
        // Normalise context: trim whitespace + collapse runs.
        $norm = preg_replace('/\s+/', ' ', trim($context));
        $key  = hash('sha256', $intent . "\0" . $norm);
        $dir  = AFS_ROOT . '/storage/aicache';
        return $dir . '/' . substr($key, 0, 2) . '_' . substr($key, 2) . '.json';
    }

    private static function trim(): void {
        $dir = AFS_ROOT . '/storage/aicache';
        if (!is_dir($dir)) return;
        $files = glob($dir . '/*.json') ?: [];
        if (count($files) <= self::MAX_ENTRIES) return;
        // FIFO by mtime — oldest first.
        usort($files, fn($a, $b) => filemtime($a) <=> filemtime($b));
        $toRemove = count($files) - self::MAX_ENTRIES;
        for ($i = 0; $i < $toRemove; $i++) @unlink($files[$i]);
    }
}
