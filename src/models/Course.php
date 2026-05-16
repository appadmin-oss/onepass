<?php

class Course {
    public static function all(bool $publishedOnly = true): array {
        // Prefer the live LMS catalog when Moodle is wired up
        if (Lms::enabled()) {
            $remote = self::fromMoodle();
            if (!empty($remote)) return $remote;
        }
        if (Database::available()) {
            try {
                $sql = 'SELECT * FROM courses' . ($publishedOnly ? " WHERE status = 'published'" : '') . ' ORDER BY id';
                $rows = Database::all($sql);
                if ($rows) return $rows;
            } catch (Throwable $e) { /* ignore */ }
        }
        return self::seed();
    }

    /**
     * Pull the published catalog from Moodle. Maps Moodle's
     * core_course_get_courses_by_field response onto our row shape.
     */
    private static function fromMoodle(): array {
        $resp = Lms::call('core_course_get_courses_by_field', []);
        if (empty($resp['courses'])) return [];
        $out = [];
        foreach ($resp['courses'] as $c) {
            if ((int)$c['id'] === 1) continue; // skip Moodle's "site" course
            if (($c['visible'] ?? 1) == 0) continue;
            $level = self::levelFromCategoryName($c['categoryname'] ?? '');
            $out[] = [
                'id'           => (int)$c['id'],
                'title'        => $c['fullname'] ?? '',
                'slug'         => $c['shortname'] ?: ('course-' . $c['id']),
                'level'        => $level,
                'weeks'        => self::weeksFromSummary($c['summary'] ?? ''),
                'instructor'   => '',  // populated client-side via additional call if needed
                'price_naira'  => 0,
                'summary'      => strip_tags($c['summary'] ?? ''),
                'body'         => $c['summary'] ?? '',
                'lms_id'       => (int)$c['id'],
                'lms_url'      => Lms::courseUrl((int)$c['id']),
                'enrol_url'    => Lms::enrolUrl((int)$c['id']),
                'status'       => 'published',
            ];
        }
        return $out;
    }

    private static function levelFromCategoryName(string $cat): string {
        $cat = strtolower($cat);
        if (str_contains($cat, 'advanced'))     return 'Advanced';
        if (str_contains($cat, 'intermediate')) return 'Intermediate';
        return 'Foundations';
    }

    private static function weeksFromSummary(string $summary): int {
        if (preg_match('/(\d+)\s*weeks?/i', $summary, $m)) return (int)$m[1];
        return 6;
    }

    public static function find(string $slug): ?array {
        foreach (self::all() as $c) if (($c['slug'] ?? '') === $slug) return $c;
        return null;
    }

    public static function seed(): array {
        // Afrotech Academy tracks — global tech certifications.
        // "LEARN SMARTLY. BUILD CLEANLY. GET CERTIFIED."
        return [
            ['title' => 'Cloud Engineering', 'slug' => 'cloud-engineering',
             'level' => 'Intermediate', 'weeks' => 12, 'instructor' => 'Dami Ojo',
             'price_naira' => 250000, 'live' => true,
             'summary' => 'Master cloud infrastructure and deploy production workloads on AWS, Azure, and GCP — backed by AWS, Microsoft, and CompTIA certifications.',
             'body' => '<p>Twelve weeks. Live cohort. Built for engineers ready to ship to production at scale.</p>'],
            ['title' => 'Cybersecurity', 'slug' => 'cybersecurity',
             'level' => 'Intermediate', 'weeks' => 10, 'instructor' => 'Adaeze Okeke',
             'price_naira' => 280000, 'live' => true,
             'summary' => 'Defend modern systems. Threat modelling, network defence, incident response — pathway to CompTIA Security+ and Cisco CCNA-Sec.',
             'body' => '<p>Ten weeks. Live cohort. Hands-on labs every week.</p>'],
            ['title' => 'Data Analysis', 'slug' => 'data-analysis',
             'level' => 'Foundations', 'weeks' => 8, 'instructor' => 'Tope Adeyemi',
             'price_naira' => 180000, 'live' => false,
             'summary' => 'Transform complex datasets into actionable insights using SQL, Python, and modern visualisation tools.',
             'body' => '<p>Eight weeks. Built for analysts moving past spreadsheets.</p>'],
            ['title' => 'Software Development', 'slug' => 'software-development',
             'level' => 'Advanced', 'weeks' => 16, 'instructor' => 'Kemi Balogun',
             'price_naira' => 320000, 'live' => true,
             'summary' => 'End-to-end full-stack engineering. Modern frameworks, testing discipline, deployment, and code review — taught the way real teams ship.',
             'body' => '<p>Sixteen weeks. Live cohort. Pathway to ₦500K–₦2M+/month roles.</p>'],
            ['title' => 'AI / ML Engineering', 'slug' => 'ai-ml-engineering',
             'level' => 'Advanced', 'weeks' => 14, 'instructor' => 'Dami Ojo',
             'price_naira' => 360000, 'live' => true,
             'summary' => 'Build and deploy advanced AI/ML models for a secure and analytical future. From classical ML to LLM application engineering.',
             'body' => '<p>Fourteen weeks. Live cohort. Capstone is a deployed model.</p>'],
            ['title' => 'Frontend Development', 'slug' => 'frontend-development',
             'level' => 'Foundations', 'weeks' => 10, 'instructor' => 'Tope Adeyemi',
             'price_naira' => 180000, 'live' => false,
             'summary' => 'HTML, CSS, JS · UI/UX responsive design · modern frontend frameworks. Build the art of stunning visuals with powerful code.',
             'body' => '<p>Ten weeks. Build a portfolio of shipped projects.</p>'],
            ['title' => 'Animation', 'slug' => 'animation',
             'level' => 'Intermediate', 'weeks' => 8, 'instructor' => 'Tope Adeyemi',
             'price_naira' => 200000, 'live' => false,
             'summary' => 'Create captivating motion graphics and 2D/3D visual stories that move audiences and brands.',
             'body' => '<p>Eight weeks. Tools-agnostic — built around principles.</p>'],
            ['title' => 'Design (UI/UX)', 'slug' => 'design',
             'level' => 'Foundations', 'weeks' => 8, 'instructor' => 'Adaeze Okeke',
             'price_naira' => 180000, 'live' => false,
             'summary' => 'Master visual hierarchy and branding to create stunning aesthetics across product, web, and brand surfaces.',
             'body' => '<p>Eight weeks. Built around live critique sessions.</p>'],
            ['title' => 'Digital Marketing', 'slug' => 'digital-marketing',
             'level' => 'Foundations', 'weeks' => 6, 'instructor' => 'Kemi Balogun',
             'price_naira' => 150000, 'live' => false,
             'summary' => 'Drive growth through SEO, social, content, and paid media — backed by frameworks and live campaign work.',
             'body' => '<p>Six weeks. Run a live campaign as your capstone.</p>'],
        ];
    }
}
