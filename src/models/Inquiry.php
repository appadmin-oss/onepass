<?php

class Inquiry {
    public static function create(array $data): int {
        if (!Database::available()) {
            // Soft-fail to a JSON log
            $dir = AFS_ROOT . '/storage'; if (!is_dir($dir)) @mkdir($dir, 0775, true);
            @file_put_contents($dir . '/inquiries.log', json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
            return 0;
        }
        $sql = 'INSERT INTO inquiries
            (kind, name, email, phone, company, inquiry_type, service, brand_stage, event_size, event_date, preferred_time, message, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "new")';
        return (int) Database::insert($sql, [
            $data['kind']           ?? 'contact',
            $data['name']           ?? '',
            $data['email']          ?? '',
            $data['phone']          ?? null,
            $data['company']        ?? null,
            $data['inquiry_type']   ?? null,
            $data['service']        ?? null,
            $data['brand_stage']    ?? null,
            $data['event_size']     ?? null,
            $data['event_date']     ?: null,
            $data['preferred_time'] ?? null,
            $data['message']        ?? null,
        ]);
    }

    public static function all(?string $status = null): array {
        if (!Database::available()) return [];
        if ($status) return Database::all('SELECT * FROM inquiries WHERE status = ? ORDER BY created_at DESC', [$status]);
        return Database::all('SELECT * FROM inquiries ORDER BY created_at DESC');
    }

    public static function find(int $id): ?array {
        if (!Database::available()) return null;
        return Database::one('SELECT * FROM inquiries WHERE id = ?', [$id]);
    }

    public static function setStatus(int $id, string $status): void {
        if (!Database::available()) return;
        Database::exec('UPDATE inquiries SET status = ? WHERE id = ?', [$status, $id]);
    }

    /**
     * Operator-routing helpers. The AI suggests a route at create-time;
     * the admin reviews + accepts to commit it as `assigned_to`. PII
     * intent — the call is never cached, and the AI failure is silent
     * (the studio still reads every inquiry by hand).
     */
    public const ROUTES = ['brand-dev', 'creative', 'media', 'project-event', 'digital', 'training', 'general'];

    public static function setSuggestedRoute(int $id, string $route, string $why, float $conf): void {
        if (!Database::available()) return;
        if (!in_array($route, self::ROUTES, true)) return;
        $why = mb_substr($why, 0, 280);
        $conf = max(0.0, min(1.0, $conf));
        Database::exec(
            'UPDATE inquiries SET suggested_route = ?, route_why = ?, route_conf = ? WHERE id = ?',
            [$route, $why, $conf, $id]
        );
    }

    public static function setAssignedTo(int $id, string $route): void {
        if (!Database::available()) return;
        if (!in_array($route, self::ROUTES, true)) return;
        Database::exec('UPDATE inquiries SET assigned_to = ? WHERE id = ?', [$route, $id]);
    }

    /**
     * Lazily compute + persist a routing suggestion for an inquiry. Safe
     * to call from a shutdown hook — silently returns on any failure
     * so the visitor never sees the error.
     */
    public static function routeWithAi(int $id): void {
        if (!class_exists('Ai') || !\Ai::enabled()) return;
        $row = self::find($id);
        if (!$row || !empty($row['suggested_route'])) return;

        $ctx = json_encode([
            'kind'         => (string)($row['kind'] ?? ''),
            'inquiry_type' => (string)($row['inquiry_type'] ?? ''),
            'service'      => (string)($row['service'] ?? ''),
            'brand_stage'  => (string)($row['brand_stage'] ?? ''),
            'event_size'   => (string)($row['event_size'] ?? ''),
            'message'      => mb_substr((string)($row['message'] ?? ''), 0, 1200),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $res = \Ai::run('inquiry_route', $ctx);
        if (empty($res['ok']) || empty($res['text'])) return;

        $text = trim((string)$res['text']);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```[a-z]*\s*|\s*```$/i', '', $text);
        }
        $obj = json_decode($text, true);
        if (!is_array($obj) || empty($obj['route'])) return;

        $route = strtolower(trim((string)$obj['route']));
        $why   = (string)($obj['why']   ?? '');
        $conf  = (float) ($obj['confidence'] ?? 0.5);
        self::setSuggestedRoute($id, $route, $why, $conf);
    }

    public static function counts(): array {
        if (!Database::available()) return ['new' => 0, 'open' => 0, 'resolved' => 0, 'total' => 0];
        $rows = Database::all("SELECT status, COUNT(*) c FROM inquiries GROUP BY status");
        $out = ['new' => 0, 'open' => 0, 'resolved' => 0, 'total' => 0];
        foreach ($rows as $r) { $out[$r['status']] = (int)$r['c']; $out['total'] += (int)$r['c']; }
        return $out;
    }
}
