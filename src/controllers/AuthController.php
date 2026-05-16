<?php

/**
 * AuthController — passwordless-first sign-in for students.
 *
 * The default flow is magic-link + OTP:
 *
 *   1. GET  /login            → two-column form (auth.php layout, no site nav).
 *   2. POST /login            → MagicLink::issue() + email send. Session
 *                                stashes the pending email and the public
 *                                "selector" so /login/verify-otp can find
 *                                the row without the user typing it.
 *   3. GET  /login/check      → "We sent you a link" screen with a 6-digit
 *                                OTP input as the parallel path.
 *   4. POST /login/verify-otp → consumeOtp() → grant session (or divert to
 *                                /login/2fa when TOTP is enabled).
 *   5. GET  /auth/verify?t=…  → consumeLink() → grant session.
 *
 * Password sign-in remains available at /login/password (clearly secondary)
 * for accounts created before magic-link rollout. Google sign-in stays on
 * the main /login page.
 */
class AuthController extends Controller {

    /** GET /login — render the two-column auth screen. */
    public function showLogin(): void {
        if (StudentAuth::check()) { $this->redirect('/dashboard'); return; }
        $pending = $_SESSION['pending_magic'] ?? null;
        $this->view('pages/auth/login', [
            'title'       => 'Sign in to Afrostrength',
            'description' => 'Sign in with a single-use link or a 6-digit code.',
            'pending'     => $pending,
            'noindex'     => true,
        ], 'auth');
    }

    /** POST /login — request a magic link + OTP. */
    public function requestLink(): void {
        Csrf::require();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        if (!Security::honeypotOk('website')) {
            // Bots get a fake success and a session-only cookie so we don't
            // burn quota on them. Real users never see this branch.
            if ($isAjax) { $this->json(['ok' => true]); return; }
            $this->redirect('/login/check'); return;
        }

        $email = strtolower((string) $this->input('email', ''));
        $v = (new Validator(['email' => $email]))->check('email', ['required', 'email']);
        if (!$v->passes()) {
            if ($isAjax) { $this->json(['error' => 'validation', 'fields' => $v->errors()], 422); return; }
            flash_set('login_error', 'Please enter a valid email address.');
            $this->redirect('/login'); return;
        }

        // Always tell the user "if your account exists, we'll email you" —
        // never disclose whether an email is registered or not. We still
        // generate a token only for existing students, to avoid emailing
        // people who didn't ask to be enrolled.
        $token = null;
        $student = Student::findByEmail($email);
        if ($student) {
            $token = MagicLink::issue($email, 'login');
            if (!empty($token['ok'])) {
                $html = render_email('magic-link', [
                    'link'    => $token['link'],
                    'otp'     => $token['otp'],
                    'minutes' => 15,
                    'ip'      => Security::clientIp(),
                    'ua'      => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
                ]);
                Mailer::sendTo($email, 'Your Afrostrength sign-in link', $html);
            }
        }

        // Stash only the selector (public lookup id). The verifier never
        // touches the session — it lives only inside the email link.
        $_SESSION['pending_magic'] = [
            'email'      => $email,
            'selector'   => $token['selector'] ?? null,
            'requested'  => time(),
        ];

        if ($isAjax) { $this->json(['ok' => true, 'redirect' => '/login/check']); return; }
        $this->redirect('/login/check');
    }

    /** GET /login/check — “We sent you an email” + OTP paste. */
    public function check(): void {
        if (StudentAuth::check()) { $this->redirect('/dashboard'); return; }
        $pending = $_SESSION['pending_magic'] ?? null;
        if (!$pending) { $this->redirect('/login'); return; }
        $this->view('pages/auth/check', [
            'title'       => 'Check your email · Afrostrength',
            'description' => 'We sent a sign-in link and a 6-digit code to your inbox.',
            'pending'     => $pending,
            'noindex'     => true,
        ], 'auth');
    }

    /** POST /login/verify-otp — paste-code path. */
    public function verifyOtp(): void {
        Csrf::require();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        $pending = $_SESSION['pending_magic'] ?? null;
        if (!$pending || empty($pending['selector'])) {
            if ($isAjax) { $this->json(['error' => 'expired'], 400); return; }
            $this->redirect('/login'); return;
        }
        $code = (string) $this->input('code', '');
        $email = MagicLink::consumeOtp($pending['selector'], $code);
        if (!$email || $email !== strtolower((string)$pending['email'])) {
            if ($isAjax) { $this->json(['error' => 'bad_code', 'message' => 'That code didn\'t match, or it expired. Request a new one.'], 401); return; }
            flash_set('login_error', 'That code didn\'t match, or it expired.');
            $this->redirect('/login/check'); return;
        }
        $this->signInOrChallenge($email, $isAjax);
    }

    /** GET /auth/verify?t=…  — click-the-link path. */
    public function verifyLink(): void {
        $token = (string) ($_GET['t'] ?? '');
        if ($token === '') { $this->redirect('/login'); return; }
        $email = MagicLink::consumeLink($token);
        if (!$email) {
            flash_set('login_error', 'This sign-in link is no longer valid. Request a new one.');
            $this->redirect('/login'); return;
        }
        $this->signInOrChallenge($email, false);
    }

    /** GET /login/password — password fallback. */
    public function showPassword(): void {
        if (StudentAuth::check()) { $this->redirect('/dashboard'); return; }
        $this->view('pages/auth/password', [
            'title'       => 'Sign in with a password · Afrostrength',
            'description' => 'Sign in with your email and password.',
            'noindex'     => true,
        ], 'auth');
    }

    // ----- private --------------------------------------------------

    /**
     * Establish a real session for the verified email. If the student has
     * TOTP turned on, stash the pending row and divert to /login/2fa.
     */
    private function signInOrChallenge(string $email, bool $isAjax): void {
        $row = Student::findByEmail($email);
        if (!$row) {
            flash_set('login_error', 'No active account for that email.');
            $this->redirect('/login'); return;
        }
        unset($_SESSION['pending_magic']);

        if (!empty($row['totp_enabled_at'])) {
            $_SESSION['pending_2fa'] = [
                'id'           => (int)$row['id'],
                'name'         => $row['name'],
                'email'        => $row['email'],
                'status'       => $row['status']     ?? 'onboarding',
                'track_slug'   => $row['track_slug'] ?? null,
                'totp_secret'  => $row['totp_secret'] ?? '',
            ];
            if ($isAjax) { $this->json(['ok' => true, 'redirect' => '/login/2fa']); return; }
            $this->redirect('/login/2fa'); return;
        }
        $_SESSION['student'] = [
            'id'          => (int)$row['id'],
            'name'        => $row['name'],
            'email'       => $row['email'],
            'status'      => $row['status']     ?? 'onboarding',
            'track_slug'  => $row['track_slug'] ?? null,
            'auth_method' => 'magic',
        ];
        session_regenerate_id(true);
        if ($isAjax) { $this->json(['ok' => true, 'redirect' => '/dashboard']); return; }
        $this->redirect('/dashboard');
    }
}
