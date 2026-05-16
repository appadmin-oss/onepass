<?php

class Auth {
    public static function user(): ?array {
        return $_SESSION['admin'] ?? null;
    }

    public static function check(): bool {
        return !empty($_SESSION['admin']);
    }

    public static function attempt(string $username, string $password): bool {
        if (!Database::available()) return false;
        $row = Database::one('SELECT * FROM admins WHERE username = ? LIMIT 1', [$username]);
        if (!$row) return false;
        if (!password_verify($password, $row['password_hash'])) return false;
        $_SESSION['admin'] = ['id' => (int)$row['id'], 'username' => $row['username']];
        session_regenerate_id(true);
        return true;
    }

    public static function logout(): void {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }

    public static function require(): void {
        if (!self::check()) {
            header('Location: ' . url('/admin/login'));
            exit;
        }
    }
}
