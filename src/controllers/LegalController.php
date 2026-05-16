<?php

class LegalController extends Controller {
    public function privacy(): void {
        $this->view('pages/legal/privacy', [
            'title'       => 'Privacy Policy · Afrostrength',
            'description' => 'How Afrostrength Limited collects, processes, stores and protects your personal data. NDPR + GDPR aligned.',
            'breadcrumbs' => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Legal', 'href' => url('/legal/privacy')],
                ['label' => 'Privacy Policy'],
            ],
        ], 'main');
    }

    public function terms(): void {
        $this->view('pages/legal/terms', [
            'title'       => 'Terms of Service · Afrostrength',
            'description' => 'The terms governing your use of afrostrength.com and Afrostrength services.',
            'breadcrumbs' => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Legal', 'href' => url('/legal/privacy')],
                ['label' => 'Terms of Service'],
            ],
        ], 'main');
    }

    public function cookies(): void {
        $this->view('pages/legal/cookies', [
            'title'       => 'Cookie Policy · Afrostrength',
            'description' => 'How Afrostrength uses cookies and similar technologies on afrostrength.com.',
            'breadcrumbs' => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Legal', 'href' => url('/legal/privacy')],
                ['label' => 'Cookie Policy'],
            ],
        ], 'main');
    }

    public function acceptableUse(): void {
        $this->view('pages/legal/acceptable-use', [
            'title'       => 'Acceptable Use Policy · Afrostrength',
            'description' => 'How you may and may not use Afrostrength services, tools, and the Afrotech Academy platform.',
            'breadcrumbs' => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Legal', 'href' => url('/legal/privacy')],
                ['label' => 'Acceptable Use'],
            ],
        ], 'main');
    }

    public function imprint(): void {
        $this->view('pages/legal/imprint', [
            'title'       => 'Imprint · Afrostrength',
            'description' => 'Company identification, registered office, and statutory disclosures for Afrostrength Limited.',
            'breadcrumbs' => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Legal', 'href' => url('/legal/privacy')],
                ['label' => 'Imprint'],
            ],
        ], 'main');
    }
}
