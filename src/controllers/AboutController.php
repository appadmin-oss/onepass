<?php

class AboutController extends Controller {
    public function index(): void      { $this->page('overview', 'Overview'); }
    public function overview(): void   { $this->page('overview', 'Overview'); }
    public function mission(): void    { $this->page('mission',  'Mission & Vision'); }
    public function team(): void       { $this->page('team',     'Team'); }
    public function methodology(): void{ $this->page('methodology', 'Methodology'); }

    private function page(string $slug, string $label): void {
        $operators = Instructor::all(); // reuse instructor seed for the team page
        $this->view('pages/about/' . $slug, [
            'title'        => $label . ' · About · Afrostrength',
            'description'  => 'Afrostrength — ' . $label,
            'operators'    => $operators,
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'About', 'href' => url('/about')],
                ['label' => $label],
            ],
        ], 'main');
    }
}
