<?php

/**
 * StudentAuth — separate from admin Auth (which is operator-only).
 * Used by the public-facing /login + /dashboard surfaces.
 */
class StudentAuth {
    public static function user(): ?array {
        return $_SESSION['student'] ?? null;
    }
    public static function check(): bool {
        return !empty($_SESSION['student']);
    }
    /**
     * Verify credentials. Returns:
     *   'ok'           — password matched, session is live, user can proceed.
     *   '2fa_required' — password matched, but the account has TOTP enabled;
     *                    StudentController stashes the row in pending_2fa and
     *                    redirects to the challenge screen.
     *   false          — credentials are wrong.
     */
    public static function attempt(string $email, string $password) {
        $row = Student::findByEmail($email);
        if (!$row) return false;
        if (empty($row['password_hash'])) return false; // Google-only account
        if (!password_verify($password, $row['password_hash'])) return false;

        // If 2FA is enabled, do not establish the session yet.
        if (!empty($row['totp_enabled_at'])) {
            $_SESSION['pending_2fa'] = [
                'id'           => (int)$row['id'],
                'name'         => $row['name'],
                'email'        => $row['email'],
                'status'       => $row['status']     ?? 'onboarding',
                'track_slug'   => $row['track_slug'] ?? null,
                'totp_secret'  => $row['totp_secret'] ?? '',
            ];
            return '2fa_required';
        }

        $_SESSION['student'] = [
            'id'          => (int)$row['id'],
            'name'        => $row['name'],
            'email'       => $row['email'],
            'status'      => $row['status'] ?? 'onboarding',
            'track_slug'  => $row['track_slug'] ?? null,
        ];
        session_regenerate_id(true);
        return 'ok';
    }
    public static function logout(): void {
        unset($_SESSION['student']);
        session_regenerate_id(true);
    }
    public static function require(): void {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }
}
