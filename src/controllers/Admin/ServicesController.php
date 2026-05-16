<?php

namespace Admin;

use Auth, Csrf, Database, Service;

class ServicesController extends \Controller {
    public function index(): void {
        Auth::require();
        $this->view('admin/services/index', [
            'title'    => 'Services · Admin',
            'services' => Service::all(),
        ], 'admin');
    }

    public function edit(string $id): void {
        Auth::require();
        $service = Database::one('SELECT * FROM services WHERE id = ?', [(int)$id]);
        if (!$service) { $this->notFound(); return; }
        $this->view('admin/services/edit', [
            'title'   => 'Edit · ' . ($service['name'] ?? 'Service'),
            'service' => $service,
        ], 'admin');
    }

    public function save(): void {
        Auth::require(); Csrf::require();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { $this->redirect('/admin/services'); return; }
        Database::exec(
            'UPDATE services SET name=?, tagline=?, overview=?, who_for=?, timeline=?, tag=? WHERE id=?',
            [
                trim($_POST['name'] ?? ''),
                trim($_POST['tagline'] ?? ''),
                $_POST['overview'] ?? '',
                $_POST['who_for'] ?? '',
                trim($_POST['timeline'] ?? '4–6 WEEKS'),
                trim($_POST['tag'] ?? 'CORE'),
                $id,
            ]
        );
        flash_set('admin_ok', 'Service updated.');
        $this->redirect('/admin/services');
    }
}
