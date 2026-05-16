<?php

namespace Admin;

use Auth, Csrf, Database, Project;

class ProjectsController extends \Controller {
    public function index(): void {
        Auth::require();
        $this->view('admin/projects/index', [
            'title'    => 'Projects · Admin',
            'projects' => Project::all(false),
        ], 'admin');
    }

    public function edit(?string $id = null): void {
        Auth::require();
        $project = $id ? Database::one('SELECT * FROM projects WHERE id = ?', [(int)$id]) : null;
        $this->view('admin/projects/edit', [
            'title'   => ($project ? 'Edit' : 'New') . ' · Project',
            'project' => $project,
        ], 'admin');
    }

    public function save(): void {
        Auth::require(); Csrf::require();
        $id    = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug  = trim($_POST['slug']  ?? '') ?: slugify($title);
        $cols  = ['client','category','tags','summary','problem','solution','outcome','status_label','status'];
        $vals  = [];
        foreach ($cols as $c) $vals[$c] = $_POST[$c] ?? null;
        $year  = (int)($_POST['year'] ?? date('Y'));
        $featured = !empty($_POST['is_featured']) ? 1 : 0;

        if ($id) {
            Database::exec(
                'UPDATE projects SET title=?, slug=?, client=?, year=?, category=?, tags=?, summary=?, problem=?, solution=?, outcome=?, status_label=?, status=?, is_featured=? WHERE id=?',
                [$title, $slug, $vals['client'], $year, $vals['category'], $vals['tags'], $vals['summary'], $vals['problem'], $vals['solution'], $vals['outcome'], $vals['status_label'] ?: 'case', $vals['status'] ?: 'draft', $featured, $id]
            );
        } else {
            Database::insert(
                'INSERT INTO projects (title, slug, client, year, category, tags, summary, problem, solution, outcome, status_label, status, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [$title, $slug, $vals['client'], $year, $vals['category'], $vals['tags'], $vals['summary'], $vals['problem'], $vals['solution'], $vals['outcome'], $vals['status_label'] ?: 'case', $vals['status'] ?: 'draft', $featured]
            );
        }
        flash_set('admin_ok', 'Saved.');
        $this->redirect('/admin/projects');
    }

    public function delete(string $id): void {
        Auth::require(); Csrf::require();
        Database::exec('DELETE FROM projects WHERE id = ?', [(int)$id]);
        $this->redirect('/admin/projects');
    }
}
