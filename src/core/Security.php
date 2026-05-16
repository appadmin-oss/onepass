<?php

/**
 * Security — small, dependency-free defensive primitives used across the site.
 *
 * Everything here is free to operate (no third-party keys required) and
 * designed for shared cPanel hosting. The implementations are deliberately
 * small so a reader can audit them quickly.
 *
 *   rateLimit($key, $max, $windowSec)        soft per-key window
 *   lockoutCheck($key) / lockoutHit($key)    incremental lockout for auth
 *   lockoutReset($key)                       clear on success
 *   passwordIsBreached($password)            HIBP k-anonymity check (free)
 *   honeypotOk($field)                       silent honeypot validator
 *   totpSecret()                             new RFC 4226 base32 secret
 *   totpOtpauthUrl($secret, $label, $issuer) URL for Google Authenticator
 *   totpVerify($secret, $code)               RFC 6238 6-digit verify (±1 step)
 *   sendHeaders()                            CSP + standard hardening headers
 */
class Security {

    // ----- Rate limit (file-backed, atomic) --------------------------

    /**
     * Returns true if the action is allowed, false if rate-limited.
     * Uses a tiny JSON-per-key file under /storage/ratelimit/.
     * Each entry stores a sliding-window timestamp array.
     */
    public static function rateLimit(string $key, int $max, int $windowSec): bool {
        $now = time();
        $path = self::path('ratelimit', $key);
        $fp = @fopen($path, 'c+');
        if (!$fp) return true; // fail-open — never lock the user out because disk failed
        flock($fp, LOCK_EX);
        $raw = stream_get_contents($fp);
        $hits = json_decode((string)$raw, true);
        if (!is_array($hits)) $hits = [];
        $hits = array_values(array_filter($hits, fn($t) => ($now - (int)$t) < $windowSec));
        $allowed = count($hits) < $max;
        if ($allowed) $hits[] = $now;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($hits));
        flock($fp, LOCK_UN);
        fclose($fp);
        return $allowed;
    }

    // ----- Lockout (incremental backoff on auth failure) -------------

    /** @return array{locked:bool, retry_after:int, fails:int} */
    public static function lockoutCheck(string $key): array {
        $path = self::path('lockout', $key);
        if (!is_file($path)) return ['locked' => false, 'retry_after' => 0, 'fails' => 0];
        $data = json_decode((string)file_get_contents($path), true);
        if (!is_array($data)) return ['locked' => false, 'retry_after' => 0, 'fails' => 0];
        $fails = (int)($data['fails'] ?? 0);
        $until = (int)($data['until'] ?? 0);
        if ($until > time()) {
            return ['locked' => true, 'retry_after' => $until - time(), 'fails' => $fails];
        }
        return ['locked' => false, 'retry_after' => 0, 'fails' => $fails];
    }

    /** Record a failed attempt and apply exponential backoff. */
    public static function lockoutHit(string $key): void {
        $path = self::path('lockout', $key);
        $data = is_file($path) ? json_decode((string)file_get_contents($path), true) : null;
        if (!is_array($data)) $data = ['fails' => 0, 'until' => 0];
        $data['fails'] = (int)$data['fails'] + 1;
        // 1,2,3 free → 4th = 30s, 5th = 2m, 6th = 10m, 7th+ = 30m
        $f = $data['fails'];
        $delay = $f >= 7 ? 1800 : ($f === 6 ? 600 : ($f === 5 ? 120 : ($f === 4 ? 30 : 0)));
        $data['until'] = $delay > 0 ? (time() + $delay) : 0;
        @file_put_contents($path, json_encode($data), LOCK_EX);
    }

    public static function lockoutReset(string $key): void {
        $path = self::path('lockout', $key);
        @unlink($path);
    }

    // ----- HIBP k-anonymity password check ---------------------------

    /**
     * Asks Have-I-Been-Pwned whether the password's SHA-1 prefix is in any
     * known breach. Only the first 5 hex chars of the SHA-1 are sent; the
     * service returns the suffixes that match, and we compare locally.
     * Free, no API key, GDPR-friendly (no plaintext password leaves us).
     *
     * Returns false on network failure (fail-open — don't block sign-up
     * because HIBP is down). Returns 0+ if the password has been seen.
     *
     * @return int|false  occurrence count (0 = unseen), or false on failure
     */
    public static function passwordIsBreached(string $password) {
        $sha1   = strtoupper(sha1($password));
        $prefix = substr($sha1, 0, 5);
        $suffix = substr($sha1, 5);
        $ch = curl_init('https://api.pwnedpasswords.com/range/' . $prefix);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Add-Padding: true'],
            CURLOPT_USERAGENT      => 'Afrostrength-site/1.0',
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400) return false;
        foreach (preg_split('/\r?\n/', (string)$body) as $line) {
            $parts = explode(':', trim($line));
            if (count($parts) !== 2) continue;
            if (strcasecmp($parts[0], $suffix) === 0) return (int)$parts[1];
        }
        return 0;
    }

    // ----- Honeypot --------------------------------------------------

    /**
     * Silent honeypot. Render a hidden `<input name="$field">` in the form
     * (CSS-hidden + tabindex=-1 + autocomplete=off). Bots tend to fill it;
     * humans never see it. Returns true if the submission looks human.
     */
    public static function honeypotOk(string $field = 'website'): bool {
        return ($_POST[$field] ?? '') === '';
    }

    /** Hidden honeypot input — drop this anywhere in your form. */
    public static function honeypotField(string $field = 'website'): string {
        return '<div aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;">'
             . '<label>Leave this empty <input type="text" name="' . htmlspecialchars($field, ENT_QUOTES)
             . '" tabindex="-1" autocomplete="off"></label></div>';
    }

    // ----- TOTP (RFC 6238) ------------------------------------------

    /** Generate a new base32 secret (160 bits = 32 chars). */
    public static function totpSecret(): string {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bytes = random_bytes(20);
        $bin = '';
        foreach (str_split($bytes) as $b) $bin .= str_pad(decbin(ord($b)), 8, '0', STR_PAD_LEFT);
        $out = '';
        foreach (str_split($bin, 5) as $g) {
            if (strlen($g) < 5) $g = str_pad($g, 5, '0');
            $out .= $alphabet[bindec($g)];
        }
        return $out;
    }

    /** Build an otpauth:// URI for QR scanning by any authenticator app. */
    public static function totpOtpauthUrl(string $secret, string $label, string $issuer = 'Afrostrength'): string {
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($label) . '?' . $params;
    }

    /**
     * Verify a 6-digit TOTP code. Accepts the previous and next 30s windows
     * to cover small clock drift on the user's device. Constant-time compare.
     */
    public static function totpVerify(string $secret, string $code): bool {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6) return false;
        $key = self::base32Decode($secret);
        if ($key === '') return false;
        $time = (int) floor(time() / 30);
        foreach ([-1, 0, 1] as $offset) {
            $counter = pack('N*', 0) . pack('N*', $time + $offset);
            $hash = hash_hmac('sha1', $counter, $key, true);
            $offsetByte = ord($hash[strlen($hash) - 1]) & 0x0F;
            $truncated = (ord($hash[$offsetByte])       & 0x7F) << 24
                       | (ord($hash[$offsetByte + 1])   & 0xFF) << 16
                       | (ord($hash[$offsetByte + 2])   & 0xFF) << 8
                       | (ord($hash[$offsetByte + 3])   & 0xFF);
            $candidate = str_pad((string)($truncated % 1000000), 6, '0', STR_PAD_LEFT);
            if (hash_equals($candidate, $code)) return true;
        }
        return false;
    }

    private static function base32Decode(string $b32): string {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
        if ($b32 === '') return '';
        $bin = '';
        foreach (str_split($b32) as $c) {
            $i = strpos($alphabet, $c);
            if ($i === false) return '';
            $bin .= str_pad(decbin($i), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bin, 8) as $g) {
            if (strlen($g) === 8) $out .= chr(bindec($g));
        }
        return $out;
    }

    // ----- HTTP hardening headers -----------------------------------

    /**
     * Sets a sensible, build-aware baseline of security headers. Called once
     * per request from the front controller, before any output.
     *
     * The CSP is intentionally permissive for the public site (we use
     * Tailwind CDN, Fontshare, Google Fonts, GA4, Firebase Auth, Pollinations,
     * Hack Club AI, Jitsi). Tighten further once everything is self-hosted.
     */
    public static function sendHeaders(): void {
        if (headers_sent()) return;
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Cross-Origin-Opener-Policy: same-origin-allow-popups'); // allow Google sign-in popup
        header('Permissions-Policy: ' . implode(', ', [
            'camera=(self)',         // Jitsi live classes
            'microphone=(self)',
            'display-capture=(self)',
            'geolocation=()',
            'interest-cohort=()',
            'browsing-topics=()',
        ]));
        // Light HSTS only if we are actually on HTTPS.
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=15552000; includeSubDomains');
        }
        // Content Security Policy.
        $self    = "'self'";
        $img     = "img-src 'self' data: blob: https://api.qrserver.com https://images.unsplash.com https://*.googleusercontent.com https://*.gstatic.com";
        $script  = "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://www.gstatic.com https://*.googletagmanager.com https://*.firebaseapp.com";
        $style   = "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://api.fontshare.com";
        $font    = "font-src 'self' data: https://fonts.gstatic.com https://api.fontshare.com";
        // jsdelivr.net is allowlisted in script-src for the CDN libraries we
        // load (svg.js, graphics.js, fuse, canvas-confetti). Browsers also
        // try to fetch the matching .map files via XHR, which is gated by
        // connect-src; without this entry the browser blocks every source
        // map with a noisy "Refused to connect…" message. Allow it here.
        $connect = "connect-src 'self' https://cdn.jsdelivr.net https://text.pollinations.ai https://ai.hackclub.com https://api.qrserver.com https://api.pwnedpasswords.com https://www.googletagmanager.com https://*.google-analytics.com https://*.googleapis.com https://identitytoolkit.googleapis.com https://securetoken.googleapis.com https://*.firebaseio.com";
        $frame   = "frame-src 'self' https://*.firebaseapp.com https://accounts.google.com https://meet.jit.si https://*.8x8.vc";
        $object  = "object-src 'none'";
        $base    = "base-uri 'self'";
        $form    = "form-action 'self'";
        header("Content-Security-Policy: default-src $self; $img; $script; $style; $font; $connect; $frame; $object; $base; $form");
    }

    // ----- helpers ---------------------------------------------------

    private static function path(string $bucket, string $key): string {
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '_', $key);
        $dir = AFS_ROOT . '/storage/' . $bucket;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        return $dir . '/' . $safe . '.json';
    }

    public static function clientIp(): string {
        // Don't trust XFF unless we know we're behind a proxy. cPanel default
        // tends to give REMOTE_ADDR straight from the edge. Override with
        // AFS_TRUST_XFF=1 once you've checked your stack.
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (getenv('AFS_TRUST_XFF') && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $first = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
            $first = trim($first);
            if (filter_var($first, FILTER_VALIDATE_IP)) $ip = $first;
        }
        return $ip;
    }
}
