<?php

namespace Admin;

use Auth, Csrf, Database, Lms;

/**
 * Combined admin surface for the academy entities. Section is given by URL
 * (/admin/academy/{section}). Supports courses, instructors, certifications,
 * enrollments — each as a list view with quick-edit/delete in one place.
 */
class AcademyController extends \Controller {
    public function index(string $section): void {
        Auth::require();
        $section = in_array($section, ['courses','instructors','certifications','enrollments'], true) ? $section : 'courses';
        $rows = [];
        if (Database::available()) {
            try {
                $rows = match ($section) {
                    'courses'        => Database::all('SELECT * FROM courses ORDER BY id'),
                    'instructors'    => Database::all('SELECT * FROM instructors ORDER BY sort, id'),
                    'certifications' => Database::all('SELECT * FROM certifications ORDER BY id'),
                    'enrollments'    => Database::all('SELECT * FROM enrollments ORDER BY created_at DESC'),
                };
            } catch (\Throwable $e) { $rows = []; }
        }
        $this->view('admin/academy/section', [
            'title'      => 'Academy · ' . ucfirst($section),
            'section'    => $section,
            'rows'       => $rows,
            'lmsEnabled' => Lms::enabled(),
        ], 'admin');
    }

    public function save(): void {
        Auth::require(); Csrf::require();
        $section = $_POST['section'] ?? '';
        $id      = (int)($_POST['id'] ?? 0);

        if ($section === 'courses') {
            $bind = [
                trim($_POST['title'] ?? ''),
                trim($_POST['slug']  ?? '') ?: slugify($_POST['title'] ?? ''),
                trim($_POST['level'] ?? 'Foundations'),
                (int)($_POST['weeks'] ?? 6),
                trim($_POST['instructor'] ?? ''),
                (int)($_POST['price_naira'] ?? 0),
                trim($_POST['summary'] ?? ''),
                $_POST['body'] ?? '',
                in_array($_POST['status'] ?? 'draft', ['draft','published'], true) ? $_POST['status'] : 'draft',
            ];
            if ($id) {
                Database::exec('UPDATE courses SET title=?, slug=?, level=?, weeks=?, instructor=?, price_naira=?, summary=?, body=?, status=? WHERE id=?', array_merge($bind, [$id]));
            } else {
                Database::insert('INSERT INTO courses (title, slug, level, weeks, instructor, price_naira, summary, body, status) VALUES (?,?,?,?,?,?,?,?,?)', $bind);
            }
        } elseif ($section === 'instructors') {
            $bind = [
                trim($_POST['name'] ?? ''),
                trim($_POST['slug'] ?? '') ?: slugify($_POST['name'] ?? ''),
                trim($_POST['role'] ?? ''),
                $_POST['bio'] ?? '',
                trim($_POST['skills'] ?? ''),
                trim($_POST['status_pill'] ?? 'live'),
                trim($_POST['status_label'] ?? 'Available'),
                (int)($_POST['sort'] ?? 0),
            ];
            if ($id) {
                Database::exec('UPDATE instructors SET name=?, slug=?, role=?, bio=?, skills=?, status_pill=?, status_label=?, sort=? WHERE id=?', array_merge($bind, [$id]));
            } else {
                Database::insert('INSERT INTO instructors (name, slug, role, bio, skills, status_pill, status_label, sort) VALUES (?,?,?,?,?,?,?,?)', $bind);
            }
        } elseif ($section === 'certifications') {
            $bind = [
                trim($_POST['title'] ?? ''),
                trim($_POST['slug'] ?? '') ?: slugify($_POST['title'] ?? ''),
                trim($_POST['summary'] ?? ''),
            ];
            if ($id) {
                Database::exec('UPDATE certifications SET title=?, slug=?, summary=? WHERE id=?', array_merge($bind, [$id]));
            } else {
                Database::insert('INSERT INTO certifications (title, slug, summary) VALUES (?,?,?)', $bind);
            }
        } elseif ($section === 'enrollments') {
            // status updates only
            if ($id) {
                $status = in_array($_POST['status'] ?? 'pending', ['pending','confirmed','cancelled'], true) ? $_POST['status'] : 'pending';
                Database::exec('UPDATE enrollments SET status=? WHERE id=?', [$status, $id]);
            }
        }
        flash_set('admin_ok', 'Saved.');
        $this->redirect('/admin/academy/' . $section);
    }
}
