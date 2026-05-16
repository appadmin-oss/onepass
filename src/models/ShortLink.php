<?php

class ShortLink {
    /** Reserved codes that conflict with first-segment routes. */
    private const RESERVED = [
        'admin','academy','about','blog','contact','services','projects',
        'tools','login','dashboard','logout','opportunities','testimonials',
        'faq','api','sitemap.xml','robots.txt','s','field-notes',
    ];

    public static function isReserved(string $code): bool {
        return in_array(strtolower($code), self::RESERVED, true);
    }

    public static function create(string $url, ?string $alias = null): array {
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            return ['ok' => false, 'error' => 'invalid_url', 'message' => 'URL must start with http:// or https://.'];
        }
        if (strlen($url) > 2048) {
            return ['ok' => false, 'error' => 'too_long', 'message' => 'URL too long (max 2048 chars).'];
        }
        $alias = trim($alias ?? '');
        if ($alias !== '') {
            $alias = strtolower(preg_replace('/[^a-zA-Z0-9\-_]/', '', $alias));
            if ($alias === '' || strlen($alias) < 3) {
                return ['ok' => false, 'error' => 'bad_alias', 'message' => 'Alias must be at least 3 chars (letters/digits/-/_).'];
            }
            if (self::isReserved($alias)) {
                return ['ok' => false, 'error' => 'reserved', 'message' => 'That alias is reserved — pick another.'];
            }
        }
        if (!Database::available()) {
            // No DB — return a client-only code; the link won't actually resolve
            // but at least the tool still feels alive in dev.
            return ['ok' => true, 'code' => $alias ?: self::randomCode(), 'persisted' => false];
        }

        // If alias requested, check it's free
        if ($alias) {
            $existing = Database::one('SELECT id FROM short_links WHERE code = ?', [$alias]);
            if ($existing) {
                return ['ok' => false, 'error' => 'taken', 'message' => 'That alias is already in use.'];
            }
            $code = $alias;
        } else {
            // Generate until we find a free code
            for ($i = 0; $i < 8; $i++) {
                $code = self::randomCode();
                if (!Database::one('SELECT id FROM short_links WHERE code = ?', [$code])) break;
            }
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        Database::insert(
            'INSERT INTO short_links (code, target_url, created_ip) VALUES (?, ?, ?)',
            [$code, $url, $ip]
        );
        return ['ok' => true, 'code' => $code, 'persisted' => true];
    }

    public static function resolve(string $code): ?string {
        if (!Database::available()) return null;
        $row = Database::one('SELECT target_url FROM short_links WHERE code = ?', [$code]);
        if (!$row) return null;
        // Best-effort click + lastUsed update (don't block redirect on failure)
        try {
            Database::exec(
                'UPDATE short_links SET clicks = clicks + 1, last_used_at = NOW() WHERE code = ?',
                [$code]
            );
        } catch (Throwable $e) { /* ignore */ }
        return $row['target_url'];
    }

    public static function stats(string $code): ?array {
        if (!Database::available()) return null;
        return Database::one('SELECT code, target_url, clicks, created_at, last_used_at FROM short_links WHERE code = ?', [$code]);
    }

    private static function randomCode(): string {
        $alphabet = 'abcdefghijkmnpqrstuvwxyz23456789'; // unambiguous
        $out = '';
        $bytes = random_bytes(7);
        for ($i = 0; $i < 7; $i++) $out .= $alphabet[ord($bytes[$i]) % strlen($alphabet)];
        return $out;
    }
}
