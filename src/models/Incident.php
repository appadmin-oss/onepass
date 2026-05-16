<?php

/**
 * Incident — read/write model for the public status page.
 *
 * Lifecycle:
 *   - An operator creates an incident the moment a degradation is
 *     confirmed (severity, affected components, optional markdown body).
 *   - StatusController::report() reads active incidents (resolved_at IS
 *     NULL) and overlays their state onto the otherwise "up" component
 *     pills — so the status page is authored from facts, not guessed
 *     from a self-test that could disagree with operator reality.
 *   - When the operator marks the incident resolved, the page snaps
 *     back to green and the incident moves into the history strip
 *     beneath the live components.
 *
 * Components are a CSV of canonical short keys (web, database, lms,
 * jaas, ai). The matcher in StatusController normalises both sides
 * before comparing so case + whitespace don't cause silent misses.
 */
class Incident {
    public const COMPONENTS = ['web', 'database', 'lms', 'jaas', 'ai'];
    public const SEVERITIES = ['minor', 'major', 'critical'];
    public const KINDS      = ['incident', 'maintenance'];

    /**
     * Create a new incident from operator input. Returns the new id, or
     * 0 if the DB isn't reachable. `public_id` is generated server-side
     * from the title + started_at so it's stable across edits.
     *
     * Expected $data keys: title, body, postmortem, kind, severity,
     * components_csv (or components[]), started_at, resolved_at, created_by.
     */
    public static function create(array $data): int {
        if (!Database::available()) return 0;
        $row = self::normalise($data);
        $row['public_id'] = self::uniquePublicId($row['title'], $row['started_at']);
        return (int) Database::insert(
            'INSERT INTO incidents
              (public_id, title, body, postmortem, kind, severity,
               components_csv, started_at, resolved_at, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)',
            [
                $row['public_id'], $row['title'], $row['body'], $row['postmortem'],
                $row['kind'], $row['severity'], $row['components_csv'],
                $row['started_at'], $row['resolved_at'], $row['created_by'],
            ]
        );
    }

    /** Update an existing incident. Returns true on success. */
    public static function update(int $id, array $data): bool {
        if (!Database::available() || $id <= 0) return false;
        $row = self::normalise($data);
        $affected = Database::exec(
            'UPDATE incidents
                SET title = ?, body = ?, postmortem = ?, kind = ?,
                    severity = ?, components_csv = ?, started_at = ?,
                    resolved_at = ?
              WHERE id = ?',
            [
                $row['title'], $row['body'], $row['postmortem'], $row['kind'],
                $row['severity'], $row['components_csv'], $row['started_at'],
                $row['resolved_at'], $id,
            ]
        );
        return $affected >= 0;
    }

    /** Mark resolved now. Returns true if a row flipped. */
    public static function resolve(int $id): bool {
        if (!Database::available() || $id <= 0) return false;
        return Database::exec(
            'UPDATE incidents SET resolved_at = NOW()
              WHERE id = ? AND resolved_at IS NULL',
            [$id]
        ) > 0;
    }

    /** Persist a postmortem draft (AI-generated or operator-typed). */
    public static function setPostmortem(int $id, string $text): bool {
        if (!Database::available() || $id <= 0) return false;
        return Database::exec(
            'UPDATE incidents SET postmortem = ? WHERE id = ?',
            [$text, $id]
        ) >= 0;
    }

    /** All rows for the admin index, newest first. */
    public static function all(int $limit = 100): array {
        if (!Database::available()) return [];
        $limit = max(1, min(500, $limit));
        return Database::all(
            'SELECT * FROM incidents ORDER BY started_at DESC LIMIT ' . $limit
        );
    }

    /**
     * Sanitise + canonicalise operator input. Single source of truth so
     * create() and update() can't drift. Unknown values fall back to
     * sensible defaults; never throws.
     */
    private static function normalise(array $data): array {
        // Components can arrive as csv string OR array; canonicalise both.
        $components = [];
        if (isset($data['components']) && is_array($data['components'])) {
            $components = $data['components'];
        } elseif (isset($data['components_csv'])) {
            $components = explode(',', (string)$data['components_csv']);
        }
        $components = self::parseComponents(implode(',', $components));

        $sev  = (string)($data['severity'] ?? 'minor');
        if (!in_array($sev, self::SEVERITIES, true)) $sev = 'minor';

        $kind = (string)($data['kind'] ?? 'incident');
        if (!in_array($kind, self::KINDS, true)) $kind = 'incident';

        $started = (string)($data['started_at'] ?? '');
        $started = $started !== '' ? date('Y-m-d H:i:s', strtotime($started)) : date('Y-m-d H:i:s');

        $resolved = $data['resolved_at'] ?? null;
        if (is_string($resolved) && trim($resolved) !== '') {
            $resolved = date('Y-m-d H:i:s', strtotime($resolved));
        } else {
            $resolved = null;
        }

        return [
            'title'          => mb_substr(trim((string)($data['title'] ?? '')), 0, 200),
            'body'           => trim((string)($data['body'] ?? '')) ?: null,
            'postmortem'     => trim((string)($data['postmortem'] ?? '')) ?: null,
            'kind'           => $kind,
            'severity'       => $sev,
            'components_csv' => implode(',', $components),
            'started_at'     => $started,
            'resolved_at'    => $resolved,
            'created_by'     => mb_substr(trim((string)($data['created_by'] ?? '')), 0, 80) ?: null,
        ];
    }

    /** Ensure the slug doesn't collide with an existing public_id. */
    private static function uniquePublicId(string $title, string $startedAt): string {
        $base = self::buildPublicId($title, $startedAt);
        if (!Database::available()) return $base;
        $try = $base;
        for ($i = 1; $i < 8; $i++) {
            $exists = Database::one('SELECT 1 FROM incidents WHERE public_id = ?', [$try]);
            if (!$exists) return $try;
            $try = $base . '-' . $i;
        }
        // Last-resort: tack on a fresh hash. Effectively never reached.
        return $base . '-' . substr(sha1(microtime(true) . $title), 0, 4);
    }

    public static function active(): array {
        if (!Database::available()) return [];
        return Database::all(
            'SELECT * FROM incidents WHERE resolved_at IS NULL ORDER BY started_at DESC'
        );
    }

    public static function recent(int $limit = 8): array {
        if (!Database::available()) return [];
        $limit = max(1, min(50, $limit));
        return Database::all(
            'SELECT * FROM incidents
             WHERE resolved_at IS NOT NULL
             ORDER BY started_at DESC LIMIT ' . $limit
        );
    }

    /**
     * Returns ['web' => 'up'|'degraded'|'down', ...] based on the
     * current active-incidents set. A component is "down" when at
     * least one critical-severity incident lists it; "degraded" when
     * any active incident does. Unaffected components stay "up".
     */
    public static function componentStateMap(): array {
        $map = array_fill_keys(self::COMPONENTS, 'up');
        foreach (self::active() as $row) {
            $components = self::parseComponents((string)($row['components_csv'] ?? ''));
            $sev = (string)($row['severity'] ?? 'minor');
            foreach ($components as $c) {
                if (!isset($map[$c])) continue;
                if ($sev === 'critical') $map[$c] = 'down';
                elseif ($map[$c] !== 'down') $map[$c] = 'degraded';
            }
        }
        return $map;
    }

    public static function parseComponents(string $csv): array {
        $out = [];
        foreach (explode(',', strtolower($csv)) as $p) {
            $p = trim($p);
            if ($p !== '' && in_array($p, self::COMPONENTS, true)) $out[] = $p;
        }
        return array_values(array_unique($out));
    }

    public static function find(int $id): ?array {
        if (!Database::available() || $id <= 0) return null;
        return Database::one('SELECT * FROM incidents WHERE id = ? LIMIT 1', [$id]);
    }

    /** Public-id is a stable URL slug for the per-incident permalink. */
    public static function byPublicId(string $publicId): ?array {
        if (!Database::available() || $publicId === '') return null;
        $publicId = preg_replace('/[^A-Za-z0-9\-_]/', '', $publicId);
        if ($publicId === '') return null;
        return Database::one(
            'SELECT * FROM incidents WHERE public_id = ? LIMIT 1',
            [$publicId]
        );
    }

    /**
     * Build a stable public_id from a title. We never reveal the
     * incident's numeric id in the URL — collisions are resolved by
     * appending a 4-char hash of the row creation timestamp.
     */
    public static function buildPublicId(string $title, ?string $startedAt = null): string {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        $slug = substr($slug, 0, 48);
        $h = substr(sha1(($startedAt ?: gmdate('c'))), 0, 4);
        return $slug === '' ? 'incident-' . $h : $slug . '-' . $h;
    }

    /**
     * 90-day timeline per component. Same overlap-walk as last24h() but
     * the bucket is one day. Cheap: one query against incidents WHERE
     * (resolved_at IS NULL OR resolved_at >= now-90d) AND started_at <= now.
     *
     * Returns: ['<componentKey>' => ['up'|'degraded'|'down', ...90]]
     *          newest day LAST so it reads left-to-right as time-forward.
     */
    public static function last90d(): array {
        $tl = [];
        foreach (self::COMPONENTS as $c) $tl[$c] = array_fill(0, 90, 'up');
        if (!Database::available()) return $tl;
        $now    = time();
        $window = $now - 90 * 86400;
        $rows = Database::all(
            'SELECT components_csv, severity, started_at, resolved_at
             FROM incidents
             WHERE (resolved_at IS NULL OR resolved_at >= ?)
               AND started_at <= ?',
            [date('Y-m-d H:i:s', $window), date('Y-m-d H:i:s', $now)]
        );
        foreach ($rows as $r) {
            $start = max(strtotime((string)$r['started_at']), $window);
            $end   = $r['resolved_at'] ? min(strtotime((string)$r['resolved_at']), $now) : $now;
            if ($end <= $start) continue;
            $startSeg = max(0, min(89, (int) floor(($start - $window) / 86400)));
            $endSeg   = max(0, min(89, (int) floor(($end   - $window) / 86400)));
            $worst    = $r['severity'] === 'critical' ? 'down' : 'degraded';
            foreach (self::parseComponents((string)$r['components_csv']) as $c) {
                for ($i = $startSeg; $i <= $endSeg; $i++) {
                    if ($tl[$c][$i] === 'down') continue;
                    if ($worst === 'down') $tl[$c][$i] = 'down';
                    elseif ($tl[$c][$i] !== 'down') $tl[$c][$i] = 'degraded';
                }
            }
        }
        return $tl;
    }

    /**
     * Uptime percentage for a row of state cells. Counts "up" only —
     * any non-up state docks the day. One decimal precision; never
     * shows 100.0 unless every cell is up. Returns float 0–100.
     */
    public static function uptimePercent(array $cells): float {
        if (!$cells) return 100.0;
        $up = 0;
        foreach ($cells as $c) if ($c === 'up') $up++;
        return round(($up / count($cells)) * 1000) / 10;
    }

    /** Active future-dated scheduled maintenance windows. */
    public static function scheduledUpcoming(int $limit = 6): array {
        if (!Database::available()) return [];
        $limit = max(1, min(50, $limit));
        return Database::all(
            'SELECT * FROM incidents
             WHERE kind = "maintenance" AND started_at > NOW()
             ORDER BY started_at ASC LIMIT ' . $limit
        );
    }

    /**
     * Compute a 24-segment "last 24 hours" timeline per component for
     * the uptime hairlines on the status page. Each segment is 1 hour;
     * the value is 'up' | 'degraded' | 'down' based on any incident
     * that overlapped that hour. Cheap: one query, in-memory walk.
     */
    public static function last24h(): array {
        $tl = [];
        foreach (self::COMPONENTS as $c) $tl[$c] = array_fill(0, 24, 'up');
        if (!Database::available()) return $tl;
        $now    = time();
        $window = $now - 24 * 3600;
        $rows = Database::all(
            'SELECT components_csv, severity, started_at, resolved_at
             FROM incidents
             WHERE (resolved_at IS NULL OR resolved_at >= ?)
               AND started_at <= ?',
            [date('Y-m-d H:i:s', $window), date('Y-m-d H:i:s', $now)]
        );
        foreach ($rows as $r) {
            $start = max(strtotime((string)$r['started_at']), $window);
            $end   = $r['resolved_at'] ? min(strtotime((string)$r['resolved_at']), $now) : $now;
            if ($end <= $start) continue;
            $startSeg = max(0, min(23, (int) floor(($start - $window) / 3600)));
            $endSeg   = max(0, min(23, (int) floor(($end   - $window) / 3600)));
            $worst    = $r['severity'] === 'critical' ? 'down' : 'degraded';
            foreach (self::parseComponents((string)$r['components_csv']) as $c) {
                for ($i = $startSeg; $i <= $endSeg; $i++) {
                    if ($tl[$c][$i] === 'down') continue;
                    if ($worst === 'down') $tl[$c][$i] = 'down';
                    elseif ($tl[$c][$i] !== 'down') $tl[$c][$i] = 'degraded';
                }
            }
        }
        return $tl;
    }
}
