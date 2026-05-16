<?php

/**
 * Promotion — broad surface for marketing content: banners, cohort drops,
 * service spotlights, scholarships, hackathons, redirect interstitials.
 *
 * Always returns seeded fallbacks when the DB is unavailable so every
 * placement renders during local dev.
 */
class Promotion {
    /** All active, in-date promotions sorted by `sort` then recency. */
    public static function all(): array {
        if (Database::available()) {
            try {
                $rows = Database::all(
                    "SELECT * FROM promotions
                     WHERE status = 'active'
                       AND (starts_at IS NULL OR starts_at <= NOW())
                       AND (ends_at   IS NULL OR ends_at   >= NOW())
                     ORDER BY sort ASC, updated_at DESC"
                );
                if ($rows) return $rows;
            } catch (Throwable $e) { /* fall through to seed */ }
        }
        return self::seed();
    }

    /** All promotions (including drafts) — used by admin. */
    public static function listAll(): array {
        if (!Database::available()) return self::seed();
        try {
            return Database::all('SELECT * FROM promotions ORDER BY sort ASC, updated_at DESC') ?: [];
        } catch (Throwable $e) { return []; }
    }

    /** Promotions tagged for a given placement (e.g. 'interstitial', 'home'). */
    public static function forPlacement(string $placement, int $limit = 3): array {
        $items = array_filter(self::all(), function ($p) use ($placement) {
            $ps = strtolower((string)($p['placements'] ?? ''));
            return str_contains($ps, $placement) || $ps === '';
        });
        return array_slice(array_values($items), 0, $limit);
    }

    public static function find(int $id): ?array {
        if (!Database::available()) return null;
        return Database::one('SELECT * FROM promotions WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array {
        if (!Database::available()) return null;
        return Database::one('SELECT * FROM promotions WHERE slug = ?', [$slug]);
    }

    public static function save(array $data, ?int $id = null): int {
        if (!Database::available()) return 0;
        $fields = ['slug','kind','eyebrow','title','subtitle','body','image_url','tone',
                   'badge','cta_label','cta_href','placements','starts_at','ends_at','sort','status'];
        $vals = [];
        foreach ($fields as $f) $vals[$f] = $data[$f] ?? null;
        // Normalise
        $vals['slug']   = $vals['slug'] ? slugify($vals['slug']) : slugify($vals['title'] ?? 'promotion-' . time());
        $vals['kind']   = in_array($vals['kind'] ?? '', ['banner','cohort','service','scholarship','hackathon','interstitial','feature'], true) ? $vals['kind'] : 'banner';
        $vals['tone']   = in_array($vals['tone'] ?? '', ['crimson','ink','peach','maroon','bone'], true) ? $vals['tone'] : 'crimson';
        $vals['status'] = in_array($vals['status'] ?? '', ['draft','active','paused'], true) ? $vals['status'] : 'draft';
        $vals['sort']   = (int)($vals['sort'] ?? 0);
        $vals['starts_at'] = $vals['starts_at'] ?: null;
        $vals['ends_at']   = $vals['ends_at']   ?: null;

        if ($id) {
            $set = implode(', ', array_map(fn($f) => "`$f` = ?", $fields));
            $bind = array_values($vals); $bind[] = $id;
            Database::exec("UPDATE promotions SET $set WHERE id = ?", $bind);
            return $id;
        }
        $cols = '`' . implode('`,`', $fields) . '`';
        $ph   = rtrim(str_repeat('?,', count($fields)), ',');
        return (int) Database::insert("INSERT INTO promotions ($cols) VALUES ($ph)", array_values($vals));
    }

    public static function delete(int $id): void {
        if (!Database::available()) return;
        Database::exec('DELETE FROM promotions WHERE id = ?', [$id]);
    }

    /** Fallback seed data — keeps every placement rendering during local dev. */
    public static function seed(): array {
        return [
            [
                'id' => 1, 'slug' => 'afrotech-cohort-spring',
                'kind' => 'cohort', 'eyebrow' => '// AFROTECH ACADEMY · LIVE COHORT',
                'title' => 'Next cohort opens in two weeks.',
                'subtitle' => 'Nine certification tracks · live mentor reviews · globally recognised credentials.',
                'body' => null,
                'image_url' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1200&q=80',
                'tone' => 'crimson', 'badge' => 'Cohort',
                'cta_label' => 'Apply free →', 'cta_href' => '/academy/apply',
                'placements' => 'ribbon,interstitial,home,academy', 'sort' => 1, 'status' => 'active',
            ],
            [
                'id' => 2, 'slug' => 'women-in-tech',
                'kind' => 'scholarship', 'eyebrow' => '// SCHOLARSHIP',
                'title' => 'Women-in-Tech Fellowship · 75% tuition cover.',
                'subtitle' => 'Eight seats per AI/ML cohort. Dedicated mentor + growth-network access.',
                'body' => null,
                'image_url' => 'https://images.unsplash.com/photo-1573164713988-8665fc963095?w=1200&q=80',
                'tone' => 'peach', 'badge' => 'Funded',
                'cta_label' => 'Apply for fellowship →', 'cta_href' => '/opportunities',
                'placements' => 'interstitial,academy', 'sort' => 2, 'status' => 'active',
            ],
            [
                'id' => 3, 'slug' => 'brand-development-spotlight',
                'kind' => 'service', 'eyebrow' => '// STUDIO · BRAND DEVELOPMENT',
                'title' => 'Brand strategy + identity in six weeks.',
                'subtitle' => 'Senior strategy intensive, three considered identity routes, full system build.',
                'body' => null,
                'image_url' => 'https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=1200&q=80',
                'tone' => 'ink', 'badge' => 'Service',
                'cta_label' => 'Start a brief →', 'cta_href' => '/services/brand-development',
                'placements' => 'interstitial', 'sort' => 3, 'status' => 'active',
            ],
            [
                'id' => 4, 'slug' => 'build-sprint',
                'kind' => 'hackathon', 'eyebrow' => '// HACKATHON · 48 HOURS',
                'title' => '₦1.5M prize · build weekend at CACENTRE Egbeda.',
                'subtitle' => 'Top three teams split the prize and join the next cohort tuition-free.',
                'body' => null,
                'image_url' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=1200&q=80',
                'tone' => 'maroon', 'badge' => 'Event',
                'cta_label' => 'Register your team →', 'cta_href' => '/opportunities',
                'placements' => 'interstitial,home', 'sort' => 4, 'status' => 'active',
            ],
        ];
    }
}
