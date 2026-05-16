<?php

class Student {
    /**
     * Persist a new academy student. Returns the new id, or 0 if the DB
     * isn't reachable (the row is also written to a soft-fail log so the
     * inbox sees it).
     */
    public static function create(array $data, string $passwordHash): int {
        if (!Database::available()) {
            $dir = AFS_ROOT . '/storage';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            @file_put_contents($dir . '/students.log',
                json_encode($data + ['hash' => $passwordHash]) . "\n", FILE_APPEND | LOCK_EX);
            return 0;
        }
        return (int) Database::insert(
            'INSERT INTO students
                (name, email, password_hash, phone, country, current_role,
                 experience_level, track_slug, goal, cohort_pref, cohort_target_date,
                 referral_source, marketing_opt_in, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, "onboarding")',
            [
                $data['name']            ?? '',
                $data['email']           ?? '',
                $passwordHash,
                $data['phone']           ?? null,
                $data['country']         ?? 'Nigeria',
                $data['current_role']    ?? null,
                in_array($data['experience_level'] ?? 'starting',
                    ['starting','some','experienced','advanced'], true)
                    ? $data['experience_level'] : 'starting',
                $data['track_slug']      ?? null,
                $data['goal']            ?? null,
                $data['cohort_pref']     ?? 'next',
                $data['cohort_target_date'] ?? null,
                $data['referral_source'] ?? null,
                !empty($data['marketing_opt_in']) ? 1 : 0,
            ]
        );
    }

    public static function findByEmail(string $email): ?array {
        if (!Database::available()) return null;
        return Database::one('SELECT * FROM students WHERE email = ?', [$email]);
    }

    public static function all(): array {
        if (!Database::available()) return [];
        return Database::all('SELECT * FROM students ORDER BY created_at DESC');
    }

    public static function findByGoogleUid(string $uid): ?array {
        if (!Database::available()) return null;
        return Database::one('SELECT * FROM students WHERE google_uid = ?', [$uid]);
    }

    /**
     * Find or create a student from a verified Google identity. Used by
     * the Firebase Google sign-in flow. The password hash is left empty
     * (login is only via Google for these accounts until the user sets
     * a password from their dashboard).
     */
    public static function findOrCreateFromGoogle(array $identity): ?array {
        $email = strtolower(trim($identity['email'] ?? ''));
        $uid   = (string)($identity['uid'] ?? '');
        if ($email === '' || $uid === '') return null;
        if (!Database::available()) {
            $dir = AFS_ROOT . '/storage';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            @file_put_contents($dir . '/students.log',
                json_encode(['google' => $identity]) . "\n", FILE_APPEND | LOCK_EX);
            // Return a phantom row so the caller can still sign the visitor in.
            return [
                'id'         => 0,
                'name'       => $identity['name']  ?? 'Student',
                'email'      => $email,
                'google_uid' => $uid,
                'avatar_url' => $identity['picture'] ?? null,
                'status'     => 'onboarding',
                'track_slug' => null,
            ];
        }

        // 1) Match by Google UID, the strongest identity.
        $row = self::findByGoogleUid($uid);
        if ($row) return $row;

        // 2) Match by email — link this Google identity to the existing row.
        $row = self::findByEmail($email);
        if ($row) {
            Database::exec(
                'UPDATE students SET google_uid = ?, avatar_url = COALESCE(NULLIF(avatar_url, ""), ?) WHERE id = ?',
                [$uid, $identity['picture'] ?? null, (int)$row['id']]
            );
            return self::findByEmail($email);
        }

        // 3) New student — minimal onboarding row, no password.
        $id = (int) Database::insert(
            'INSERT INTO students
                (name, email, password_hash, google_uid, avatar_url, country, status)
             VALUES (?,?,?,?,?,?, "onboarding")',
            [
                $identity['name']    ?? 'Student',
                $email,
                '', // no password — Google-only account for now
                $uid,
                $identity['picture'] ?? null,
                'Nigeria',
            ]
        );
        return Database::one('SELECT * FROM students WHERE id = ?', [$id]);
    }
}
