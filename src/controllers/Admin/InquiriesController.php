<?php

namespace Admin;

use Auth, Csrf, Inquiry;

class InquiriesController extends \Controller {
    public function index(): void {
        Auth::require();
        $this->view('admin/inquiries/index', [
            'title'     => 'Inquiries · Admin',
            'inquiries' => Inquiry::all(),
        ], 'admin');
    }

    public function show(string $id): void {
        Auth::require();
        $inquiry = Inquiry::find((int)$id);
        if (!$inquiry) { $this->notFound(); return; }
        $this->view('admin/inquiries/show', [
            'title'   => 'Inquiry · Admin',
            'inquiry' => $inquiry,
        ], 'admin');
    }

    public function updateStatus(string $id): void {
        Auth::require(); Csrf::require();
        $status = $_POST['status'] ?? 'open';
        if (!in_array($status, ['new', 'open', 'resolved'], true)) $status = 'open';
        Inquiry::setStatus((int)$id, $status);
        $this->redirect('/admin/inquiries/' . (int)$id);
    }

    public function acceptRoute(string $id): void {
        Auth::require(); Csrf::require();
        $inquiry = Inquiry::find((int)$id);
        if (!$inquiry) { $this->notFound(); return; }
        $route = $_POST['route'] ?? ($inquiry['suggested_route'] ?? '');
        if (!in_array($route, Inquiry::ROUTES, true)) {
            $this->redirect('/admin/inquiries/' . (int)$id);
            return;
        }
        Inquiry::setAssignedTo((int)$id, $route);
        $back = $_POST['back'] ?? '';
        $this->redirect($back === 'inbox' ? '/admin/inquiries' : '/admin/inquiries/' . (int)$id);
    }

    public function routeNow(string $id): void {
        Auth::require(); Csrf::require();
        Inquiry::routeWithAi((int)$id);
        $this->redirect('/admin/inquiries/' . (int)$id);
    }
}
