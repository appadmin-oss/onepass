<?php

class ContentBlock {
    private static array $cache = [];

    public static function get(string $key, string $default = ''): string {
        if (!array_key_exists($key, self::$cache)) {
            self::loadAll();
        }
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, string $value): void {
        if (!Database::available()) return;
        Database::exec(
            'INSERT INTO content_blocks (key_name, value_text) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value_text = VALUES(value_text)',
            [$key, $value]
        );
        self::$cache[$key] = $value;
    }

    public static function loadAll(): array {
        if (self::$cache) return self::$cache;
        $rows = [];
        if (Database::available()) {
            try { $rows = Database::all('SELECT key_name, value_text FROM content_blocks'); } catch (Throwable $e) { $rows = []; }
        }
        foreach ($rows as $r) self::$cache[$r['key_name']] = $r['value_text'];
        // Seed defaults so the homepage can render before DB is populated
        $defaults = self::defaults();
        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, self::$cache)) self::$cache[$k] = $v;
        }
        return self::$cache;
    }

    /** Render {{accent}}…{{/accent}} markers as crimson italics. */
    public static function html(string $key, string $default = ''): string {
        $raw = self::get($key, $default);
        $escaped = e($raw);
        return preg_replace_callback('/\{\{accent\}\}(.*?)\{\{\/accent\}\}/', function ($m) {
            return '<em class="accent-italic">' . $m[1] . '</em>';
        }, $escaped);
    }

    private static function defaults(): array {
        return [
            'hero.eyebrow'       => '// AFROSTRENGTH LIMITED · LAGOS · DIGITAL DESIGN · BRANDING · DEVELOPMENT · PRODUCTION',
            'hero.title'         => 'Building Brands, {{accent}}Strengthening Legacies.{{/accent}}',
            'hero.sub'           => 'Your vision. Transformed into a timeless legacy. We are the convergence point of Art, Media, and Technology — three disciplines that, when unified, create brands that don\'t just compete but dominate.',
            'hero.cta_primary'   => 'Start a brief',
            'hero.cta_secondary' => 'See the work',
            'final.cta_title'    => 'Ready to build something {{accent}}powerful?{{/accent}}',
            'final.trust'        => '50+ projects delivered · trusted by brands across Africa',
        ];
    }
}
