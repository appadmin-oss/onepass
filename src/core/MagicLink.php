<?php

/**
 * MagicLink — passwordless sign-in by email link or 6-digit OTP.
 *
 * Why two pieces in one token?
 *
 *   We use a split-token scheme: every issuance produces a public
 *   "selector" (24 base64url chars, used as the primary lookup key)
 *   and a secret "verifier" (32 base64url chars, hashed before
 *   storage). The link the user receives is `selector.verifier`;
 *   the row in `auth_tokens` stores only sha256(verifier). On
 *   redemption we look up by selector, then compare hashes in
 *   constant time. This means a leak of the database does NOT
 *   leak any working magic links.
 *
 *   The 6-digit OTP is a separate value, also hashed at rest, sent
 *   alongside the link in the email. Some clients flatten links;
 *   some users prefer to paste a code. Either path works.
 *
 * Single-use, time-bound, throttled:
 *
 *   - Tokens live 15 minutes.
 *   - A successful redemption sets consumed_at; any second use
 *     fails closed.
 *   - Each token tracks attempts; 5 failed OTP guesses invalidate
 *     it.
 *   - We rate-limit requests per email (3 every 10 min) and per
 *     IP (10 every 10 min) via the Security class.
 */
class MagicLink {
    private const TOKEN_TTL_SECONDS = 900;     // 15 minutes
    private const MAX_OTP_ATTEMPTS  = 5;
    private const SELECTOR_LEN      = 18;      // 18 raw bytes → 24 base64url chars
    private const VERIFIER_LEN      = 24;      // 24 raw bytes → 32 base64url chars

    /** @return array{ok:bool, error?:string, link?:string, otp?:string, expires_at?:string} */
    public static function issue(string $email, string $purpose = 'login'): array {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'invalid_email'];
        }
        if (!Database::available()) {
            return ['ok' => false, 'error' => 'database_unavailable'];
        }
        if (!Security::rateLimit('magic_email_' . sha1($email), 3, 600)) {
            return ['ok' => false, 'error' => 'too_many_requests'];
        }
        if (!Security::rateLimit('magic_ip_' . Security::clientIp(), 10, 600)) {
            return ['ok' => false, 'error' => 'too_many_requests'];
        }

        $selector  = self::b64url(random_bytes(self::SELECTOR_LEN));
        $verifier  = self::b64url(random_bytes(self::VERIFIER_LEN));
        $otp       = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_TTL_SECONDS);

        Database::insert(
            'INSERT INTO auth_tokens
                (email, selector, verifier_hash, otp_hash, purpose, expires_at, request_ip, request_ua)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $email,
                $selector,
                hash('sha256', $verifier),
                hash('sha256', $otp),
                in_array($purpose, ['login','signup','email_verify'], true) ? $purpose : 'login',
                $expiresAt,
                Security::clientIp(),
                substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200),
            ]
        );

        return [
            'ok'         => true,
            'link'       => self::buildLink($selector, $verifier),
            'otp'        => $otp,
            'selector'   => $selector,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verify a `selector.verifier` link. Returns the email on success,
     * null on failure. Marks the token as consumed atomically.
     */
    public static function consumeLink(string $combined): ?string {
        $parts = explode('.', $combined, 2);
        if (count($parts) !== 2) return null;
        [$selector, $verifier] = $parts;
        return self::consume($selector, hash('sha256', $verifier), 'link');
    }

    /**
     * Verify a 6-digit OTP against a selector. Returns the email on
     * success, null on failure. Increments attempts on miss; locks
     * after MAX_OTP_ATTEMPTS misses.
     */
    public static function consumeOtp(string $selector, string $otp): ?string {
        $otp = preg_replace('/\D/', '', $otp);
        if (strlen($otp) !== 6) return null;
        return self::consume($selector, hash('sha256', $otp), 'otp');
    }

    /**
     * Returns the most recent unexpired selector for an email, if any.
     * Used by the OTP-paste path so the user doesn't have to type the
     * selector — we look it up from session state.
     */
    public static function latestSelectorFor(string $email): ?string {
        if (!Database::available()) return null;
        $row = Database::one(
            'SELECT selector FROM auth_tokens
             WHERE email = ? AND consumed_at IS NULL AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1',
            [strtolower(trim($email))]
        );
        return $row['selector'] ?? null;
    }

    /** Drop expired or fully-burned tokens. Safe to call from any request. */
    public static function gc(): void {
        if (!Database::available()) return;
        try {
            Database::exec('DELETE FROM auth_tokens WHERE expires_at < NOW() OR attempts >= ?', [self::MAX_OTP_ATTEMPTS]);
        } catch (Throwable $e) { /* nothing useful to do */ }
    }

    // ----- internals ------------------------------------------------

    private static function consume(string $selector, string $candidateHash, string $kind): ?string {
        if (!Database::available()) return null;
        $row = Database::one(
            'SELECT * FROM auth_tokens WHERE selector = ? LIMIT 1',
            [$selector]
        );
        if (!$row) return null;
        if (!empty($row['consumed_at'])) return null;
        if (strtotime((string)$row['expires_at']) < time()) return null;
        if ((int)$row['attempts'] >= self::MAX_OTP_ATTEMPTS) return null;

        $expected = $kind === 'otp' ? $row['otp_hash'] : $row['verifier_hash'];
        if (!hash_equals((string)$expected, $candidateHash)) {
            Database::exec('UPDATE auth_tokens SET attempts = attempts + 1 WHERE id = ?', [$row['id']]);
            return null;
        }
        // Atomic single-use: only one updater can set consumed_at.
        $affected = Database::exec(
            'UPDATE auth_tokens SET consumed_at = NOW()
             WHERE id = ? AND consumed_at IS NULL',
            [$row['id']]
        );
        if ($affected < 1) return null;

        return strtolower((string)$row['email']);
    }

    private static function buildLink(string $selector, string $verifier): string {
        $token = $selector . '.' . $verifier;
        return rtrim(AFS_URL, '/') . '/auth/verify?t=' . urlencode($token);
    }

    private static function b64url(string $bytes): string {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
