<?php

class Post {
    public static function all(bool $publishedOnly = true): array {
        if (Database::available()) {
            try {
                $sql = 'SELECT * FROM posts' . ($publishedOnly ? " WHERE status = 'published'" : '')
                     . ' ORDER BY COALESCE(published_at, created_at) DESC';
                $rows = Database::all($sql);
                if ($rows) return $rows;
            } catch (Throwable $e) { /* fall through */ }
        }
        return self::seed();
    }

    public static function recent(int $limit = 3): array {
        return array_slice(self::all(), 0, $limit);
    }

    public static function find(string $slug): ?array {
        foreach (self::all() as $p) if (($p['slug'] ?? '') === $slug) return $p;
        return null;
    }

    public static function seed(): array {
        return [
            [
                'id' => 1,
                'title' => 'Field notes 04 — what we learned shipping eight rebrands in 2025',
                'slug' => 'field-notes-04-eight-rebrands-2025',
                'excerpt' => 'Eight rebrands. Forty-two production weeks. A handful of things we thought we knew that turned out to be wrong.',
                'body' => '<p>Every rebrand we shipped in 2025 followed the same opening week: a strategy intensive, three identity routes, and a build window. By the eighth, we had tightened the playbook in eight specific ways. This essay is each of them, in order.</p><p>The biggest learning: your strategy intensive is too long. Cut it in half.</p>',
                'category' => 'Editorial', 'tags' => 'rebrand · process',
                'author' => 'Adaeze Okeke', 'status' => 'published',
                'published_at' => '2026-04-22 10:00:00',
                'created_at' => '2026-04-22 10:00:00',
            ],
            [
                'id' => 2,
                'title' => 'How we costed the Lagos Futures Summit',
                'slug' => 'how-we-costed-lagos-futures-summit',
                'excerpt' => 'Two thousand attendees, one weekend, one P&L. The numbers, the line items, and what we’d do again.',
                'body' => '<p>Most event recaps publish a film. We are publishing the spreadsheet. Production budget, vendor mix, actual vs forecast, and the three line items we got wrong.</p>',
                'category' => 'Operations', 'tags' => 'events · finance',
                'author' => 'Kemi Balogun', 'status' => 'published',
                'published_at' => '2026-03-08 09:30:00',
                'created_at' => '2026-03-08 09:30:00',
            ],
            [
                'id' => 3,
                'title' => 'A short defence of editorial systems',
                'slug' => 'short-defence-of-editorial-systems',
                'excerpt' => 'When every brand feels indistinguishable, an editorial system is the cheapest moat you can build.',
                'body' => '<p>An editorial system is a small set of rules — about voice, type, hierarchy, and rhythm — that makes a brand legible across every surface. Most brands skip them. Most brands look the same. Here is the version we use.</p>',
                'category' => 'Editorial', 'tags' => 'systems · voice',
                'author' => 'Tope Adeyemi', 'status' => 'published',
                'published_at' => '2026-02-14 12:00:00',
                'created_at' => '2026-02-14 12:00:00',
            ],
        ];
    }
}
