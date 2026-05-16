<?php

/**
 * Afrotech Academy onboarding — five-step registration flow.
 *
 * Steps:
 *   1. Track  — pick a certification track
 *   2. Profile — name, email, phone, country, current role
 *   3. Goals — experience level + free-text goal
 *   4. Cohort + referral — preferred cohort, marketing opt-in, referral
 *   5. Account — password (with strength + confirm)
 *
 * Server stores everything as one record on submit. The form is friendly
 * to no-JS clients via a single multi-fieldset POST, but the JS layer
 * makes it feel like a guided wizard.
 */
class OnboardingController extends Controller {
    public function index(): void {
        $preselected = $_GET['track'] ?? '';
        $this->view('pages/academy/onboarding', [
            'title'        => 'Apply to Afrotech Academy · Afrostrength',
            'description'  => 'Five steps to a seat in the next Afrotech Academy cohort. Global certifications. Powerful skills.',
            'tracks'       => Course::all(),
            'preselected'  => $preselected,
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Apply'],
            ],
        ], 'academy');
    }

    public function submit(): void {
        Csrf::require();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        // Map skill-level keyword to the experience_level enum we already store.
        $levelMap = [
            'beginner' => 'starting', 'basic' => 'some',
            'intermediate' => 'experienced', 'advanced' => 'advanced',
        ];
        $skillLevel = $this->input('skill_level', 'beginner');
        $experienceLevel = $levelMap[$skillLevel] ?? 'starting';

        // Compose the free-text "goal" from intent + portfolio so the studio
        // sees the trust-first narrative without a dedicated goal field.
        $intent     = $this->input('intent');
        $github     = $this->input('github_url');
        $portfolio  = $this->input('portfolio_url');
        $linkedin   = $this->input('linkedin_url');
        $behance    = $this->input('behance_url');
        $showcase   = $this->input('showcase');
        $skillScores = $this->input('skill_scores',     '{}');
        $expScores   = $this->input('experience_scores','{}');

        $goalParts = [];
        if ($intent)    $goalParts[] = 'Intent: ' . $intent;
        if ($showcase)  $goalParts[] = 'Showcase: ' . $showcase;
        if ($github)    $goalParts[] = 'GitHub: ' . $github;
        if ($portfolio) $goalParts[] = 'Portfolio: ' . $portfolio;
        if ($linkedin)  $goalParts[] = 'LinkedIn: ' . $linkedin;
        if ($behance)   $goalParts[] = 'Behance: ' . $behance;
        $goalParts[] = 'Skill scores: ' . $skillScores;
        $goalParts[] = 'Experience scores: ' . $expScores;

        $data = [
            'name'             => $this->input('name'),
            'email'            => $this->input('email'),
            'phone'            => $this->input('phone'),
            'country'          => $this->input('country', 'Nigeria'),
            'current_role'     => $this->input('current_status'),
            'experience_level' => $experienceLevel,
            'track_slug'       => $this->input('track_slug'),
            'goal'             => implode("\n", $goalParts),
            'cohort_pref'      => $this->input('cohort_pref', 'next'),
            'cohort_target_date' => $this->input('cohort_target_date'),
            'referral_source'  => $intent,
            'marketing_opt_in' => $this->input('marketing_opt_in', '0'),
        ];
        // Only retain the target date when the user actually picked "specific".
        // Otherwise it's stale UI state from a discarded branch.
        if (($data['cohort_pref'] ?? 'next') !== 'specific') {
            $data['cohort_target_date'] = null;
        } elseif (!empty($data['cohort_target_date']) &&
                  !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$data['cohort_target_date'])) {
            $data['cohort_target_date'] = null;
        }
        $password = (string) $this->input('password', '');
        $confirm  = (string) $this->input('password_confirm', '');

        $v = (new Validator($data + ['password' => $password, 'password_confirm' => $confirm]))
            ->check('name',       ['required', 'min:2', 'max:160'])
            ->check('email',      ['required', 'email'])
            ->check('track_slug', ['required'])
            ->check('password',   ['required', 'min:8']);
        if (!empty($data['phone']))  $v->check('phone', ['phone']);

        if ($password !== $confirm) {
            $errs = $v->errors();
            $errs['password_confirm'] = 'Passwords do not match.';
            if ($isAjax) $this->json(['error' => 'validation', 'fields' => $errs], 422);
            flash_set('onboarding_error', 'Passwords do not match.');
            $this->redirect('/academy/apply');
            return;
        }

        if (!$v->passes()) {
            if ($isAjax) $this->json(['error' => 'validation', 'fields' => $v->errors()], 422);
            flash_set('onboarding_error', 'Some fields need attention.');
            $this->redirect('/academy/apply');
            return;
        }

        // Reject duplicates with a friendly message
        if (Student::findByEmail($data['email'])) {
            if ($isAjax) $this->json(['error' => 'duplicate', 'message' => 'You\'ve already started an application with this email — check your inbox.'], 409);
            flash_set('onboarding_error', 'You\'ve already started an application with this email.');
            $this->redirect('/academy/apply');
            return;
        }

        // Free breached-password check via Have-I-Been-Pwned k-anonymity.
        // We never send the password; only the first 5 chars of its SHA-1.
        // Fail-open: if HIBP is down we don't block the sign-up.
        $breached = Security::passwordIsBreached($password);
        if (is_int($breached) && $breached > 0) {
            $msg = 'That password has appeared in known data breaches (' . number_format($breached) . ' times). Please choose a different one.';
            if ($isAjax) $this->json(['error' => 'weak_password', 'fields' => ['password' => $msg]], 422);
            else { flash_set('onboarding_error', $msg); $this->redirect('/academy/apply'); }
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $id = Student::create($data, $hash);

        // Mirror the student into Moodle if LMS is configured. Idempotent
        // and a no-op when LMS_ENABLED=0. Errors are logged inside Lms::call.
        if (Lms::enabled()) {
            $mirror = Lms::mirrorStudent($data + ['name' => $data['name'], 'email' => $data['email']]);
            if (!$mirror['ok']) error_log('[onboard] LMS mirror failed: ' . ($mirror['detail'] ?? '?'));
        }

        // Mirror as enrolment record so admin/academy/enrollments shows it too
        Enrollment::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'phone'        => $data['phone'],
            'course_id'    => '',
            'course_title' => 'AFROTECH · ' . ($this->trackName($data['track_slug']) ?: $data['track_slug']),
        ]);

        // Notify the studio
        $body  = '<h2>New Afrotech Academy application</h2>';
        $body .= '<table cellpadding="6" style="font-family:Arial;font-size:14px;">';
        foreach ($data as $k => $v2) {
            if ($v2 === null || $v2 === '') continue;
            $body .= '<tr><td style="vertical-align:top;color:#888;text-transform:uppercase;font-size:11px;letter-spacing:0.12em;">'
                  . htmlspecialchars($k) . '</td><td>' . nl2br(htmlspecialchars((string)$v2)) . '</td></tr>';
        }
        $body .= '</table>';
        Mailer::send('Afrotech Academy · New application', $body, $data['email']);

        // Welcome the student via session
        $_SESSION['student_pending'] = [
            'id'    => $id,
            'name'  => $data['name'],
            'email' => $data['email'],
            'track' => $this->trackName($data['track_slug']),
        ];

        if ($isAjax) $this->json(['ok' => true, 'id' => $id, 'redirect' => '/academy/apply/welcome']);
        else $this->redirect('/academy/apply/welcome');
    }

    public function welcome(): void {
        $pending = $_SESSION['student_pending'] ?? null;
        if (!$pending) { $this->redirect('/academy/apply'); return; }
        // Expire the welcome session after one render
        unset($_SESSION['student_pending']);

        $this->view('pages/academy/onboarding-welcome', [
            'title'        => 'Welcome to Afrotech Academy · Afrostrength',
            'description'  => 'Your application is in. Here\'s what happens next.',
            'pending'      => $pending,
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Welcome'],
            ],
        ], 'academy');
    }

    private function trackName(?string $slug): ?string {
        if (!$slug) return null;
        foreach (Course::all() as $c) if (($c['slug'] ?? '') === $slug) return $c['title'];
        return null;
    }
}
