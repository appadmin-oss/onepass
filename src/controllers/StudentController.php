<?php

/**
 * Public-facing student auth + dashboard.
 *
 * /login        — sign-in form
 * /dashboard    — track progress, cohort info, links to Moodle + Jitsi
 * /logout       — clears the session
 *
 * NB: admin/operator login at /admin/login is intentionally NOT
 * referenced anywhere in the public site. It still exists for the team.
 */
class StudentController extends Controller {
    public function showLogin(): void {
        if (StudentAuth::check()) { $this->redirect('/dashboard'); return; }
        $this->view('pages/student/login', [
            'title'       => 'Sign in to your Afrotech Academy dashboard',
            'description' => 'Sign in to track your cohort progress, join live sessions, and continue your learning.',
            'breadcrumbs' => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'Sign in'],
            ],
        ], 'main');
    }

    public function login(): void {
        Csrf::require();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        // Honeypot: bots tend to fill every input. Silent reject.
        if (!Security::honeypotOk('website')) {
            if ($isAjax) $this->json(['ok' => true, 'redirect' => '/dashboard']);
            else $this->redirect('/login/password');
            return;
        }

        $email    = strtolower((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');

        // IP-based throttle: 10 attempts / 5 min across all accounts from this IP.
        $ipKey = 'login_ip_' . Security::clientIp();
        if (!Security::rateLimit($ipKey, 10, 300)) {
            $msg = 'Too many sign-in attempts from this network. Wait a few minutes and try again.';
            if ($isAjax) { $this->json(['error' => 'rate_limited', 'message' => $msg], 429); return; }
            flash_set('student_login_error', $msg);
            $this->redirect('/login/password'); return;
        }

        // Account-specific lockout (incremental backoff).
        $lockKey = 'login_acct_' . sha1($email);
        $lock = Security::lockoutCheck($lockKey);
        if ($lock['locked']) {
            $mins = max(1, (int) ceil($lock['retry_after'] / 60));
            $msg  = "Account temporarily locked after repeated failures. Try again in {$mins} min.";
            if ($isAjax) { $this->json(['error' => 'locked', 'message' => $msg, 'retry_after' => $lock['retry_after']], 423); return; }
            flash_set('student_login_error', $msg);
            $this->redirect('/login/password'); return;
        }

        $v = (new Validator(['email' => $email, 'password' => $password]))
            ->check('email',    ['required', 'email'])
            ->check('password', ['required', 'min:8']);

        if (!$v->passes()) {
            if ($isAjax) $this->json(['error' => 'validation', 'fields' => $v->errors()], 422);
            else { flash_set('student_login_error', 'Check your email and password.'); $this->redirect('/login/password'); }
            return;
        }

        $result = StudentAuth::attempt($email, $password);
        if ($result === false) {
            Security::lockoutHit($lockKey);
            if ($isAjax) $this->json(['error' => 'auth', 'message' => 'Email or password not recognised.'], 401);
            else { flash_set('student_login_error', 'Email or password not recognised. New here? Apply free below.'); $this->redirect('/login/password'); }
            return;
        }

        Security::lockoutReset($lockKey);

        if ($result === '2fa_required') {
            if ($isAjax) $this->json(['ok' => true, 'redirect' => '/login/2fa']);
            else $this->redirect('/login/2fa');
            return;
        }

        if ($isAjax) $this->json(['ok' => true, 'redirect' => '/dashboard']);
        else $this->redirect('/dashboard');
    }

    /**
     * POST /auth/google — exchange a Firebase ID token for a session.
     * The client obtains the token via signInWithPopup(GoogleAuthProvider);
     * we verify the JWT server-side, then find-or-create the student row.
     */
    public function googleSignIn(): void {
        Csrf::require();
        $idToken = trim((string)$this->input('idToken', ''));
        if ($idToken === '') {
            $this->json(['error' => 'missing_token'], 422); return;
        }
        if (!Firebase::enabled()) {
            $this->json(['error' => 'firebase_disabled',
                         'message' => 'Google sign-in isn\'t configured for this environment.'], 503);
            return;
        }
        $r = Firebase::verifyIdToken($idToken);
        if (!$r['ok']) {
            error_log('[auth:google] verify failed: ' . $r['error']);
            $this->json(['error' => 'invalid_token', 'detail' => $r['error']], 401);
            return;
        }
        $row = Student::findOrCreateFromGoogle($r['identity']);
        if (!$row) {
            $this->json(['error' => 'persist_failed'], 500); return;
        }
        $_SESSION['student'] = [
            'id'          => (int)$row['id'],
            'name'        => $row['name']  ?? ($r['identity']['name']  ?? 'Student'),
            'email'       => $row['email'] ?? $r['identity']['email'],
            'status'      => $row['status'] ?? 'onboarding',
            'track_slug'  => $row['track_slug'] ?? null,
            'avatar_url'  => $row['avatar_url'] ?? ($r['identity']['picture'] ?? null),
            'auth_method' => 'google',
        ];
        session_regenerate_id(true);
        $this->json(['ok' => true, 'redirect' => '/dashboard']);
    }

    /**
     * GET /dashboard/profile — onsite profile page. Reads from the students
     * row + an extras blob (skills, goals, bio) stored as JSON in
     * students.profile_json so we don't need a wide ALTER.
     */
    public function profile(): void {
        StudentAuth::require();
        $user = StudentAuth::user();
        $row  = Database::available()
            ? Database::one('SELECT * FROM students WHERE id = ?', [$user['id']])
            : null;
        if (!$row) { StudentAuth::logout(); $this->redirect('/login'); return; }

        $profile = [];
        if (!empty($row['profile_json'])) {
            $decoded = json_decode((string)$row['profile_json'], true);
            if (is_array($decoded)) $profile = $decoded;
        }
        $this->view('pages/student/profile', [
            'title'       => $row['name'] . ' · Profile · Afrotech Academy',
            'description' => 'Your Afrotech profile, skills, goals and study streak.',
            'student'     => $row,
            'profile'     => $profile,
            'breadcrumbs' => [
                ['label' => 'Home',      'href' => url('/')],
                ['label' => 'Dashboard', 'href' => url('/dashboard')],
                ['label' => 'Profile'],
            ],
        ], 'main');
    }

    /** POST /dashboard/profile — saves the editable fields. */
    public function profileSave(): void {
        Csrf::require();
        StudentAuth::require();
        $user = StudentAuth::user();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        if (!Database::available()) {
            if ($isAjax) { $this->json(['error' => 'db_unavailable'], 503); return; }
            flash_set('profile_error', 'Profile save unavailable right now.');
            $this->redirect('/dashboard/profile'); return;
        }

        $name = trim((string)$this->input('name', $user['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 160) $name = $user['name'] ?? 'Student';

        // Free-form fields → constrained, sanitised JSON. Never trust raw HTML.
        $profile = [
            'bio'        => mb_substr(trim((string)$this->input('bio', '')), 0, 800),
            'goal'       => mb_substr(trim((string)$this->input('goal', '')), 0, 400),
            'tagline'    => mb_substr(trim((string)$this->input('tagline', '')), 0, 120),
            'skills'     => array_values(array_filter(array_map(
                'trim',
                explode(',', (string)$this->input('skills', ''))
            ))),
            'site_url'   => filter_var(trim((string)$this->input('site_url', '')), FILTER_VALIDATE_URL) ?: '',
            'github_url' => filter_var(trim((string)$this->input('github_url', '')), FILTER_VALIDATE_URL) ?: '',
            'linkedin_url' => filter_var(trim((string)$this->input('linkedin_url', '')), FILTER_VALIDATE_URL) ?: '',
            'avatar_emoji' => mb_substr(trim((string)$this->input('avatar_emoji', '')), 0, 8),
            'public_handle' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$this->input('public_handle', '')) ?: null,
        ];
        // Cap the skills list defensively.
        $profile['skills'] = array_slice(array_map(
            fn($s) => mb_substr((string)$s, 0, 40),
            $profile['skills']
        ), 0, 24);

        // Soft moderation on the free-form text fields. The mentor flags
        // spam / harassment / hate / sexual / threats. We block the save
        // and return the rejection reason so the visitor can fix it
        // (rather than silently shadow-banning). Default to allow if AI
        // is off or the call fails — operators still review profiles
        // manually before public surfacing.
        $freeText = trim($profile['bio'] . "\n" . $profile['goal'] . "\n" . $profile['tagline']);
        if ($freeText !== '' && class_exists('Ai') && Ai::enabled()) {
            $res = Ai::run('content_check', $freeText);
            if (!empty($res['ok']) && !empty($res['text'])) {
                $raw = trim((string)$res['text']);
                if (str_starts_with($raw, '```')) $raw = preg_replace('/^```[a-z]*\s*|\s*```$/i', '', $raw);
                $obj = json_decode($raw, true);
                if (is_array($obj) && isset($obj['ok']) && $obj['ok'] === false) {
                    $reason = trim((string)($obj['reason'] ?? 'flagged'));
                    if ($isAjax) { $this->json(['error' => 'moderation', 'reason' => $reason], 422); return; }
                    flash_set('profile_error', 'We can\'t save that yet — flagged as: ' . $reason . '. Edit the bio / goal / tagline and try again.');
                    $this->redirect('/dashboard/profile'); return;
                }
            }
        }

        Database::exec(
            'UPDATE students SET name = ?, profile_json = ? WHERE id = ?',
            [$name, json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), (int)$user['id']]
        );
        $_SESSION['student']['name'] = $name;

        if ($isAjax) { $this->json(['ok' => true]); return; }
        flash_set('profile_ok', 'Profile saved.');
        $this->redirect('/dashboard/profile');
    }

    public function logout(): void {
        if (($_POST['_csrf'] ?? null) || ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) Csrf::require();
        StudentAuth::logout();
        $this->redirect('/');
    }

    public function dashboard(): void {
        StudentAuth::require();
        $user = StudentAuth::user();
        // Load latest student row (so admin-side status updates surface)
        $row = Database::available() ? Database::one('SELECT * FROM students WHERE id = ?', [$user['id']]) : $user;
        if (!$row) { StudentAuth::logout(); $this->redirect('/login'); return; }

        $track = $row['track_slug'] ?? null;
        $course = $track ? Course::find($track) : null;
        $sessions = LiveSession::upcoming();

        // Resolve a personal LMS deep-link + live-room URL when wired.
        $lmsUrl   = (defined('LMS_ENABLED') && LMS_ENABLED && !empty($course['lms_id']))
                  ? Lms::courseUrl((int)$course['lms_id'])
                  : (defined('LMS_BASE_URL') ? LMS_BASE_URL : null);
        $jitsiUrl = (defined('JAAS_ENABLED') && JAAS_ENABLED)
                  ? url('/academy/live-sessions')
                  : url('/academy/live-sessions');

        $this->view('pages/student/dashboard', [
            'title'       => 'Your dashboard · Afrotech Academy',
            'description' => 'Continue your Afrotech Academy track. Live sessions, course material, and progress in one place.',
            'student'     => $row,
            'course'      => $course,
            'sessions'    => $sessions,
            'lmsUrl'      => $lmsUrl,
            'jitsiUrl'    => $jitsiUrl,
            'lmsEnabled'  => defined('LMS_ENABLED') && LMS_ENABLED,
            'jaasEnabled' => defined('JAAS_ENABLED') && JAAS_ENABLED,
            'breadcrumbs' => [
                ['label' => 'Home',      'href' => url('/')],
                ['label' => 'Dashboard'],
            ],
            'bodyClass'   => 'is-dashboard',
        ], 'main');
    }
}
