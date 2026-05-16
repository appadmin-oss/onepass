<?php

class ServicesController extends Controller {
    public function index(): void {
        $this->view('pages/services/index', [
            'title'        => 'Services · Afrostrength',
            'description'  => 'Six capabilities. One studio. Brand, design, media, project & event, digital, and training.',
            'services'     => Service::all(),
            'breadcrumbs'  => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'Services'],
            ],
        ], 'main');
    }

    public function show(string $slug): void {
        $service = Service::find($slug);
        if (!$service) { $this->notFound(); return; }

        $this->view('pages/services/detail', [
            'title'        => $service['name'] . ' · Afrostrength',
            'description'  => $service['tagline'] ?? AFS_DESC,
            'service'      => $service,
            'related'      => array_filter(Service::all(), fn($s) => $s['slug'] !== $slug),
            'projects'     => array_slice(Project::all(), 0, 3),
            'breadcrumbs'  => [
                ['label' => 'Home',     'href' => url('/')],
                ['label' => 'Services', 'href' => url('/services')],
                ['label' => $service['name']],
            ],
            'jsonld'       => [[
                '@context' => 'https://schema.org',
                '@type'    => 'Service',
                'name'     => $service['name'],
                'description' => $service['tagline'] ?? '',
                'provider' => ['@type' => 'Organization', 'name' => AFS_NAME],
            ]],
        ], 'main');
    }
}
