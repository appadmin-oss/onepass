<?php

class Testimonial {
    public static function all(): array {
        if (Database::available()) {
            try {
                $rows = Database::all('SELECT * FROM testimonials ORDER BY is_featured DESC, id');
                if ($rows) return array_map([self::class, 'hydrate'], $rows);
            } catch (Throwable $e) { /* fall through */ }
        }
        return array_map([self::class, 'hydrate'], self::seed());
    }

    public static function featured(int $limit = 3): array {
        return array_slice(array_filter(self::all(), fn($t) => !empty($t['is_featured'])), 0, $limit);
    }

    private static function hydrate(array $row): array {
        $quote = $row['quote'] ?? '';
        $row['quote_html'] = self::renderAccents($quote);
        return $row;
    }

    private static function renderAccents(string $text): string {
        $escaped = e($text);
        return preg_replace_callback('/\{\{accent\}\}(.*?)\{\{\/accent\}\}/', function ($m) {
            return '<em class="accent-italic" style="color:#FCB7AB;">' . $m[1] . '</em>';
        }, $escaped);
    }

    public static function seed(): array {
        return [
            ['client_name' => 'I. Bello', 'role' => 'Founder', 'company' => 'Heritable Africa',
             'quote' => 'Afrostrength took us from a pitch deck to a shipped wealth platform in eleven weeks. {{accent}}No agency theatre.{{/accent}}',
             'rating' => 5, 'is_featured' => 1],
            ['client_name' => 'A. Nwosu', 'role' => 'MD', 'company' => 'Mwanga Studio',
             'quote' => 'We came for the identity. We stayed for the system — every shop floor, every package, every screen now {{accent}}speaks the same language.{{/accent}}',
             'rating' => 5, 'is_featured' => 1],
            ['client_name' => 'O. Adeoye', 'role' => 'Lead, Innovation', 'company' => 'FCMB Innovation',
             'quote' => 'Lagos Futures Summit ran on their production. Two thousand attendees, one weekend, {{accent}}zero compromises.{{/accent}}',
             'rating' => 5, 'is_featured' => 1],
        ];
    }
}
