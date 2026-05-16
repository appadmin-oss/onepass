<?php

class Certification {
    public static function all(): array {
        if (Database::available()) {
            try {
                $rows = Database::all('SELECT * FROM certifications ORDER BY id');
                if ($rows) return $rows;
            } catch (Throwable $e) { /* ignore */ }
        }
        return self::seed();
    }

    public static function seed(): array {
        return [
            ['title' => 'Brand Strategist Certificate',  'slug' => 'brand-strategist',
             'summary' => 'Demonstrate working command of brand positioning, naming, and verbal systems.'],
            ['title' => 'Editorial Systems Certificate', 'slug' => 'editorial-systems',
             'summary' => 'Prove fluency in editorial design, type systems, and voice frameworks.'],
            ['title' => 'Media Production Skill Badge',  'slug' => 'media-production',
             'summary' => 'Show readiness to lead in-house photo, film, and podcast production.'],
            ['title' => 'Event Management Foundations',  'slug' => 'event-management',
             'summary' => 'Foundational competence in event strategy, logistics, and on-the-day production.'],
        ];
    }
}
