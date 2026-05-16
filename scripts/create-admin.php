<?php
/**
 * scripts/create-admin.php — bcrypt + insert/update an admin row.
 *
 * Usage:
 *   php scripts/create-admin.php <username> <password>
 *
 * Re-running with the same username updates the password.
 */

if (PHP_SAPI !== 'cli') { exit("CLI only.\n"); }

if ($argc < 3) {
    fwrite(STDERR, "Usage: php scripts/create-admin.php <username> <password>\n");
    exit(1);
}

[$_, $username, $password] = $argv;

if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}

define('AFS_ROOT', dirname(__DIR__));
require AFS_ROOT . '/config/app.php';
require AFS_ROOT . '/src/core/Helpers.php';
require AFS_ROOT . '/src/core/Database.php';

try {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $existing = Database::one('SELECT id FROM admins WHERE username = ?', [$username]);
    if ($existing) {
        Database::exec('UPDATE admins SET password_hash = ? WHERE id = ?', [$hash, $existing['id']]);
        echo "Admin '{$username}' updated.\n";
    } else {
        Database::insert('INSERT INTO admins (username, password_hash) VALUES (?, ?)', [$username, $hash]);
        echo "Admin '{$username}' created.\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(2);
}
