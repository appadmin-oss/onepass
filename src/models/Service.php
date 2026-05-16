<?php

class Service {
    /** @return array<int,array> */
    public static function all(): array {
        if (Database::available()) {
            try {
                $rows = Database::all('SELECT * FROM services ORDER BY sort, id');
                if ($rows) return array_map([self::class, 'hydrate'], $rows);
            } catch (Throwable $e) { /* fallback */ }
        }
        return self::seed();
    }

    public static function find(string $slug): ?array {
        foreach (self::all() as $s) if (($s['slug'] ?? '') === $slug) return $s;
        return null;
    }

    private static function hydrate(array $row): array {
        $row['process']      = $row['process_json']      ? json_decode($row['process_json'], true) ?? []      : [];
        $row['deliverables'] = $row['deliverables_json'] ? json_decode($row['deliverables_json'], true) ?? [] : [];
        return $row;
    }

    /** Static fallback data — keeps the site rendering before DB is wired. */
    public static function seed(): array {
        return [
            self::s('Brand Development', 'brand-development', 'Strategy, identity systems, naming, and verbal voice — built to scale across every surface.', 'brand', '6 WEEKS', 'CORE', 1),
            self::s('Creative Design',    'creative-design',     'Editorial, packaging, environmental, and digital — design that ships and outlasts the trend cycle.', 'editorial', '4 WEEKS', 'CORE', 2),
            self::s('Media Solutions',    'media-solutions',     'Photography, film, podcast, and editorial — produced in-house with senior crews.', 'media', '3 WEEKS', 'CORE', 3),
            self::s('Project & Event Management', 'project-event-management', 'Strategy, build, and on-the-ground delivery — for the moments that have to land.', 'spark', '4–8 WEEKS', 'CORE', 4),
            self::s('Digital Solutions',  'digital-solutions',   'Sites, products, and digital surfaces — built to perform on real-world hosting.', 'stack', '6–10 WEEKS', 'CORE', 5),
            self::s('Client Training',    'client-training',     'Hands-on workshops and embedded training to lift the team that runs the brand after we leave.', 'teams', '1–4 WEEKS', 'TRAINING', 6),
        ];
    }

    private static function s(string $name, string $slug, string $tagline, string $icon, string $timeline, string $tag, int $sort): array {
        $byslug = [
            'brand-development' => [
                'overview' => 'We build brands that hold up under pressure. Strategy first, identity second, system always. From positioning to naming, voice, and a complete identity system, every piece is designed to ship and outlast a trend cycle.',
                'who_for'  => 'For founders launching their first brand, MDs leading a rebrand, and teams refreshing for a new chapter.',
                'process'  => [
                    ['title' => 'Strategy intensive',     'desc' => 'We compress the discovery into a tight, senior-led week. Positioning, audience map, voice principles.'],
                    ['title' => 'Identity exploration',   'desc' => 'Three considered routes. Not 30. Each one defensible from boardroom to billboard.'],
                    ['title' => 'System build',           'desc' => 'Logos, type, colour, motion, voice — wired together as a working system, not a deck.'],
                    ['title' => 'Rollout & handover',     'desc' => 'Shipped guidelines, working files, and a 30-minute team walkthrough.'],
                ],
                'deliverables' => ['Positioning & messaging matrix','Logo system & brand mark','Type & colour systems','Voice & tone principles','Brand guidelines (PDF + Figma)','Working files (AI/SVG/PNG)'],
            ],
            'creative-design' => [
                'overview' => 'Design that earns its keep. We bring senior craft to editorial systems, packaging, environmental graphics, and digital surfaces — always built to ship, never just to win awards.',
                'who_for'  => 'For brands that need craft-led design execution at speed, without losing strategic intent.',
                'process'  => [
                    ['title' => 'Brief & references', 'desc' => 'A working session that locks scope and removes ambiguity before pencils touch paper.'],
                    ['title' => 'Concept routes',     'desc' => 'Two or three considered directions, each tied back to the brief.'],
                    ['title' => 'Production',         'desc' => 'Final design at production fidelity — print-ready, screen-ready, env-ready.'],
                    ['title' => 'Handover',           'desc' => 'All assets, source files, and a short rationale doc for the team.'],
                ],
                'deliverables' => ['Editorial layouts','Packaging system','Environmental graphics','Digital design','Production-ready files'],
            ],
            'media-solutions' => [
                'overview' => 'In-house production. Real crews, real kit, real production discipline. Photography, film, podcast, and editorial — shot, cut, and delivered against tight production windows.',
                'who_for'  => 'For brands that need owned, on-brand media made at production-house quality without the agency overhead.',
                'process'  => [
                    ['title' => 'Pre-production', 'desc' => 'Shot lists, treatment, locations, talent. Sign-off before the camera moves.'],
                    ['title' => 'Production',     'desc' => 'Senior crews. One day on set is one day delivered.'],
                    ['title' => 'Post',           'desc' => 'Edit, grade, sound, master. All in-house, all tracked daily.'],
                    ['title' => 'Delivery',       'desc' => 'Final masters plus the cuts your channels actually need.'],
                ],
                'deliverables' => ['Photography (lifestyle / product / editorial)','Film (brand / product / event recap)','Podcast production','Editorial features','Master + channel cuts'],
            ],
            'project-event-management' => [
                'overview' => 'When the date is fixed and the room is full, you need a partner who runs production like an operator, not an agency. We deliver brand experiences, summits, and product launches with the same rigour we bring to a build.',
                'who_for'  => 'For founders, marketing leads, and innovation teams running events that absolutely have to land.',
                'process'  => [
                    ['title' => 'Brief & vision',    'desc' => 'Define what success looks like before we touch a venue.'],
                    ['title' => 'Build & rehearse',  'desc' => 'Stages, signage, decks, talent — all rehearsed before doors open.'],
                    ['title' => 'Execute',           'desc' => 'Senior production runs the room. You run the relationships.'],
                    ['title' => 'Recap',             'desc' => 'Editorial recap, recap film, and a stakeholder report inside seven days.'],
                ],
                'deliverables' => ['Event strategy & creative','Production & build','Stage management','Talent & vendor coordination','Recap film + editorial'],
            ],
            'digital-solutions' => [
                'overview' => 'We build the digital surfaces that carry your brand into the world: marketing sites, internal tools, product MVPs. Lean stack, production-ready code, no framework theatre.',
                'who_for'  => 'For founders shipping a first product, brands moving off SaaS templates, and teams needing a senior hand on the codebase.',
                'process'  => [
                    ['title' => 'Architecture',   'desc' => 'Decisions about stack, hosting, and data — made in week one.'],
                    ['title' => 'Design system',  'desc' => 'Components built once, applied everywhere.'],
                    ['title' => 'Build',          'desc' => 'Senior engineers, working software shipped weekly.'],
                    ['title' => 'Launch',         'desc' => 'Domain, analytics, monitoring, handover.'],
                ],
                'deliverables' => ['Marketing site or product MVP','Design system','CMS or admin tooling','Analytics + monitoring setup','Handover docs'],
            ],
            'client-training' => [
                'overview' => 'Strategy and identity only stick when the in-house team can wield them. We run focused training programmes — 1-day intensives or 4-week embedded engagements — that turn brand systems into working muscle.',
                'who_for'  => 'For marketing leads, comms teams, and in-house creatives ready to take the brand forward.',
                'process'  => [
                    ['title' => 'Audit',            'desc' => 'What does the team already know? Where are the real gaps?'],
                    ['title' => 'Programme design', 'desc' => 'A curriculum tailored to the brand and the team.'],
                    ['title' => 'Workshops',        'desc' => 'Hands-on sessions, not slides. Outputs the team keeps.'],
                    ['title' => 'Coaching',         'desc' => 'Optional 30/60/90-day check-ins to keep the muscle warm.'],
                ],
                'deliverables' => ['Skills audit','Custom curriculum','Workshop sessions (live or remote)','Working playbooks','Optional follow-up coaching'],
            ],
        ];
        return array_merge([
            'name' => $name, 'slug' => $slug, 'tagline' => $tagline,
            'icon' => $icon, 'timeline' => $timeline, 'tag' => $tag, 'sort' => $sort,
        ], $byslug[$slug] ?? []);
    }
}
