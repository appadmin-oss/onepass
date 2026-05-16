<?php
/** @var Router $router */

// --- Public ----------------------------------------------------------
$router->get('/',                              [HomeController::class,        'index']);

$router->get('/about',                         [AboutController::class,       'index']);
$router->get('/about/overview',                [AboutController::class,       'overview']);
$router->get('/about/mission',                 [AboutController::class,       'mission']);
$router->get('/about/team',                    [AboutController::class,       'team']);
$router->get('/about/methodology',             [AboutController::class,       'methodology']);

$router->get('/services',                      [ServicesController::class,    'index']);
$router->get('/services/{slug}',               [ServicesController::class,    'show']);

$router->get('/projects',                      [ProjectsController::class,    'index']);
$router->get('/projects/{slug}',               [ProjectsController::class,    'show']);

$router->get('/blog',                          [BlogController::class,        'index']);
$router->get('/field-notes',                   [BlogController::class,        'index']);   // alias
$router->get('/blog/{slug}',                   [BlogController::class,        'show']);
$router->get('/field-notes/{slug}',            [BlogController::class,        'show']);

$router->get('/contact',                       [ContactController::class,     'index']);
$router->get('/faq',                           [FaqController::class,         'index']);
$router->get('/testimonials',                  [TestimonialsController::class,'index']);

// AJAX inquiry endpoint (also handles non-JS POSTs from /contact)
$router->post('/api/inquiries',                [ContactController::class,     'submit']);
$router->post('/api/newsletter',               [ContactController::class,     'newsletter']);

// --- Academy --------------------------------------------------------
$router->get('/academy',                       [AcademyController::class,     'index']);
$router->get('/academy/courses',               [AcademyController::class,     'courses']);
$router->get('/academy/courses/{slug}',        [AcademyController::class,     'course']);
$router->get('/academy/instructors',           [AcademyController::class,     'instructors']);
$router->get('/academy/certifications',        [AcademyController::class,     'certifications']);
$router->get('/academy/live-sessions',         [AcademyController::class,     'liveSessions']);
$router->get('/academy/enroll',                [AcademyController::class,     'enroll']);
$router->post('/api/enrollments',              [AcademyController::class,     'submitEnrollment']);

// Public-facing learner auth — passwordless magic link + OTP is the default.
$router->get('/login',                         [AuthController::class,         'showLogin']);
$router->post('/login',                        [AuthController::class,         'requestLink']);
$router->get('/login/check',                   [AuthController::class,         'check']);
$router->post('/login/verify-otp',             [AuthController::class,         'verifyOtp']);
$router->get('/auth/verify',                   [AuthController::class,         'verifyLink']);
$router->get('/login/password',                [AuthController::class,         'showPassword']);
$router->post('/login/password',               [StudentController::class,      'login']);
$router->post('/auth/google',                  [StudentController::class,      'googleSignIn']);
$router->post('/logout',                       [StudentController::class,      'logout']);
$router->get('/dashboard',                     [StudentController::class,      'dashboard']);
$router->get('/dashboard/profile',              [StudentController::class,      'profile']);
$router->post('/dashboard/profile',             [StudentController::class,      'profileSave']);

// Public status page (also served on status.afrostrength.com)
$router->get('/status',                         [StatusController::class,       'index']);
$router->get('/status/health.json',             [StatusController::class,       'healthJson']);
$router->get('/status/badge.svg',               [StatusController::class,       'badge']);
$router->get('/status/badge-dark.svg',          [StatusController::class,       'badgeDark']);
$router->get('/status/badge-square.svg',        [StatusController::class,       'badgeSquare']);
$router->get('/status/embed',                   [StatusController::class,       'embed']);
$router->get('/status/api',                     [StatusController::class,       'api']);
$router->get('/status/feed.rss',                [StatusController::class,       'rssFeed']);
$router->get('/status/feed.atom',               [StatusController::class,       'atomFeed']);
$router->get('/status/incidents/{code}',        [StatusController::class,       'incidentShow']);
$router->get('/status/ai-summary',              [StatusController::class,       'aiSummary']);
$router->post('/status/subscribe',              [StatusController::class,       'subscribe']);

// Operator surfaces for the status page — admin-guarded inside the controller.
$router->get ('/admin/status/incidents',                    ['Admin\StatusIncidentsController', 'index']);
$router->get ('/admin/status/incidents/new',                ['Admin\StatusIncidentsController', 'newForm']);
$router->post('/admin/status/incidents',                    ['Admin\StatusIncidentsController', 'create']);
$router->get ('/admin/status/incidents/{id}/edit',          ['Admin\StatusIncidentsController', 'edit']);
$router->post('/admin/status/incidents/{id}',               ['Admin\StatusIncidentsController', 'update']);
$router->post('/admin/status/incidents/{id}/resolve',       ['Admin\StatusIncidentsController', 'resolve']);
$router->post('/admin/status/incidents/{id}/postmortem',    ['Admin\StatusIncidentsController', 'postmortemDraft']);
$router->post('/admin/status/incidents/{id}/delete',        ['Admin\StatusIncidentsController', 'delete']);

// Two-factor (TOTP) — enrolment + login challenge
$router->get('/dashboard/2fa',                 [TwoFactorController::class,    'setup']);
$router->post('/dashboard/2fa/enable',         [TwoFactorController::class,    'enable']);
$router->post('/dashboard/2fa/disable',        [TwoFactorController::class,    'disable']);
$router->get('/login/2fa',                     [TwoFactorController::class,    'challengeForm']);
$router->post('/login/2fa',                    [TwoFactorController::class,    'challengeSubmit']);

// Public certificate verification
$router->get('/verify',                        [CertificateController::class,  'index']);
$router->get('/verify/{code}',                 [CertificateController::class,  'show']);

// Afrotech Academy onboarding (multi-step registration)
$router->get('/academy/apply',                 [OnboardingController::class,  'index']);
$router->get('/academy/apply/welcome',         [OnboardingController::class,  'welcome']);
$router->post('/api/onboarding',               [OnboardingController::class,  'submit']);

// Free tools — short link, QR generator, UTM builder
$router->get('/tools',                         [ToolsController::class,       'index']);
$router->get('/tools/short-link',              [ToolsController::class,       'shortener']);
$router->get('/tools/qr',                      [ToolsController::class,       'qr']);
$router->get('/tools/utm',                     [ToolsController::class,       'utm']);
$router->get('/tools/career-path',             [ToolsController::class,       'careerPath']);
$router->get('/tools/salary',                  [ToolsController::class,       'salary']);
$router->get('/tools/palette',                 [ToolsController::class,       'palette']);
$router->get('/tools/contrast',                [ToolsController::class,       'contrast']);
$router->get('/tools/favicon',                 [ToolsController::class,       'favicon']);
$router->get('/tools/og',                      [ToolsController::class,       'og']);
$router->get('/tools/regex',                   [ToolsController::class,       'regex']);
$router->get('/tools/jsonld',                  [ToolsController::class,       'jsonld']);
$router->get('/tools/yaml',                    [ToolsController::class,       'yaml']);
$router->get('/tools/diff',                    [ToolsController::class,       'diff']);
$router->get('/tools/hash',                    [ToolsController::class,       'hash']);
$router->get('/tools/ipsum',                   [ToolsController::class,       'ipsum']);
$router->get('/tools/slug',                    [ToolsController::class,       'slug']);

// Short-link backend — public redirect + API
$router->post('/api/short-links',              [ToolsController::class,       'shortenerCreate']);
$router->get('/api/short-links/{code}',        [ToolsController::class,       'shortenerStats']);
$router->get('/s/{code}',                      [ToolsController::class,       'shortenerRedirect']);

// Opportunities & scholarships
$router->get('/opportunities',                 [OpportunitiesController::class, 'index']);

// Integration health checks — JSON
$router->get('/api/health/lms',                [HealthController::class,        'lms']);
$router->get('/api/health/jaas',               [HealthController::class,        'jaas']);
$router->get('/api/health/ai',                 [HealthController::class,        'ai']);

// AI endpoints — free, no key (Pollinations.ai)
$router->post('/api/ai/chat',                  [AiController::class,            'chat']);
$router->post('/api/ai/reset',                 [AiController::class,            'resetChat']);
$router->post('/api/ai/suggest',               [AiController::class,            'suggest']);

// Legal
$router->get('/legal/privacy',                 [LegalController::class,         'privacy']);
$router->get('/legal/terms',                   [LegalController::class,         'terms']);
$router->get('/legal/cookies',                 [LegalController::class,         'cookies']);
$router->get('/legal/acceptable-use',          [LegalController::class,         'acceptableUse']);
$router->get('/legal/imprint',                 [LegalController::class,         'imprint']);

// --- Admin ----------------------------------------------------------
$router->get('/admin/login',                   ['Admin\AuthController',       'showLogin']);
$router->post('/admin/login',                  ['Admin\AuthController',       'login']);
$router->post('/admin/logout',                 ['Admin\AuthController',       'logout']);

$router->get('/admin',                         ['Admin\DashboardController',  'index']);

$router->get('/admin/posts',                   ['Admin\PostsController',      'index']);
$router->get('/admin/posts/new',               ['Admin\PostsController',      'edit']);
$router->get('/admin/posts/{id}/edit',         ['Admin\PostsController',      'edit']);
$router->post('/admin/posts/save',             ['Admin\PostsController',      'save']);
$router->post('/admin/posts/{id}/delete',      ['Admin\PostsController',      'delete']);

$router->get('/admin/projects',                ['Admin\ProjectsController',   'index']);
$router->get('/admin/projects/new',            ['Admin\ProjectsController',   'edit']);
$router->get('/admin/projects/{id}/edit',      ['Admin\ProjectsController',   'edit']);
$router->post('/admin/projects/save',          ['Admin\ProjectsController',   'save']);
$router->post('/admin/projects/{id}/delete',   ['Admin\ProjectsController',   'delete']);

$router->get('/admin/services',                ['Admin\ServicesController',   'index']);
$router->get('/admin/services/{id}/edit',      ['Admin\ServicesController',   'edit']);
$router->post('/admin/services/save',          ['Admin\ServicesController',   'save']);

$router->get('/admin/inquiries',               ['Admin\InquiriesController',  'index']);
$router->get('/admin/inquiries/{id}',          ['Admin\InquiriesController',  'show']);
$router->post('/admin/inquiries/{id}/status',  ['Admin\InquiriesController',  'updateStatus']);
$router->post('/admin/inquiries/{id}/route',   ['Admin\InquiriesController',  'acceptRoute']);
$router->post('/admin/inquiries/{id}/route-now', ['Admin\InquiriesController','routeNow']);

$router->get('/admin/academy/{section}',       ['Admin\AcademyController',    'index']);
$router->post('/admin/academy/save',           ['Admin\AcademyController',    'save']);

$router->get('/admin/content',                 ['Admin\ContentController',    'index']);
$router->post('/admin/content/save',           ['Admin\ContentController',    'save']);

$router->get('/admin/promotions',              ['Admin\PromotionsController', 'index']);
$router->get('/admin/promotions/new',          ['Admin\PromotionsController', 'edit']);
$router->get('/admin/promotions/{id}/edit',    ['Admin\PromotionsController', 'edit']);
$router->post('/admin/promotions/save',        ['Admin\PromotionsController', 'save']);
$router->post('/admin/promotions/{id}/delete', ['Admin\PromotionsController', 'delete']);
$router->post('/admin/promotions/upload',      ['Admin\PromotionsController', 'upload']);

// --- SEO ------------------------------------------------------------
$router->get('/sitemap.xml',                   [HomeController::class,        'sitemap']);
$router->get('/robots.txt',                    [HomeController::class,        'robots']);
