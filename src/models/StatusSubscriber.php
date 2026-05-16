<?php

/**
 * StatusSubscriber — emails that asked to hear when something breaks.
 *
 * We deliberately keep this list dead simple: just an email and an
 * inserted_at. There's no double-opt-in here because we don't email
 * marketing from it; the only outbound a subscriber receives is an
 * operator-triggered incident notification through Mailer::sendTo().
 * Operators pull the list on incident-open.
 */
class StatusSubscriber {
    public static function add(string $email): array {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'invalid_email'];
        }
        if (!Database::available()) {
            // Soft-fail to a flat-file so we don't lose the address.
            $dir = AFS_ROOT . '/storage';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            @file_put_contents($dir . '/status-subscribers.log',
                $email . "\t" . date('c') . "\n", FILE_APPEND | LOCK_EX);
            return ['ok' => true, 'mode' => 'log'];
        }
        try {
            Database::exec(
                'INSERT INTO status_subscribers (email) VALUES (?)
                 ON DUPLICATE KEY UPDATE email = email',
                [$email]
            );
            return ['ok' => true, 'mode' => 'db'];
        } catch (Throwable $e) {
            error_log('[status] subscribe failed: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'db_failed'];
        }
    }

    public static function all(): array {
        if (!Database::available()) return [];
        return Database::all('SELECT email FROM status_subscribers ORDER BY created_at DESC');
    }
}
