<?php

/**
 * Lms — Moodle web-service bridge.
 *
 * Calls Moodle's `/webservice/rest/server.php` with a service token and the
 * function names Moodle exposes (e.g. core_course_get_courses_by_field,
 * core_enrol_get_users_courses). Soft-fails to seeded fallback data so the
 * marketing site renders even before the LMS is live.
 *
 * Setup in Moodle:
 *   1. Site administration → Plugins → Web services → Manage protocols → enable REST.
 *   2. Manage tokens → create a token for a service that exposes
 *      'core_course_get_courses_by_field', 'core_user_create_users',
 *      'enrol_manual_enrol_users', 'core_webservice_get_site_info'.
 *   3. Set LMS_TOKEN env var (or in config/app.php) to that token.
 */
class Lms {
    public static function enabled(): bool { return LMS_ENABLED && !empty(LMS_TOKEN); }

    public static function call(string $function, array $params = []): array {
        if (!self::enabled()) return [];
        $url = rtrim(LMS_BASE_URL, '/') . '/webservice/rest/server.php';
        $body = http_build_query(array_merge([
            'wstoken'           => LMS_TOKEN,
            'wsfunction'        => $function,
            'moodlewsrestformat'=> 'json',
        ], $params));

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $body,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 6,
            CURLOPT_CONNECTTIMEOUT  => 3,
            CURLOPT_SSL_VERIFYPEER  => true,
        ]);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            error_log('[lms] ' . $err);
            return [];
        }
        $data = json_decode($resp, true);
        if (!is_array($data) || isset($data['exception'])) {
            error_log('[lms] ' . substr((string)$resp, 0, 500));
            return [];
        }
        return $data;
    }

    /** Build a deep-link to a Moodle course player. */
    public static function courseUrl(int $moodleCourseId): string {
        return rtrim(LMS_BASE_URL, '/') . '/course/view.php?id=' . $moodleCourseId;
    }

    /** Build a deep-link to the Moodle enrol page for a course. */
    public static function enrolUrl(int $moodleCourseId): string {
        return rtrim(LMS_BASE_URL, '/') . '/enrol/index.php?id=' . $moodleCourseId;
    }

    /** Build a deep-link to the Moodle login surface. */
    public static function loginUrl(): string {
        return rtrim(LMS_BASE_URL, '/') . '/login/index.php';
    }

    // ----- Catalog (used by /academy/courses fallback when DB lookup is empty) -----

    /**
     * Fetch every visible Moodle course. Falls back to [] on failure so the
     * caller can use seeded data without conditional checks.
     */
    public static function listCourses(): array {
        if (!self::enabled()) return [];
        $res = self::call('core_course_get_courses_by_field');
        return $res['courses'] ?? [];
    }

    public static function getCourse(int $id): ?array {
        if (!self::enabled()) return null;
        $res = self::call('core_course_get_courses_by_field', ['field' => 'id', 'value' => $id]);
        return $res['courses'][0] ?? null;
    }

    // ----- Users -----------------------------------------------------

    /** Find a Moodle user row by email. Returns null if not found. */
    public static function findUserByEmail(string $email): ?array {
        if (!self::enabled()) return null;
        $res = self::call('core_user_get_users', [
            'criteria[0][key]'   => 'email',
            'criteria[0][value]' => $email,
        ]);
        $users = $res['users'] ?? [];
        return $users[0] ?? null;
    }

    /**
     * Create a Moodle user. Moodle requires:
     *   username, password (or createpassword=1), firstname, lastname, email.
     * We split the student's name into first/last on the last space.
     * Returns the created user id, or 0 on failure.
     */
    public static function createUser(array $student, string $password = ''): int {
        if (!self::enabled()) return 0;
        $email = strtolower((string)($student['email'] ?? ''));
        if ($email === '') return 0;
        $name  = trim((string)($student['name'] ?? ''));
        $parts = preg_split('/\s+/', $name);
        $first = (string)array_shift($parts);
        $last  = $parts ? implode(' ', $parts) : ($first ?: 'Student');
        if ($first === '') $first = 'Student';

        $params = [
            'users[0][username]'  => self::usernameFromEmail($email),
            'users[0][firstname]' => $first,
            'users[0][lastname]'  => $last,
            'users[0][email]'     => $email,
            'users[0][auth]'      => 'manual',
            'users[0][lang]'      => 'en',
        ];
        if ($password !== '') {
            $params['users[0][password]'] = $password;
        } else {
            // Moodle will email a "set your password" link via the auth_email
            // workflow if it's enabled on the site.
            $params['users[0][createpassword]'] = 1;
        }
        $res = self::call('core_user_create_users', $params);
        return (int) ($res[0]['id'] ?? 0);
    }

    // ----- Enrolment -------------------------------------------------

    /**
     * Enrol a Moodle user into a Moodle course. roleId 5 = student in a stock
     * Moodle install. Returns true on success, false otherwise.
     */
    public static function enrolUser(int $moodleUserId, int $moodleCourseId, int $roleId = 5): bool {
        if (!self::enabled() || $moodleUserId <= 0 || $moodleCourseId <= 0) return false;
        $res = self::call('enrol_manual_enrol_users', [
            'enrolments[0][roleid]'   => $roleId,
            'enrolments[0][userid]'   => $moodleUserId,
            'enrolments[0][courseid]' => $moodleCourseId,
        ]);
        // enrol_manual_enrol_users returns null on success, an exception array on failure.
        return !isset($res['exception']);
    }

    /** Courses the user is enrolled in (most-recent first). */
    public static function coursesForUser(int $moodleUserId): array {
        if (!self::enabled() || $moodleUserId <= 0) return [];
        $res = self::call('core_enrol_get_users_courses', ['userid' => $moodleUserId]);
        return is_array($res) ? $res : [];
    }

    // ----- Higher-level convenience ----------------------------------

    /**
     * Idempotently make sure the Moodle user exists for a given Afrostrength
     * student row, and (optionally) is enrolled into a Moodle course.
     * Returns ['ok' => bool, 'user_id' => int, 'enrolled' => bool, 'detail' => string].
     *
     * Safe to call on every successful onboarding even if LMS is not yet
     * configured — when LMS_ENABLED is off the call is a no-op.
     */
    public static function mirrorStudent(array $student, ?int $moodleCourseId = null): array {
        if (!self::enabled()) return ['ok' => false, 'user_id' => 0, 'enrolled' => false, 'detail' => 'LMS disabled'];
        $email = strtolower((string)($student['email'] ?? ''));
        if ($email === '') return ['ok' => false, 'user_id' => 0, 'enrolled' => false, 'detail' => 'no email'];

        $user = self::findUserByEmail($email);
        $uid  = (int)($user['id'] ?? 0);
        if (!$uid) {
            $uid = self::createUser($student);
            if (!$uid) return ['ok' => false, 'user_id' => 0, 'enrolled' => false, 'detail' => 'create failed'];
        }
        $enrolled = false;
        if ($moodleCourseId) {
            $enrolled = self::enrolUser($uid, $moodleCourseId);
        }
        return ['ok' => true, 'user_id' => $uid, 'enrolled' => $enrolled, 'detail' => $enrolled ? 'created+enrolled' : 'created'];
    }

    /** Slug-style username from the email local part (Moodle requires lowercase alnum). */
    private static function usernameFromEmail(string $email): string {
        $local = strstr($email, '@', true) ?: $email;
        $u = strtolower(preg_replace('/[^a-z0-9._-]/', '', strtolower($local)));
        return $u !== '' ? $u : 'student' . substr(sha1($email), 0, 6);
    }

    /**
     * Round-trip health check. Returns:
     *   ['ok' => bool, 'mode' => 'live'|'demo', 'detail' => string, 'site' => array]
     *
     * Used by the admin dashboard and /api/health/lms — surfaces precisely
     * why the integration is or isn't working without leaking the token.
     */
    public static function selfTest(): array {
        if (!defined('LMS_ENABLED') || !LMS_ENABLED) {
            return ['ok' => false, 'mode' => 'demo', 'detail' => 'LMS_ENABLED is off — running on seeded catalog.', 'site' => []];
        }
        if (empty(LMS_TOKEN)) {
            return ['ok' => false, 'mode' => 'demo', 'detail' => 'LMS_ENABLED is on but LMS_TOKEN is empty.', 'site' => []];
        }
        if (empty(LMS_BASE_URL)) {
            return ['ok' => false, 'mode' => 'demo', 'detail' => 'LMS_BASE_URL is empty.', 'site' => []];
        }
        $info = self::call('core_webservice_get_site_info');
        if (empty($info) || empty($info['sitename'])) {
            return ['ok' => false, 'mode' => 'demo', 'detail' => 'Token rejected or unreachable host.', 'site' => $info];
        }
        return [
            'ok'     => true,
            'mode'   => 'live',
            'detail' => 'Connected to ' . $info['sitename'] . ' (release ' . ($info['release'] ?? '?') . ').',
            'site'   => [
                'name'    => $info['sitename'],
                'release' => $info['release'] ?? null,
                'user'    => $info['fullname'] ?? null,
                'url'     => $info['siteurl']  ?? LMS_BASE_URL,
            ],
        ];
    }
}
