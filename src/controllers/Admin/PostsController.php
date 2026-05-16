<?php

namespace Admin;

use Auth, Csrf, Database, Post;

class PostsController extends \Controller {
    public function index(): void {
        Auth::require();
        $this->view('admin/posts/index', [
            'title' => 'Field notes · Admin',
            'posts' => Post::all(false),
        ], 'admin');
    }

    public function edit(?string $id = null): void {
        Auth::require();
        $post = $id ? Database::one('SELECT * FROM posts WHERE id = ?', [(int)$id]) : null;
        $this->view('admin/posts/edit', [
            'title' => ($post ? 'Edit' : 'New') . ' · Field notes',
            'post'  => $post,
        ], 'admin');
    }

    public function save(): void {
        Auth::require(); Csrf::require();
        $id      = (int)($_POST['id'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $slug    = trim($_POST['slug']  ?? '') ?: slugify($title);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $body    = $_POST['body'] ?? '';
        $cat     = trim($_POST['category'] ?? 'Editorial');
        $author  = trim($_POST['author'] ?? 'Afrostrength Studio');
        $status  = in_array($_POST['status'] ?? 'draft', ['draft','published'], true) ? $_POST['status'] : 'draft';
        $published = $status === 'published' ? date('Y-m-d H:i:s') : null;

        if ($id) {
            Database::exec(
                'UPDATE posts SET title=?, slug=?, excerpt=?, body=?, category=?, author=?, status=?, published_at=COALESCE(?, published_at) WHERE id=?',
                [$title, $slug, $excerpt, $body, $cat, $author, $status, $published, $id]
            );
        } else {
            Database::insert(
                'INSERT INTO posts (title, slug, excerpt, body, category, author, status, published_at) VALUES (?,?,?,?,?,?,?,?)',
                [$title, $slug, $excerpt, $body, $cat, $author, $status, $published]
            );
        }
        flash_set('admin_ok', 'Saved.');
        $this->redirect('/admin/posts');
    }

    public function delete(string $id): void {
        Auth::require(); Csrf::require();
        Database::exec('DELETE FROM posts WHERE id = ?', [(int)$id]);
        $this->redirect('/admin/posts');
    }
}
