<?php

/**
 * IssuedCertificate — record of a completed certification.
 *
 * The model is intentionally small. The only public surface the
 * marketing site exposes is by `code` — verification is a single
 * lookup and never exposes student email or sensitive fields.
 */
class IssuedCertificate {
    public static function findByCode(string $code): ?array {
        if (!Database::available()) return null;
        $row = Database::one(
            'SELECT * FROM issued_certificates WHERE code = ? LIMIT 1',
            [self::normaliseCode($code)]
        );
        return $row ?: null;
    }

    /**
     * Issue a certificate. Returns the new row id, or 0 if the
     * DB isn't reachable. Generates a unique, scannable code.
     */
    public static function issue(array $payload): int {
        if (!Database::available()) return 0;
        $code = self::generateCode();
        $skills = is_array($payload['skills'] ?? null) ? json_encode(array_values($payload['skills']), JSON_UNESCAPED_UNICODE) : null;
        return (int) Database::insert(
            'INSERT INTO issued_certificates
                (code, student_id, student_name, student_email,
                 certification_id, certification_title,
                 course_id, course_title, cohort, grade, skills_json,
                 issued_on, expires_on)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $code,
                (int)($payload['student_id'] ?? 0),
                (string)($payload['student_name'] ?? ''),
                strtolower((string)($payload['student_email'] ?? '')),
                $payload['certification_id'] ?? null,
                (string)($payload['certification_title'] ?? ''),
                $payload['course_id'] ?? null,
                $payload['course_title'] ?? null,
                $payload['cohort'] ?? null,
                $payload['grade'] ?? null,
                $skills,
                $payload['issued_on'] ?? date('Y-m-d'),
                $payload['expires_on'] ?? null,
            ]
        );
    }

    public static function revoke(int $id, string $reason): bool {
        if (!Database::available()) return false;
        return Database::exec(
            'UPDATE issued_certificates SET revoked_at = NOW(), revoked_reason = ? WHERE id = ?',
            [$reason, $id]
        ) > 0;
    }

    public static function recent(int $limit = 50): array {
        if (!Database::available()) return [];
        $limit = max(1, min(500, $limit));
        return Database::all(
            'SELECT * FROM issued_certificates ORDER BY id DESC LIMIT ' . $limit
        );
    }

    /**
     * Canonical code shape: AFS-YYYY-XXXX-XXXX
     *   - AFS prefix locks the brand in.
     *   - Year segment helps with quick eyeballing.
     *   - Two 4-char blocks of Crockford base32 (no 0/O/I/L/U confusables).
     */
    public static function generateCode(): string {
        $alphabet = '23456789ABCDEFGHJKMNPQRSTVWXYZ';
        $year = date('Y');
        // 8 random chars, guaranteed not to collide in practice (≈ 6×10¹¹ space).
        do {
            $b = '';
            for ($i = 0; $i < 8; $i++) {
                $b .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = sprintf('AFS-%s-%s-%s', $year, substr($b, 0, 4), substr($b, 4, 4));
        } while (self::findByCode($code) !== null);
        return $code;
    }

    public static function normaliseCode(string $code): string {
        // Accept lowercase, missing dashes, embedded spaces. Output canonical.
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $code));
        if (strlen($clean) === 15 && str_starts_with($clean, 'AFS')) {
            return sprintf('AFS-%s-%s-%s', substr($clean, 3, 4), substr($clean, 7, 4), substr($clean, 11, 4));
        }
        return strtoupper($code); // fall through; the equality check will fail and we'll show "not found"
    }
}
