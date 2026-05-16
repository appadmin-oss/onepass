<?php

class Admin {
    public static function find(string $username): ?array {
        if (!Database::available()) return null;
        return Database::one('SELECT * FROM admins WHERE username = ?', [$username]);
    }
}
