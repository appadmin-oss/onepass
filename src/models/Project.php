<?php

class Project {
    /** @return array<int,array> */
    public static function all(bool $publishedOnly = true): array {
        if (Database::available()) {
            try {
                $sql = 'SELECT * FROM projects' . ($publishedOnly ? " WHERE status = 'published'" : '') . ' ORDER BY year DESC, id DESC';
                $rows = Database::all($sql);
                if ($rows) return array_map([self::class, 'hydrate'], $rows);
            } catch (Throwable $e) { /* fall through */ }
        }
        return self::seed();
    }

    public static function featured(int $limit = 3): array {
        return array_slice(array_filter(self::all(), fn($p) => !empty($p['is_featured'])), 0, $limit);
    }

    public static function find(string $slug): ?array {
        foreach (self::all() as $p) if (($p['slug'] ?? '') === $slug) return $p;
        return null;
    }

    public static function related(string $excludeSlug, int $limit = 3): array {
        $rows = array_filter(self::all(), fn($p) => ($p['slug'] ?? '') !== $excludeSlug);
        return array_slice($rows, 0, $limit);
    }

    private static function hydrate(array $row): array {
        $row['gallery'] = $row['gallery_json'] ? json_decode($row['gallery_json'], true) ?? [] : [];
        return $row;
    }

    public static function seed(): array {
        return [
            [
                'id' => 1, 'index' => 1,
                'title' => 'Mwanga — luminaire identity', 'slug' => 'mwanga-luminaire-identity',
                'client' => 'Mwanga Studio', 'year' => 2026,
                'category' => 'Brand · Identity · Packaging', 'tags' => 'Brand · Identity · Packaging',
                'summary' => 'A complete identity system for a Lagos-based luminaire studio expanding into pan-African retail.',
                'problem' => 'Mwanga had grown from a workshop to a regional studio without an identity system that scaled. Every shopfront looked different. Every package told a different story.',
                'solution' => 'We rebuilt the brand from positioning down — a verbal system rooted in light as legacy, a logo mark drawn from West African brass-work, and a full retail and packaging system that locks every surface to one voice.',
                'outcome' => 'Twelve retail locations now operating on a single identity. Wholesale conversion up 32% in the first quarter post-launch. Packaging recognised by AGI 2026.',
                'is_featured' => 1, 'status_label' => 'live', 'gallery' => [],
                'featured_image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=1200&q=80&auto=format&fit=crop',
            ],
            [
                'id' => 2, 'index' => 2,
                'title' => 'Lagos Futures Summit', 'slug' => 'lagos-futures-summit',
                'client' => 'FCMB Innovation', 'year' => 2025,
                'category' => 'Event · Production · Editorial', 'tags' => 'Event · Production · Editorial',
                'summary' => 'Two thousand attendees, one weekend, one editorial system threaded through every surface.',
                'problem' => 'FCMB Innovation needed a fintech summit that read as serious as its programme — but every existing event template felt either too corporate or too festival.',
                'solution' => 'We designed an editorial-first event system: stage design, signage, programme book, recap film. Every surface carried the same voice, the same type, the same restraint.',
                'outcome' => 'Two thousand attendees. Zero compromises on production. Recap film viewed 480k times in the first month. The event is back in 2026 with a 30% larger footprint.',
                'is_featured' => 1, 'status_label' => 'case', 'gallery' => [],
                'featured_image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&q=80&auto=format&fit=crop',
            ],
            [
                'id' => 3, 'index' => 3,
                'title' => 'Heritable — wealth platform', 'slug' => 'heritable-wealth-platform',
                'client' => 'Heritable Africa', 'year' => 2026,
                'category' => 'Digital · Product · Brand', 'tags' => 'Digital · Product · Brand',
                'summary' => 'From pitch deck to shipped wealth platform in eleven weeks.',
                'problem' => 'Heritable had Series-A momentum but was pitching against legacy banks with a Figma file. They needed brand and product, shipping in parallel.',
                'solution' => 'A two-track build: brand strategy and identity in week one, product design and engineering from week two. We compressed the discovery into a strategy intensive and ran design and engineering in lockstep.',
                'outcome' => 'Live platform in eleven weeks. First 1,000 users onboarded inside the launch window. Brand cited in Rest of World as a category benchmark.',
                'is_featured' => 1, 'status_label' => 'production', 'gallery' => [],
                'featured_image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=80&auto=format&fit=crop',
            ],
        ];
    }
}
