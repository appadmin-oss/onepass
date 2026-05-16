<?php

/**
 * AiBudget — daily call counter so a runaway loop or a sudden spike
 * can't drain the free upstream quota or surprise the operator.
 *
 * The default ceiling is generous (500 calls / UTC day) and is set via
 * the env constant `AFS_AI_DAILY_BUDGET`. Operators can lower it for
 * tighter cost discipline or raise it temporarily during cohort weeks.
 *
 * When the budget trips:
 *   - every AI surface receives `{ok: false, error: 'budget'}`
 *   - the visible UI swaps to the registry's `fallback` string
 *   - nothing 500s
 *
 * Storage is a tiny JSON file (`storage/aibudget.json`) keyed by UTC
 * date. We never persist per-user budgets — the ceiling is a system
 * guarantee, not a per-account limit.
 */
class AiBudget {
    private const FILE = '/storage/aibudget.json';

    public static function cap(): int {
        $env = (int) (getenv('AFS_AI_DAILY_BUDGET') ?: '500');
        return $env > 0 ? $env : 500;
    }

    public static function spent(): int {
        $state = self::read();
        return (int)($state['calls'] ?? 0);
    }

    public static function remaining(): int {
        return max(0, self::cap() - self::spent());
    }

    public static function allow(): bool {
        return self::remaining() > 0;
    }

    /** Increment the counter. Caller should check allow() first. */
    public static function record(): void {
        $today = gmdate('Y-m-d');
        $state = self::read();
        if (($state['date'] ?? '') !== $today) {
            $state = ['date' => $today, 'calls' => 0, 'capped_at' => null];
        }
        $state['calls']++;
        if ($state['calls'] >= self::cap() && !$state['capped_at']) {
            $state['capped_at'] = gmdate('c');
        }
        self::write($state);
    }

    /** Snapshot for the admin observability surface. */
    public static function status(): array {
        $cap = self::cap();
        $spent = self::spent();
        return [
            'date'       => gmdate('Y-m-d'),
            'cap'        => $cap,
            'spent'      => $spent,
            'remaining'  => max(0, $cap - $spent),
            'percent'    => $cap > 0 ? round(($spent / $cap) * 100, 1) : 0,
        ];
    }

    private static function read(): array {
        $path = AFS_ROOT . self::FILE;
        if (!is_file($path)) return ['date' => gmdate('Y-m-d'), 'calls' => 0];
        $raw = @file_get_contents($path);
        $data = $raw !== false ? json_decode($raw, true) : null;
        if (!is_array($data)) return ['date' => gmdate('Y-m-d'), 'calls' => 0];
        // Roll over on a new UTC day.
        if (($data['date'] ?? '') !== gmdate('Y-m-d')) {
            return ['date' => gmdate('Y-m-d'), 'calls' => 0];
        }
        return $data;
    }

    private static function write(array $state): void {
        $dir = AFS_ROOT . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents(
            $dir . '/aibudget.json',
            json_encode($state, JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }
}
