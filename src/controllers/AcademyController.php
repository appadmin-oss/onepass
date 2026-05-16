<?php

class AcademyController extends Controller {
    public function index(): void {
        $this->render('pages/academy/index', [
            'title'        => 'Academy · Afrostrength — Skill up. Move up.',
            'description'  => 'Cohort-based courses, certifications, and live sessions for marketing leads, in-house creatives, and operators.',
            'courses'      => array_slice(Course::all(), 0, 3),
            'instructors'  => Instructor::all(),
            'certs'        => Certification::all(),
            'sessions'     => array_slice(LiveSession::upcoming(), 0, 3),
            'lmsEnabled'   => Lms::enabled(),
            'jaasEnabled'  => Jaas::enabled(),
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy'],
            ],
            'needsSvgJs'      => true,
            'needsGraphicsJs' => true,
        ]);
    }

    public function courses(): void {
        $this->render('pages/academy/courses-index', [
            'title'        => 'Courses · Afrostrength Academy',
            'description'  => 'Cohort-based courses for the operators of African brands.',
            'courses'      => Course::all(),
            'lmsEnabled'   => Lms::enabled(),
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Courses'],
            ],
        ]);
    }

    public function course(string $slug): void {
        $course = Course::find($slug);
        if (!$course) { $this->notFound(); return; }
        $this->render('pages/academy/course-detail', [
            'title'       => $course['title'] . ' · Afrostrength Academy',
            'description' => $course['summary'] ?? '',
            'course'      => $course,
            'instructors' => Instructor::all(),
            'lmsEnabled'  => Lms::enabled(),
            'breadcrumbs' => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Courses', 'href' => url('/academy/courses')],
                ['label' => $course['title']],
            ],
            'jsonld' => [[
                '@context'    => 'https://schema.org',
                '@type'       => 'Course',
                'name'        => $course['title'],
                'description' => $course['summary'] ?? '',
                'provider'    => ['@type' => 'Organization', 'name' => AFS_NAME . ' Academy'],
            ]],
        ]);
    }

    public function instructors(): void {
        $this->render('pages/academy/instructors', [
            'title'        => 'Instructors · Afrostrength Academy',
            'description'  => 'The operators who teach the curriculum — every one of them shipping in the studio.',
            'instructors'  => Instructor::all(),
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Instructors'],
            ],
        ]);
    }

    public function certifications(): void {
        $this->render('pages/academy/certifications', [
            'title'        => 'Certifications · Afrostrength Academy',
            'description'  => 'Build the skills that move careers — and certify them with credentials our clients hire against.',
            'certs'        => Certification::all(),
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Certifications'],
            ],
        ]);
    }

    public function liveSessions(): void {
        // Build a working embed config for the preview room — falls back to
        // public meet.jit.si when no JaaS tenant is provisioned, so the page
        // is functional out-of-the-box.
        $room   = 'cohort-' . date('Ymd');
        $embed  = Jaas::embedConfig($room, [
            'id'    => bin2hex(random_bytes(6)),
            'name'  => 'Guest',
            'email' => '',
        ]);
        $this->render('pages/academy/live-sessions', [
            'title'        => 'Live sessions · Afrostrength Academy',
            'description'  => 'Working sessions, clinics, and Q&As — live with the studio operators.',
            'sessions'     => LiveSession::upcoming(),
            'jaasEnabled'  => true, // always renders the embed now
            'jaasMode'     => $embed['mode'],
            'jaasScript'   => $embed['script'],
            'jaasToken'    => $embed['jwt'],
            'jaasRoom'     => $embed['roomName'],
            'jaasDomain'   => $embed['domain'],
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Live sessions'],
            ],
        ]);
    }

    public function enroll(): void {
        $courseSlug = $_GET['course'] ?? '';
        $this->render('pages/academy/enroll', [
            'title'        => 'Enroll · Afrostrength Academy',
            'description'  => 'Three steps. Confirm course, share details, secure your seat.',
            'courses'      => Course::all(),
            'preselected'  => $courseSlug,
            'breadcrumbs'  => [
                ['label' => 'Studio',  'href' => url('/')],
                ['label' => 'Academy', 'href' => url('/academy')],
                ['label' => 'Enroll'],
            ],
        ]);
    }

    public function submitEnrollment(): void {
        Csrf::require();
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

        $data = [
            'name'         => $this->input('name'),
            'email'        => $this->input('email'),
            'phone'        => $this->input('phone'),
            'course_id'    => $this->input('course_id'),
            'course_title' => $this->input('course_title'),
        ];
        $v = (new Validator($data))
            ->check('name',  ['required', 'min:2'])
            ->check('email', ['required', 'email']);
        if (!$v->passes()) {
            if ($isAjax) $this->json(['error' => 'validation', 'fields' => $v->errors()], 422);
            $this->redirect('/academy/enroll');
            return;
        }

        $id = Enrollment::create($data);

        // Forward to Moodle if wired
        $moodleResult = null;
        if (Lms::enabled() && !empty($data['course_id'])) {
            $moodleResult = Lms::call('core_user_create_users', [
                'users[0][username]'  => strtolower(preg_replace('/[^a-z0-9]/', '', $data['email'])),
                'users[0][password]'  => bin2hex(random_bytes(8)) . 'A1!',
                'users[0][firstname]' => trim(strtok($data['name'], ' ')),
                'users[0][lastname]'  => trim(substr($data['name'], strlen(strtok($data['name'], ' ')))) ?: '—',
                'users[0][email]'     => $data['email'],
            ]);
        }

        Mailer::send(
            'Afrostrength Academy · New enrolment',
            '<p>New enrolment from ' . e($data['name']) . ' (' . e($data['email']) . ') for ' . e($data['course_title']) . '.</p>',
            $data['email']
        );

        if ($isAjax) $this->json(['ok' => true, 'id' => $id, 'lms' => $moodleResult]);
        else $this->redirect('/academy/enroll?ok=1');
    }

    /** All academy pages render with the academy layout. */
    private function render(string $page, array $data): void {
        $this->view($page, $data, 'academy');
    }
}
