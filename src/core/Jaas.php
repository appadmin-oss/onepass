<?php

/**
 * Jaas — Jitsi helper.
 *
 * Two modes:
 *   1. Configured JaaS tenant (paid 8x8) → mints RS256 JWT, embeds on
 *      https://8x8.vc/{APP_ID}/external_api.js.
 *   2. Public meet.jit.si (free, open-source) → no token, embeds on
 *      https://meet.jit.si/external_api.js with a stable per-cohort room.
 *
 * Use Jaas::mode() to decide which one is active, then pass
 * Jaas::embedConfig($room, $user) to the view.
 */
class Jaas {
    /** "jaas" when fully wired, "public" otherwise (still functional). */
    public static function mode(): string {
        if (defined('JAAS_ENABLED') && JAAS_ENABLED
            && defined('JAAS_APP_ID') && !empty(JAAS_APP_ID)
            && defined('JAAS_API_KEY') && !empty(JAAS_API_KEY)
            && defined('JAAS_PRIVATE_KEY') && is_file(JAAS_PRIVATE_KEY)) {
            return 'jaas';
        }
        return 'public';
    }

    public static function enabled(): bool {
        // Always enabled — falls back to public Jitsi when no tenant.
        return true;
    }

    /**
     * Returns an array the live-sessions view can drop straight into the
     * JitsiMeetExternalAPI constructor.
     *
     *   ['domain' => '...', 'roomName' => '...', 'script' => '...', 'jwt' => '...', 'mode' => 'public'|'jaas']
     */
    public static function embedConfig(string $room, array $user = [], bool $moderator = false): array {
        $room = preg_replace('/[^a-zA-Z0-9\-_]/', '', $room) ?: 'afs-room';
        $mode = self::mode();

        if ($mode === 'jaas') {
            return [
                'mode'     => 'jaas',
                'domain'   => JAAS_DOMAIN,
                'roomName' => JAAS_APP_ID . '/' . $room,
                'script'   => 'https://' . JAAS_DOMAIN . '/' . JAAS_APP_ID . '/external_api.js',
                'jwt'      => self::mintToken($user, $room, $moderator),
            ];
        }
        // Public Jitsi — prefix the room name with our brand so collisions
        // are improbable. No JWT needed.
        return [
            'mode'     => 'public',
            'domain'   => 'meet.jit.si',
            'roomName' => 'AfrostrengthAcademy-' . $room,
            'script'   => 'https://meet.jit.si/external_api.js',
            'jwt'      => null,
        ];
    }

    /** Mint a JaaS JWT (only used when mode() === 'jaas'). */
    public static function mintToken(array $user, string $room, bool $moderator = false, int $ttl = 7200): ?string {
        if (self::mode() !== 'jaas') return null;
        $now = time();
        $header = ['alg' => 'RS256', 'kid' => JAAS_API_KEY, 'typ' => 'JWT'];
        $payload = [
            'aud' => 'jitsi',
            'iss' => 'chat',
            'sub' => JAAS_APP_ID,
            'room'=> $room,
            'iat' => $now,
            'nbf' => $now - 5,
            'exp' => $now + $ttl,
            'context' => [
                'user' => [
                    'id'        => (string)($user['id'] ?? bin2hex(random_bytes(8))),
                    'name'      => (string)($user['name'] ?? 'Guest'),
                    'email'     => (string)($user['email'] ?? ''),
                    'avatar'    => (string)($user['avatar'] ?? ''),
                    'moderator' => $moderator ? 'true' : 'false',
                ],
                'features' => [
                    'livestreaming'  => $moderator ? 'true' : 'false',
                    'recording'      => $moderator ? 'true' : 'false',
                    'transcription'  => 'true',
                    'outbound-call'  => 'false',
                ],
                'room' => ['regex' => false],
            ],
        ];
        $segments = [self::b64(json_encode($header)), self::b64(json_encode($payload))];
        $signing  = implode('.', $segments);
        $key = openssl_pkey_get_private('file://' . JAAS_PRIVATE_KEY);
        if (!$key) { error_log('[jaas] cannot read private key'); return null; }
        $signature = '';
        if (!openssl_sign($signing, $signature, $key, OPENSSL_ALGO_SHA256)) {
            error_log('[jaas] sign failed'); return null;
        }
        $segments[] = self::b64($signature);
        return implode('.', $segments);
    }

    // ----- back-compat shims used by older callers -----
    public static function scriptUrl(): string {
        $cfg = self::embedConfig('preview');
        return $cfg['script'];
    }
    public static function fullRoomName(string $room): string {
        $cfg = self::embedConfig($room);
        return $cfg['roomName'];
    }
    public static function token(array $user, string $room, bool $moderator = false, int $ttl = 7200): ?string {
        return self::mintToken($user, $room, $moderator, $ttl);
    }

    private static function b64(string $bytes): string {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
