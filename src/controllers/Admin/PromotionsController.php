<?php

namespace Admin;

use Auth, Csrf, Promotion, Database;

class PromotionsController extends \Controller {
    public function index(): void {
        Auth::require();
        $this->view('admin/promotions/index', [
            'title' => 'Promotions · Admin',
            'rows'  => Promotion::listAll(),
        ], 'admin');
    }

    public function edit(?string $id = null): void {
        Auth::require();
        $row = $id ? Promotion::find((int)$id) : null;
        $this->view('admin/promotions/edit', [
            'title' => ($row ? 'Edit' : 'New') . ' · Promotion',
            'row'   => $row,
        ], 'admin');
    }

    public function save(): void {
        Auth::require(); Csrf::require();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'slug'        => trim($_POST['slug'] ?? ''),
            'kind'        => $_POST['kind']  ?? 'banner',
            'eyebrow'     => trim($_POST['eyebrow'] ?? ''),
            'title'       => trim($_POST['title'] ?? ''),
            'subtitle'    => trim($_POST['subtitle'] ?? ''),
            'body'        => $_POST['body'] ?? '',
            'image_url'   => trim($_POST['image_url'] ?? ''),
            'tone'        => $_POST['tone'] ?? 'crimson',
            'badge'       => trim($_POST['badge'] ?? ''),
            'cta_label'   => trim($_POST['cta_label'] ?? ''),
            'cta_href'    => trim($_POST['cta_href'] ?? ''),
            'placements'  => trim($_POST['placements'] ?? 'interstitial'),
            'starts_at'   => $_POST['starts_at'] ?: null,
            'ends_at'     => $_POST['ends_at']   ?: null,
            'sort'        => (int)($_POST['sort'] ?? 0),
            'status'      => $_POST['status'] ?? 'draft',
        ];
        Promotion::save($data, $id ?: null);
        flash_set('admin_ok', 'Promotion saved.');
        $this->redirect('/admin/promotions');
    }

    public function delete(string $id): void {
        Auth::require(); Csrf::require();
        Promotion::delete((int)$id);
        $this->redirect('/admin/promotions');
    }

    /**
     * Inline image upload — saves to /assets/images/promo/ and returns
     * the URL as JSON so the form can drop it into image_url.
     */
    public function upload(): void {
        Auth::require(); Csrf::require();
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'upload_failed'], 400); return;
        }
        $f = $_FILES['file'];
        if ($f['size'] > 8 * 1024 * 1024) { $this->json(['error' => 'too_large'], 413); return; }
        $info = getimagesize($f['tmp_name']);
        $allow = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/svg+xml' => 'svg'];
        if (!$info || empty($allow[$info['mime']])) { $this->json(['error' => 'bad_type'], 415); return; }
        $ext = $allow[$info['mime']];
        $dir = AFS_ROOT . '/assets/images/promo';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $name = 'promo-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!@move_uploaded_file($f['tmp_name'], $dest)) {
            $this->json(['error' => 'write_failed'], 500); return;
        }
        $this->json([
            'ok'  => true,
            'url' => '/assets/images/promo/' . $name,
        ]);
    }
}
