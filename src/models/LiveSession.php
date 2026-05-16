<?php

class LiveSession {
    public static function upcoming(): array {
        if (Database::available()) {
            try {
                $rows = Database::all("SELECT * FROM live_sessions WHERE status = 'upcoming' ORDER BY starts_at");
                if ($rows) return $rows;
            } catch (Throwable $e) { /* ignore */ }
        }
        return self::seed();
    }

    public static function seed(): array {
        return [
            ['title' => 'Naming Workshop — From shortlist to lockup',
             'summary' => 'A two-hour live working session with Adaeze.',
             'starts_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
             'duration_minutes' => 120, 'instructor' => 'Adaeze Okeke', 'capacity' => 25],
            ['title' => 'Editorial Type Clinic',
             'summary' => 'Bring your layouts. We will review them live.',
             'starts_at' => date('Y-m-d H:i:s', strtotime('+28 days')),
             'duration_minutes' => 90, 'instructor' => 'Tope Adeyemi', 'capacity' => 20],
            ['title' => 'Production Q&A — Anything goes',
             'summary' => 'Open Q&A for production leads.',
             'starts_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
             'duration_minutes' => 60, 'instructor' => 'Kemi Balogun', 'capacity' => 40],
        ];
    }
}
