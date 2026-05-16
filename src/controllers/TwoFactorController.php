<?php

/**
 * TwoFactorController — TOTP (RFC 6238) enrolment + login challenge for students.
 *
 * Flow:
 *   1. Signed-in student visits /dashboard/2fa → we generate a secret if
 *      none exists yet, render the otpauth:// QR + manual key. The secret
 *      is stored unencrypted but only ever the user's own row sees it.
 *   2. User scans with Google Authenticator / 1Password / Authy, enters a
 *      6-digit code on POST /dashboard/2fa/enable. We verify with ±1 step
 *      drift. On success we set totp_enabled_at.
 *   3. On login, if the student has totp_enabled_at, we stash the row in
 *      $_SESSION['pending_2fa'] and redirect to /login/2fa. They submit a
 *      code; we verify; only then do we complete the session.
 *   4. /dashboard/2fa/disable requires the user's password.
 */
class TwoFactorController extends Controller {

    /** GET /dashboard/2fa */
    public function setup(): void {
        StudentAuth::require();
        $user = StudentAuth::user();
        $row  = Database::available() ? Database::one('SELECT * FROM students WHERE id = ?', [$user['id']]) : null;
        if (!$row) { StudentAuth::logout(); $this->redirect('/login'); return; }

        $isEnabled = !empty($row['totp_enabled_at']);
        $secret = $row['totp_secret'] ?: null;
        // Generate a pending secret only if 2FA is not yet enabled.
        if (!$isEnabled && !$secret) {
            $secret = Security::totpSecret();
            if (Database::available()) {
                Database::exec('UPDATE students SET totp_secret = ? WHERE id = ?', [$secret, $row['id']]);
            }
        }
        $otpauth = $secret ? Security::totpOtpauthUrl($secret, $row['email'], 'Afrostrength') : '';
        $qrUrl = $otpauth ? 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=0&data=' . urlencode($otpauth) : '';

        $this->view('pages/student/2fa', [
            'title'       => $isEnabled ? 'Two-factor authentication · enabled' : 'Set up two-factor authentication',
            'student'     => $row,
            'isEnabled'   => $isEnabled,
            'secret'      => $secret,
            'qrUrl'       => $qrUrl,
            'breadcrumbs' => [
                ['label' => 'Home',      'href' => url('/')],
                ['label' => 'Dashboard', 'href' => url('/dashboard')],
                ['label' => '2FA'],
            ],
        ], 'main');
    }

    /** POST /dashboard/2fa/enable */
    public function enable(): void {
        Csrf::require();
        StudentAuth::require();
        $user = StudentAuth::user();
        if (!Database::available()) {
            flash_set('twofa_error', 'Two-factor needs the database; ask the team to enable it.');
            $this->redirect('/dashboard/2fa'); return;
        }
        $row = Database::one('SELECT * FROM students WHERE id = ?', [$user['id']]);
        if (!$row || empty($row['totp_secret'])) {
            $this->redirect('/dashboard/2fa'); return;
        }
        $code = (string) $this->input('code', '');
        if (!Security::totpVerify($row['totp_secret'], $code)) {
            flash_set('twofa_error', 'That code didn\'t match. Double-check the app and try again.');
            $this->redirect('/dashboard/2fa'); return;
        }
        Database::exec('UPDATE students SET totp_enabled_at = NOW() WHERE id = ?', [$row['id']]);
        flash_set('twofa_ok', 'Two-factor authentication is on. We\'ll ask for a code next time you sign in.');
        $this->redirect('/dashboard/2fa');
    }

    /** POST /dashboard/2fa/disable */
    public function disable(): void {
        Csrf::require();
        StudentAuth::require();
        $user = StudentAuth::user();
        if (!Database::available()) { $this->redirect('/dashboard/2fa'); return; }
        $row = Database::one('SELECT * FROM students WHERE id = ?', [$user['id']]);
        if (!$row) { $this->redirect('/login'); return; }

        $password = (string) $this->input('password', '');
        if (empty($row['password_hash']) || !password_verify($password, $row['password_hash'])) {
            flash_set('twofa_error', 'Password didn\'t match. 2FA is still on.');
            $this->redirect('/dashboard/2fa'); return;
        }
        Database::exec('UPDATE students SET totp_secret = NULL, totp_enabled_at = NULL WHERE id = ?', [$row['id']]);
        flash_set('twofa_ok', 'Two-factor authentication has been turned off.');
        $this->redirect('/dashboard/2fa');
    }

    /** GET /login/2fa — challenge screen shown after a correct password. */
    public function challengeForm(): void {
        if (empty($_SESSION['pending_2fa'])) { $this->redirect('/login'); return; }
        $this->view('pages/student/2fa-challenge', [
            'title'       => 'Enter your two-factor code',
            'breadcrumbs' => [
                ['label' => 'Home',     'href' => url('/')],
                ['label' => 'Sign in',  'href' => url('/login')],
                ['label' => 'Two-factor'],
            ],
        ], 'main');
    }

    /** POST /login/2fa — verify the code and complete the session. */
    public function challengeSubmit(): void {
        Csrf::require();
        if (empty($_SESSION['pending_2fa'])) { $this->redirect('/login'); return; }
        $pending = $_SESSION['pending_2fa'];
        if (!Security::rateLimit('2fa_' . sha1((string)$pending['id']), 6, 600)) {
            flash_set('twofa_error', 'Too many attempts. Wait a few minutes and try again.');
            $this->redirect('/login/2fa'); return;
        }
        $code = (string) $this->input('code', '');
        if (!Security::totpVerify((string)($pending['totp_secret'] ?? ''), $code)) {
            flash_set('twofa_error', 'That code didn\'t match. Try again.');
            $this->redirect('/login/2fa'); return;
        }
        // Promote the pending row into a real session.
        $_SESSION['student'] = [
            'id'         => (int)$pending['id'],
            'name'       => $pending['name']  ?? '',
            'email'      => $pending['email'] ?? '',
            'status'     => $pending['status'] ?? 'onboarding',
            'track_slug' => $pending['track_slug'] ?? null,
        ];
        unset($_SESSION['pending_2fa']);
        session_regenerate_id(true);
        $this->redirect('/dashboard');
    }
}
