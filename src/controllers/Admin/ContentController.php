<?php

namespace Admin;

use Auth, Csrf, ContentBlock;

class ContentController extends \Controller {
    private array $keys = [
        'hero.eyebrow'       => 'Hero · Eyebrow',
        'hero.title'         => 'Hero · Title (use {{accent}}…{{/accent}} for crimson italic)',
        'hero.sub'           => 'Hero · Subtext',
        'hero.cta_primary'   => 'Hero · Primary CTA label',
        'hero.cta_secondary' => 'Hero · Secondary CTA label',
        'final.cta_title'    => 'Final CTA · Title',
        'final.trust'        => 'Final CTA · Trust line',
    ];

    public function index(): void {
        Auth::require();
        $blocks = [];
        foreach ($this->keys as $k => $_) $blocks[$k] = ContentBlock::get($k, '');
        $this->view('admin/content-blocks', [
            'title'  => 'Content blocks · Admin',
            'keys'   => $this->keys,
            'blocks' => $blocks,
        ], 'admin');
    }

    public function save(): void {
        Auth::require(); Csrf::require();
        foreach ($this->keys as $k => $_) {
            if (array_key_exists($k, $_POST)) ContentBlock::set($k, (string)$_POST[$k]);
        }
        flash_set('admin_ok', 'Saved.');
        $this->redirect('/admin/content');
    }
}
