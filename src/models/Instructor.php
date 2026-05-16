<?php

class Instructor {
    public static function all(): array {
        if (Database::available()) {
            try {
                $rows = Database::all('SELECT * FROM instructors ORDER BY sort, id');
                if ($rows) return array_map([self::class, 'hydrate'], $rows);
            } catch (Throwable $e) { /* ignore */ }
        }
        return array_map([self::class, 'hydrate'], self::seed());
    }

    private static function hydrate(array $row): array {
        $row['skills'] = $row['skills'] ?? '';
        return $row;
    }

    public static function seed(): array {
        return [
            ['name' => 'Adaeze Okeke', 'slug' => 'adaeze-okeke', 'role' => 'Lead Brand Strategist',
             'bio' => 'Adaeze leads our brand strategy and naming work. Twelve years across studios in Lagos, London, and Accra. Quiet authority, strong opinions, all earned.',
             'status_pill' => 'live', 'status_label' => 'Available', 'skills' => 'Strategy, Naming, Voice'],
            ['name' => 'Tope Adeyemi', 'slug' => 'tope-adeyemi', 'role' => 'Creative Director',
             'bio' => 'Tope runs our editorial and design systems work. Type-led. Print-rooted. Every grid we ship has his fingerprints on it.',
             'status_pill' => 'production', 'status_label' => 'Booked through Q2', 'skills' => 'Editorial, Type, Print'],
            ['name' => 'Kemi Balogun', 'slug' => 'kemi-balogun', 'role' => 'Head of Production',
             'bio' => 'Kemi turns brand strategy into shipped events and shipped film. Zero-drama production, every time.',
             'status_pill' => 'live', 'status_label' => 'Available', 'skills' => 'Film, Events, Logistics'],
        ];
    }
}
