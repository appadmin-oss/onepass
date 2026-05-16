<?php

namespace Admin;

use Auth, Inquiry, Post, Project, Course;

class DashboardController extends \Controller {
    public function index(): void {
        Auth::require();
        $this->view('admin/dashboard', [
            'title'        => 'Dashboard · Afrostrength admin',
            'inquiries'    => Inquiry::counts(),
            'recent'       => array_slice(Inquiry::all(), 0, 6),
            'postCount'    => count(Post::all(false)),
            'projectCount' => count(Project::all(false)),
            'courseCount'  => count(Course::all(false)),
        ], 'admin');
    }
}
