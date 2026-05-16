<?php

class TestimonialsController extends Controller {
    public function index(): void {
        $this->view('pages/testimonials', [
            'title'        => 'Testimonials · Afrostrength',
            'description'  => 'In their words. Founders, MDs, and innovation leads on what shipping with the studio felt like.',
            'testimonials' => Testimonial::all(),
            'breadcrumbs'  => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'Testimonials'],
            ],
        ], 'main');
    }
}
