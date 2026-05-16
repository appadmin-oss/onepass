<?php

/**
 * Firebase — minimal, dependency-free verifier for Firebase Authentication
 * ID tokens (the JWT returned by `signInWithPopup(...).user.getIdToken()`).
 *
 * Why custom and not the official SDK? Because shared cPanel hosting often
 * lacks Composer. So we implement just the JWT verification we need:
 *
 *   1. Split the JWT into header.payload.signature (base64url).
 *   2. Decode header → algorithm + kid.
 *   3. Fetch Google's public X.509 certificates (cached on disk for the
 *      Cache-Control max-age the response sets).
 *   4. Extract the public key for our kid → openssl_verify().
 *   5. Validate claims: iss, aud, exp, iat, auth_time, sub.
 *
 * Returns ['ok' => bool, 'identity' => array | null, 'error' => string].
 */
class Firebase {
    private const JWKS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
    private const CACHE_FILE = '/storage/firebase_jwks.json';

    public static function enabled(): bool {
        return defined('FIREBASE_ENABLED') && FIREBASE_ENABLED;
    }

    public static function projectId(): string {
        return defined('FIREBASE_PROJECT_ID') ? FIREBASE_PROJECT_ID : '';
    }

    /**
     * Verify an ID token. On success, returns the trusted identity
     * (uid, email, name, picture, email_verified, claims).
     */
    public static function verifyIdToken(string $jwt): array {
        if (!self::enabled()) {
            return ['ok' => false, 'identity' => null, 'error' => 'firebase_disabled'];
        }
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return ['ok' => false, 'identity' => null, 'error' => 'malformed_jwt'];
        }
        [$h64, $p64, $s64] = $parts;
        $header  = json_decode(self::b64UrlDecode($h64), true);
        $payload = json_decode(self::b64UrlDecode($p64), true);
        $sig     = self::b64UrlDecode($s64);
        if (!is_array($header) || !is_array($payload) || $sig === '') {
            return ['ok' => false, 'identity' => null, 'error' => 'bad_segments'];
        }
        if (($header['alg'] ?? '') !== 'RS256') {
            return ['ok' => false, 'identity' => null, 'error' => 'bad_alg'];
        }
        $kid = (string)($header['kid'] ?? '');
        if ($kid === '') {
            return ['ok' => false, 'identity' => null, 'error' => 'no_kid'];
        }

        $jwks = self::fetchJwks();
        $cert = $jwks[$kid] ?? null;
        if (!$cert) {
            return ['ok' => false, 'identity' => null, 'error' => 'unknown_kid'];
        }
        $publicKey = openssl_pkey_get_public($cert);
        if ($publicKey === false) {
            return ['ok' => false, 'identity' => null, 'error' => 'bad_cert'];
        }
        $signedInput = $h64 . '.' . $p64;
        $verify = openssl_verify($signedInput, $sig, $publicKey, OPENSSL_ALGO_SHA256);
        if ($verify !== 1) {
            return ['ok' => false, 'identity' => null, 'error' => 'bad_signature'];
        }

        // Validate the standard Firebase claims.
        $projectId = self::projectId();
        $now = time();
        if (($payload['aud'] ?? '') !== $projectId) {
            return ['ok' => false, 'identity' => null, 'error' => 'wrong_audience'];
        }
        $expectedIss = 'https://securetoken.google.com/' . $projectId;
        if (($payload['iss'] ?? '') !== $expectedIss) {
            return ['ok' => false, 'identity' => null, 'error' => 'wrong_issuer'];
        }
        if (($payload['exp'] ?? 0) < $now) {
            return ['ok' => false, 'identity' => null, 'error' => 'expired'];
        }
        if (($payload['iat'] ?? 0) > $now + 60) {
            return ['ok' => false, 'identity' => null, 'error' => 'iat_in_future'];
        }
        if (($payload['auth_time'] ?? 0) > $now + 60) {
            return ['ok' => false, 'identity' => null, 'error' => 'auth_time_in_future'];
        }
        if (empty($payload['sub']) || !is_string($payload['sub'])) {
            return ['ok' => false, 'identity' => null, 'error' => 'no_subject'];
        }
        // We only accept Google sign-ins through this path.
        $provider = $payload['firebase']['sign_in_provider'] ?? '';
        if ($provider !== 'google.com') {
            return ['ok' => false, 'identity' => null, 'error' => 'unsupported_provider'];
        }
        if (empty($payload['email_verified'])) {
            return ['ok' => false, 'identity' => null, 'error' => 'email_unverified'];
        }

        return ['ok' => true, 'identity' => [
            'uid'            => $payload['sub'],
            'email'          => strtolower((string)($payload['email'] ?? '')),
            'name'           => $payload['name']    ?? '',
            'picture'        => $payload['picture'] ?? '',
            'email_verified' => true,
            'provider'       => 'google.com',
        ], 'error' => ''];
    }

    // ----- Internals ------------------------------------------------

    private static function fetchJwks(): array {
        $path = AFS_ROOT . self::CACHE_FILE;
        if (is_file($path)) {
            $cached = json_decode((string)file_get_contents($path), true);
            if (is_array($cached) && ($cached['expires'] ?? 0) > time() && !empty($cached['keys'])) {
                return $cached['keys'];
            }
        }
        $ch = curl_init(self::JWKS_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        if ($resp === false || $code >= 400) return [];
        $headers = substr((string)$resp, 0, (int)$hsize);
        $body    = substr((string)$resp, (int)$hsize);
        $keys = json_decode((string)$body, true);
        if (!is_array($keys)) return [];
        // Parse Cache-Control: max-age=NN
        $expires = time() + 3600; // sensible default if header missing
        if (preg_match('/max-age=(\d+)/i', $headers, $m)) {
            $expires = time() + max(60, min(86400, (int)$m[1]));
        }
        $dir = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents($path, json_encode(['expires' => $expires, 'keys' => $keys]), LOCK_EX);
        return $keys;
    }

    private static function b64UrlDecode(string $s): string {
        $pad = 4 - (strlen($s) % 4);
        if ($pad < 4) $s .= str_repeat('=', $pad);
        return (string) base64_decode(strtr($s, '-_', '+/'), true);
    }
}
