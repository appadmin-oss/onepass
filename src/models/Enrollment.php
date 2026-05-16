<?php

class Enrollment {
    public static function create(array $data): int {
        if (!Database::available()) {
            $dir = AFS_ROOT . '/storage'; if (!is_dir($dir)) @mkdir($dir, 0775, true);
            @file_put_contents($dir . '/enrollments.log', json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
            return 0;
        }
        return (int) Database::insert(
            'INSERT INTO enrollments (name, email, phone, course_id, course_title, status) VALUES (?, ?, ?, ?, ?, "pending")',
            [
                $data['name']         ?? '',
                $data['email']        ?? '',
                $data['phone']        ?? null,
                isset($data['course_id']) && $data['course_id'] !== '' ? (int)$data['course_id'] : null,
                $data['course_title'] ?? null,
            ]
        );
    }

    public static function all(): array {
        if (!Database::available()) return [];
        return Database::all('SELECT * FROM enrollments ORDER BY created_at DESC');
    }
}
