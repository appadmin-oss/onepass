<?php

/**
 * AiLog — newline-delimited JSON observability for every AI call.
 *
 * Lives at storage/ailog.ndjson. One JSON line per call. Operators can
 * `tail -f` or `grep`. The file is FIFO-trimmed at MAX_LINES on every
 * 100th write so it can never grow without bound on shared hosting.
 *
 * Optional DB mirror: when AI_DB_LOG=1 and the ai_log table exists,
 * the same line is also inserted there for SQL analysis. The NDJSON
 * file is the source of truth — if the DB write fails it's swallowed.
 */
class AiLog {
    private const FILE      = '/storage/ailog.ndjson';
    private const MAX_LINES = 50000;

    /**
     * Record a single call. All fields optional except intent + ok.
     * Cheap: one append + maybe one DB write; no synchronous network.
     */
    public static function record(array $row): void {
        $rec = [
            'ts'        => gmdate('c'),
            'intent'    => (string)($row['intent']    ?? '?'),
            'provider'  => (string)($row['provider']  ?? ''),
            'ms'        => (int)   ($row['ms']        ?? 0),
            'ok'        => (bool)  ($row['ok']        ?? false),
            'cached'    => (bool)  ($row['cached']    ?? false),
            'bytes_in'  => (int)   ($row['bytes_in']  ?? 0),
            'bytes_out' => (int)   ($row['bytes_out'] ?? 0),
            'budget_remaining' => (int)($row['budget_remaining'] ?? 0),
            'error'     => $row['error'] ?? null,
        ];

        $dir = AFS_ROOT . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents(
            $dir . '/ailog.ndjson',
            json_encode($rec, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND | LOCK_EX
        );
        if (random_int(1, 100) === 1) self::trim();

        if (getenv('AI_DB_LOG') === '1') self::dbWrite($rec);
    }

    /** Read the last N lines for the admin AI observability page. */
    public static function tail(int $n = 200): array {
        $path = AFS_ROOT . self::FILE;
        if (!is_file($path)) return [];
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $slice = array_slice($lines, -max(1, min(2000, $n)));
        $out = [];
        foreach ($slice as $line) {
            $row = json_decode($line, true);
            if (is_array($row)) $out[] = $row;
        }
        return $out;
    }

    /** Aggregate by intent for a top-N report. */
    public static function summary(int $window = 1000): array {
        $rows = self::tail($window);
        $byIntent = [];
        foreach ($rows as $r) {
            $i = (string)$r['intent'];
            $byIntent[$i] ??= ['count'=>0,'ok'=>0,'cached'=>0,'ms_total'=>0];
            $byIntent[$i]['count']++;
            if (!empty($r['ok'])) $byIntent[$i]['ok']++;
            if (!empty($r['cached'])) $byIntent[$i]['cached']++;
            $byIntent[$i]['ms_total'] += (int)($r['ms'] ?? 0);
        }
        foreach ($byIntent as &$v) {
            $v['ms_avg'] = $v['count'] ? (int) round($v['ms_total'] / $v['count']) : 0;
            unset($v['ms_total']);
        }
        return $byIntent;
    }

    private static function trim(): void {
        $path = AFS_ROOT . self::FILE;
        if (!is_file($path)) return;
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        if (count($lines) <= self::MAX_LINES) return;
        $keep = array_slice($lines, -self::MAX_LINES);
        @file_put_contents($path, implode("\n", $keep) . "\n", LOCK_EX);
    }

    private static function dbWrite(array $rec): void {
        if (!class_exists('Database') || !Database::available()) return;
        try {
            Database::exec(
                'INSERT INTO ai_log (intent, provider, ms, ok, cached, bytes_in, bytes_out, error_code)
                 VALUES (?,?,?,?,?,?,?,?)',
                [
                    $rec['intent'], $rec['provider'], $rec['ms'],
                    $rec['ok'] ? 1 : 0, $rec['cached'] ? 1 : 0,
                    $rec['bytes_in'], $rec['bytes_out'],
                    $rec['error'] ? substr((string)$rec['error'], 0, 40) : null,
                ]
            );
        } catch (Throwable $e) {
            // NDJSON is the source of truth. Swallow.
        }
    }
}
