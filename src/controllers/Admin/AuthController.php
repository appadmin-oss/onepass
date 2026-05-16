<?php

namespace Admin;

use Auth, Csrf, Validator;

class AuthController extends \Controller {
    public function showLogin(): void {
        if (Auth::check()) { $this->redirect('/admin'); return; }
        $this->view('admin/login', ['title' => 'Sign in · Afrostrength admin'], 'admin');
    }

    public function login(): void {
        Csrf::require();
        $u = $this->input('username');
        $p = $this->input('password');
        if (!Auth::attempt($u, $p)) {
            flash_set('admin_error', 'Wrong username or password.');
            $this->redirect('/admin/login');
            return;
        }
        $this->redirect('/admin');
    }

    public function logout(): void {
        Csrf::require();
        Auth::logout();
        $this->redirect('/admin/login');
    }
}
