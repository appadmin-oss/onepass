<?php

/**
 * StatusController — production-grade public status surface.
 *
 *   GET  /status                 → HTML overview with live polling
 *   GET  /status/health.json     → machine-readable, monitor-friendly
 *   GET  /status/badge.svg       → embeddable SVG badge (for READMEs)
 *   POST /status/subscribe       → email subscribe for outage notes
 *   GET  /status/ai-summary      → 3-line AI summary of active incidents
 *                                  (called lazily by the HTML page)
 *
 * Status is computed from TWO sources:
 *   1. Real self-tests (Database, LMS, Jitsi, AI). These return up by
 *      default and degrade themselves on connectivity issues.
 *   2. Operator-authored incidents (Incident::active()). An active
 *      incident overlays its severity onto the listed components so
 *      the page always reflects the operator's view of reality, not
 *      a noisy self-test against a third party.
 *
 * The HTML page polls /status/health.json every 30s to refresh state
 * in place — no full reloads — and asks for the AI summary once on
 * load (and again whenever overall != 'up').
 */
class StatusController extends Controller {

    /**
     * Component grouping for the redesigned status page. Maps a human
     * group label to the component-key set inside Incident::COMPONENTS.
     * The order is meaningful: visitors read top-down, so the most
     * customer-facing surfaces come first.
     */
    public const STATUS_GROUPS = [
        'Public site' => ['web'],
        'Academy'     => ['lms', 'jaas'],
        'Sign-in'     => ['database'],
        'AI mentor'   => ['ai'],
    ];

    /** Plain-language dependency ledger surfaced at the foot of /status. */
    public const DEPENDENCIES = [
        ['Pollinations.ai', 'AI mentor responses. If down, the racing fallback (Hack Club) picks up.'],
        ['Hack Club AI',     'Second AI provider. Both have to be down before AI mentor goes offline.'],
        ['Firebase Auth',    'Google sign-in only. Email + 2FA + magic-link sign-in keep working.'],
        ['jsDelivr CDN',     'Hosts a few client libraries (Chart.js, Marked, DOMPurify). Pages degrade gracefully.'],
        ['Moodle host',      'Courseware + LMS. Without it, students see the seeded catalogue.'],
        ['Jitsi / 8x8',      'Live class rooms. Falls back to public meet.jit.si when JaaS is unconfigured.'],
        ['HIBP API',         'Breached-password check on sign-up. Fail-open if down — never blocks a sign-up.'],
    ];

    public function index(): void {
        $report   = $this->report((bool)($_GET['deep'] ?? false));
        $tl24h    = Incident::last24h();
        $tl90d    = Incident::last90d();
        $this->view('pages/status/index', [
            'title'       => 'Status · Afrostrength',
            'description' => 'Live system status for Afrostrength, Afrotech Academy, and the free tools.',
            'report'      => $report,
            'timeline'    => $tl24h,                // legacy 24h for the existing JSON shape
            'timeline_90d'=> $tl90d,                // new 90-day grid
            'groups'      => self::STATUS_GROUPS,
            'incidents'   => Incident::active(),
            'scheduled'   => Incident::scheduledUpcoming(6),
            'recent'      => Incident::recent(6),
            'deps'        => self::DEPENDENCIES,
            'subscribed'  => (string)($_GET['subscribed'] ?? '') === '1',
            'breadcrumbs' => [
                ['label' => 'Home',   'href' => url('/')],
                ['label' => 'Status'],
            ],
        ], 'main');
    }

    /**
     * GET /status/incidents/{code} — per-incident permalink. Markdown
     * body + postmortem (when present) render via Marked + DOMPurify.
     */
    public function incidentShow(string $code = ''): void {
        $row = Incident::byPublicId($code);
        if (!$row) { $this->notFound(); return; }
        $this->view('pages/status/incident', [
            'title'       => $row['title'] . ' · Status · Afrostrength',
            'description' => 'Incident details: ' . $row['title'],
            'incident'    => $row,
            'breadcrumbs' => [
                ['label' => 'Home',   'href' => url('/')],
                ['label' => 'Status', 'href' => url('/status')],
                ['label' => $row['title']],
            ],
        ], 'main');
    }

    /**
     * GET /status/embed — minimal iframe-friendly card for partner sites.
     * 320×60. Inline CSS only. No JS. Cache-Control max-age=30.
     */
    public function embed(): void {
        $report = $this->report(false);
        // Render outside the normal layout — partners want the smallest
        // possible payload. We send an X-Frame-Options ALLOWALL header
        // (Apache won't add SAMEORIGIN since we set it explicitly).
        if (!headers_sent()) {
            // Allow third-party framing for this single route. The
            // global X-Frame-Options: SAMEORIGIN comes from Apache;
            // explicit headers here override for this response only.
            header_remove('X-Frame-Options');
            header('Content-Security-Policy: frame-ancestors *');
            header('Cache-Control: public, max-age=30');
            header('Content-Type: text/html; charset=utf-8');
        }
        $state = (string)($report['overall'] ?? 'up');
        [$label, $colour] = match ($state) {
            'up'       => ['All systems normal',     '#1F8A5B'],
            'degraded' => ['Partial degradation',    '#B8780F'],
            'down'     => ['Major outage',           '#C0392B'],
            default    => ['Unknown',                '#777777'],
        };
        $url = rtrim(AFS_URL, '/') . '/status';
        // Tiny self-contained card. Inline CSS only. Loads under 4 kB.
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
           . '<meta name="viewport" content="width=device-width,initial-scale=1">'
           . '<title>Afrostrength · status</title>'
           . '<style>html,body{margin:0;padding:0;background:#FAFAFA;font:14px/1.4 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#0A0A0A;}'
           . '.card{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid rgba(10,10,10,.10);border-radius:12px;}'
           . '.dot{width:10px;height:10px;border-radius:50%;background:' . $colour . ';box-shadow:0 0 0 3px ' . $colour . '22;flex:0 0 10px}'
           . '.label{font-weight:600}'
           . '.brand{margin-left:auto;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#777}'
           . 'a{color:inherit;text-decoration:none;display:block}</style></head>'
           . '<body><a href="' . htmlspecialchars($url, ENT_QUOTES) . '" target="_top">'
           . '<div class="card"><span class="dot" aria-hidden="true"></span>'
           . '<span class="label">' . htmlspecialchars($label) . '</span>'
           . '<span class="brand">Afrostrength</span></div></a></body></html>';
    }

    /** GET /status/feed.rss — last 20 incidents, RSS 2.0. */
    public function rssFeed(): void {
        $items = $this->feedItems(20);
        if (!headers_sent()) {
            header('Content-Type: application/rss+xml; charset=utf-8');
            header('Cache-Control: public, max-age=300');
        }
        $base = rtrim(AFS_URL, '/');
        $dom = new DOMDocument('1.0', 'UTF-8');
        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $dom->appendChild($rss);
        $channel = $dom->createElement('channel');
        $rss->appendChild($channel);
        $channel->appendChild($dom->createElement('title', 'Afrostrength · status incidents'));
        $channel->appendChild($dom->createElement('link', $base . '/status'));
        $desc = $dom->createElement('description');
        $desc->appendChild($dom->createCDATASection('Active and resolved incidents for the Afrostrength studio + Afrotech Academy.'));
        $channel->appendChild($desc);
        foreach ($items as $r) {
            $item = $dom->createElement('item');
            $item->appendChild($dom->createElement('title', $r['title']));
            $item->appendChild($dom->createElement('link',  $r['url']));
            $item->appendChild($dom->createElement('guid',  $r['url']));
            $item->appendChild($dom->createElement('pubDate', $r['rfc822']));
            $body = $dom->createElement('description');
            $body->appendChild($dom->createCDATASection($r['summary']));
            $item->appendChild($body);
            $channel->appendChild($item);
        }
        echo $dom->saveXML();
    }

    /** GET /status/feed.atom — same items, Atom 1.0. */
    public function atomFeed(): void {
        $items = $this->feedItems(20);
        if (!headers_sent()) {
            header('Content-Type: application/atom+xml; charset=utf-8');
            header('Cache-Control: public, max-age=300');
        }
        $base = rtrim(AFS_URL, '/');
        $dom = new DOMDocument('1.0', 'UTF-8');
        $feed = $dom->createElement('feed');
        $feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $dom->appendChild($feed);
        $feed->appendChild($dom->createElement('title', 'Afrostrength · status incidents'));
        $link = $dom->createElement('link');
        $link->setAttribute('href', $base . '/status');
        $feed->appendChild($link);
        $feed->appendChild($dom->createElement('id', $base . '/status'));
        $feed->appendChild($dom->createElement('updated', gmdate('c')));
        foreach ($items as $r) {
            $entry = $dom->createElement('entry');
            $entry->appendChild($dom->createElement('title', $r['title']));
            $l = $dom->createElement('link');
            $l->setAttribute('href', $r['url']);
            $entry->appendChild($l);
            $entry->appendChild($dom->createElement('id', $r['url']));
            $entry->appendChild($dom->createElement('updated', $r['iso8601']));
            $sum = $dom->createElement('summary');
            $sum->appendChild($dom->createTextNode($r['summary']));
            $entry->appendChild($sum);
            $feed->appendChild($entry);
        }
        echo $dom->saveXML();
    }

    private function feedItems(int $limit): array {
        if (!Database::available()) return [];
        $rows = Database::all(
            'SELECT * FROM incidents ORDER BY started_at DESC LIMIT ' . max(1, min(100, $limit))
        );
        $base = rtrim(AFS_URL, '/');
        $out = [];
        foreach ($rows as $r) {
            $code = (string)($r['public_id'] ?? '');
            $url  = $code !== '' ? $base . '/status/incidents/' . $code : $base . '/status';
            $sum  = ($r['resolved_at'] ? 'Resolved · ' : 'Active · ')
                  . ucfirst((string)$r['severity']) . ' · '
                  . substr((string)($r['body'] ?? ''), 0, 240);
            $out[] = [
                'title'   => (string)$r['title'],
                'url'     => $url,
                'rfc822'  => date(DATE_RSS,  strtotime((string)$r['started_at'])),
                'iso8601' => date(DATE_ATOM, strtotime((string)$r['started_at'])),
                'summary' => $sum,
            ];
        }
        return $out;
    }

    /** GET /status/api — static documentation page (no live data). */
    public function api(): void {
        $this->view('pages/status/api', [
            'title'       => 'Status API · Afrostrength',
            'description' => 'JSON, RSS, Atom, and SVG badge endpoints for the Afrostrength status page.',
            'breadcrumbs' => [
                ['label' => 'Home',   'href' => url('/')],
                ['label' => 'Status', 'href' => url('/status')],
                ['label' => 'API'],
            ],
        ], 'main');
    }

    /** Square 64×64 SVG badge variant for compact embeds. */
    public function badgeSquare(): void {
        $r = $this->report(false);
        [$label, $colour, $fg] = match ($r['overall']) {
            'up'       => ['UP',  '#1F8A5B', '#FFFFFF'],
            'degraded' => ['DEG', '#B8780F', '#FFFFFF'],
            'down'     => ['OUT', '#C0392B', '#FFFFFF'],
            default    => ['?',   '#777777', '#FFFFFF'],
        };
        if (!headers_sent()) {
            header('Content-Type: image/svg+xml; charset=utf-8');
            header('Cache-Control: public, max-age=60');
        }
        echo sprintf(
            '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" role="img" aria-label="afrostrength status %s">'
          . '<rect width="64" height="64" rx="10" fill="#0A0A0A"/>'
          . '<circle cx="32" cy="22" r="6" fill="%s"/>'
          . '<text x="32" y="52" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="11" font-weight="600" fill="%s">%s</text>'
          . '</svg>',
            htmlspecialchars($label, ENT_XML1), $colour, $fg, htmlspecialchars($label, ENT_XML1)
        );
    }

    /** Dark-tone badge variant for white-on-dark partner sites. */
    public function badgeDark(): void {
        // Identical structure to badge() but inverted colour scheme.
        $r = $this->report(false);
        [$label, $colour] = match ($r['overall']) {
            'up'       => ['operational', '#1F8A5B'],
            'degraded' => ['degraded',    '#B8780F'],
            'down'     => ['outage',      '#C0392B'],
            default    => ['unknown',     '#777777'],
        };
        $w = 134; $labelW = 64; $stateW = $w - $labelW;
        $svg = sprintf(
            '<?xml version="1.0"?>'
          . '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="20" role="img" aria-label="afrostrength status: %3$s">'
          .   '<mask id="m"><rect width="%1$d" height="20" rx="3" fill="#fff"/></mask>'
          .   '<g mask="url(#m)">'
          .     '<rect width="%2$d" height="20" fill="#FFFFFF"/>'
          .     '<rect x="%2$d" width="%4$d" height="20" fill="%5$s"/>'
          .   '</g>'
          .   '<g font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="11" text-anchor="middle">'
          .     '<text x="%6$d" y="14" fill="#0A0A0A">afrostrength</text>'
          .     '<text x="%7$d" y="14" fill="#FFFFFF">%3$s</text>'
          .   '</g>'
          . '</svg>',
            $w, $labelW, htmlspecialchars($label, ENT_XML1),
            $stateW, $colour,
            (int) round($labelW / 2),
            (int) round($labelW + ($stateW / 2))
        );
        if (!headers_sent()) {
            header('Content-Type: image/svg+xml; charset=utf-8');
            header('Cache-Control: public, max-age=60');
        }
        echo $svg;
    }

    /** GET /status/health.json — same data, machine-readable. */
    public function healthJson(): void {
        $report = $this->report((bool)($_GET['deep'] ?? false));
        $report['timeline']  = Incident::last24h();
        $report['incidents'] = Incident::active();
        header('Cache-Control: private, no-cache, max-age=15');
        $this->json($report);
    }

    /** GET /status/badge.svg — small embeddable badge for READMEs. */
    public function badge(): void {
        $r = $this->report(false);
        [$label, $colour] = match ($r['overall']) {
            'up'       => ['operational', '#10B981'],
            'degraded' => ['degraded',    '#EAB308'],
            'down'     => ['outage',      '#C0392B'],
            default    => ['unknown',     '#777777'],
        };
        $w = 134;   // 64 + 70 ish
        $labelW = 64;
        $stateW = $w - $labelW;
        $svg = sprintf(
            '<?xml version="1.0"?>'
          . '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="20" role="img" aria-label="afrostrength status: %3$s">'
          .   '<linearGradient id="b" x2="0" y2="100%%"><stop offset="0" stop-color="#bbb" stop-opacity=".1"/><stop offset="1" stop-opacity=".1"/></linearGradient>'
          .   '<mask id="m"><rect width="%1$d" height="20" rx="3" fill="#fff"/></mask>'
          .   '<g mask="url(#m)">'
          .     '<rect width="%2$d" height="20" fill="#0A0A0A"/>'
          .     '<rect x="%2$d" width="%4$d" height="20" fill="%5$s"/>'
          .     '<rect width="%1$d" height="20" fill="url(#b)"/>'
          .   '</g>'
          .   '<g fill="#fff" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="11" text-anchor="middle">'
          .     '<text x="%6$d" y="14">afrostrength</text>'
          .     '<text x="%7$d" y="14">%3$s</text>'
          .   '</g>'
          . '</svg>',
            $w, $labelW, htmlspecialchars($label, ENT_XML1),
            $stateW, $colour,
            (int) round($labelW / 2),
            (int) round($labelW + ($stateW / 2))
        );
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Cache-Control: public, max-age=60');
        echo $svg;
    }

    /** POST /status/subscribe — collect an email for incident notices. */
    public function subscribe(): void {
        Csrf::require();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        if (!Security::honeypotOk('website')) {
            if ($isAjax) { $this->json(['ok' => true]); return; }
            $this->redirect('/status?subscribed=1'); return;
        }
        if (!Security::rateLimit('status_sub_' . Security::clientIp(), 4, 600)) {
            if ($isAjax) { $this->json(['error' => 'rate_limited'], 429); return; }
            $this->redirect('/status'); return;
        }
        $email = (string) $this->input('email', '');
        $r = StatusSubscriber::add($email);
        if ($isAjax) { $this->json($r['ok'] ? ['ok' => true] : ['error' => $r['error']], $r['ok'] ? 200 : 422); return; }
        $this->redirect('/status?subscribed=' . ($r['ok'] ? '1' : '0'));
    }

    /**
     * GET /status/ai-summary — short AI-authored briefing of active
     * incidents, returned as JSON. Called by the HTML page after load.
     * Cheap to skip entirely (the page degrades gracefully).
     */
    public function aiSummary(): void {
        if (!Ai::enabled()) { $this->json(['ok' => false, 'reason' => 'disabled']); return; }
        $active = Incident::active();
        if (!$active) {
            $this->json(['ok' => true, 'text' => 'All systems are reporting normal. No active incidents.']);
            return;
        }
        // Light per-IP throttle so a stuck page doesn't burn rate-limit budget.
        if (!Security::rateLimit('status_ai_' . Security::clientIp(), 6, 600)) {
            $this->json(['ok' => false, 'reason' => 'throttled']); return;
        }
        $snapshot = array_map(fn($r) => [
            'title'      => (string)$r['title'],
            'severity'   => (string)$r['severity'],
            'components' => (string)$r['components_csv'],
            'started_at' => (string)$r['started_at'],
            'body'       => mb_substr((string)($r['body'] ?? ''), 0, 400),
        ], $active);

        // Run through the canonical entry point so the call picks up the
        // budget guard, cache, and observability log. The system prompt
        // is owned by AiRegistry::INTENTS['status_impact_plain'].
        $res = Ai::run('status_impact_plain', json_encode(
            ['active_incidents' => $snapshot],
            JSON_UNESCAPED_UNICODE
        ));
        $this->json([
            'ok'   => (bool)$res['ok'],
            'text' => $res['ok'] ? trim((string)$res['text']) : ($res['fallback'] ?? 'Unable to summarise — see incident list below.'),
            'provider' => $res['provider'] ?? '',
        ]);
    }

    // ----- core report -----------------------------------------------

    private function report(bool $deep): array {
        $byKey = ['web' => 'Web', 'database' => 'Database', 'lms' => 'LMS (Moodle)',
                  'jaas' => 'Live classes (Jitsi)', 'ai' => 'AI mentor'];
        $services = [];

        // Web (implicit)
        $services['web'] = [
            'key'    => 'web',
            'name'   => $byKey['web'],
            'state'  => 'up',
            'detail' => 'Serving requests. Build ' . substr(self::rev(), 0, 7) . '.',
        ];

        // Database
        try {
            $up = Database::available();
            $services['database'] = [
                'key' => 'database', 'name' => $byKey['database'],
                'state'  => $up ? 'up' : 'degraded',
                'detail' => $up ? 'Reachable.' : 'Unreachable — running on seeded fallback content.',
            ];
        } catch (Throwable $e) {
            $services['database'] = ['key' => 'database', 'name' => $byKey['database'], 'state' => 'down', 'detail' => 'Connection error.'];
        }

        // LMS
        try {
            $lms = Lms::selfTest();
            $services['lms'] = [
                'key' => 'lms', 'name' => $byKey['lms'],
                'state'  => ($lms['ok'] ?? false) ? 'up' : (($lms['mode'] ?? '') === 'demo' ? 'degraded' : 'down'),
                'detail' => (string)($lms['detail'] ?? '—'),
            ];
        } catch (Throwable $e) {
            $services['lms'] = ['key' => 'lms', 'name' => $byKey['lms'], 'state' => 'down', 'detail' => 'Self-test failed.'];
        }

        // JaaS
        try {
            $mode = Jaas::mode();
            $services['jaas'] = [
                'key' => 'jaas', 'name' => $byKey['jaas'],
                'state' => 'up',
                'detail' => $mode === 'jaas' ? 'JaaS tenant active.' : 'Open-source meet.jit.si fallback.',
            ];
        } catch (Throwable $e) {
            $services['jaas'] = ['key' => 'jaas', 'name' => $byKey['jaas'], 'state' => 'down', 'detail' => 'Self-test failed.'];
        }

        // AI
        try {
            if (!Ai::enabled()) {
                $services['ai'] = ['key' => 'ai', 'name' => $byKey['ai'], 'state' => 'down', 'detail' => 'Disabled via AI_ENABLED=0.'];
            } elseif ($deep) {
                $r = Ai::selfTest();
                $providers = array_map(
                    fn($p) => $p['name'] . ' ' . ($p['ok'] ? '(' . $p['ms'] . 'ms)' : '— ' . $p['detail']),
                    $r['providers']
                );
                $services['ai'] = [
                    'key' => 'ai', 'name' => $byKey['ai'],
                    'state'  => $r['ok'] ? 'up' : 'degraded',
                    'detail' => $r['detail'] . ' ' . implode(' · ', $providers),
                ];
            } else {
                $s = Ai::status();
                $services['ai'] = [
                    'key' => 'ai', 'name' => $byKey['ai'],
                    'state' => 'up',
                    'detail' => 'Configured · ' . implode(' + ', array_map(fn($p) => $p['name'], $s['providers']))
                             . ' (' . ($s['racing'] ? 'racing' : 'fallback') . ').',
                ];
            }
        } catch (Throwable $e) {
            $services['ai'] = ['key' => 'ai', 'name' => $byKey['ai'], 'state' => 'down', 'detail' => 'Self-test threw.'];
        }

        // Overlay operator-authored incidents on top of self-tests.
        $overlay = Incident::componentStateMap();
        foreach ($services as $key => $svc) {
            $worst = self::worse($svc['state'], $overlay[$key] ?? 'up');
            if ($worst !== $svc['state']) {
                $services[$key]['state']  = $worst;
                $services[$key]['detail'] = 'Active incident affecting this component — see below.';
            }
        }

        $services = array_values($services);

        // Overall
        $down     = count(array_filter($services, fn($s) => $s['state'] === 'down'));
        $degraded = count(array_filter($services, fn($s) => $s['state'] === 'degraded'));
        $overall  = $down > 0 ? 'down' : ($degraded > 0 ? 'degraded' : 'up');

        return [
            'overall'   => $overall,
            'checkedAt' => date('c'),
            'rev'       => substr(self::rev(), 0, 7),
            'services'  => $services,
        ];
    }

    private static function worse(string $a, string $b): string {
        $rank = ['up' => 0, 'degraded' => 1, 'down' => 2];
        return ($rank[$a] ?? 0) >= ($rank[$b] ?? 0) ? $a : $b;
    }

    /**
     * Stable per-build rev. Hashes the mtime+size of index.php so an
     * operator can verify a deploy without us exposing a git SHA.
     */
    private static function rev(): string {
        static $rev = null;
        if ($rev !== null) return $rev;
        $f = AFS_ROOT . '/index.php';
        $rev = is_file($f) ? sha1(filemtime($f) . filesize($f)) : sha1(AFS_START);
        return $rev;
    }
}
