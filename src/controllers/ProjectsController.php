<?php

class ProjectsController extends Controller {
    public function index(): void {
        $projects = Project::all();
        $this->view('pages/projects/index', [
            'title'        => 'Projects · Afrostrength',
            'description'  => 'Brand, identity, media, and digital projects shipped from our Lagos studios.',
            'projects'     => $projects,
            'breadcrumbs'  => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'Projects'],
            ],
            'needsSvgJs'   => empty($projects),  // empty-state graphic uses SVG.js
        ], 'main');
    }

    public function show(string $slug): void {
        $project = Project::find($slug);
        if (!$project) { $this->notFound(); return; }

        $this->view('pages/projects/detail', [
            'title'        => $project['title'] . ' · Afrostrength',
            'description'  => $project['summary'] ?? AFS_DESC,
            'project'      => $project,
            'related'      => Project::related($slug, 3),
            'breadcrumbs'  => [
                ['label' => 'Home',     'href' => url('/')],
                ['label' => 'Projects', 'href' => url('/projects')],
                ['label' => $project['title']],
            ],
            'jsonld'       => [[
                '@context'  => 'https://schema.org',
                '@type'     => 'CreativeWork',
                'name'      => $project['title'],
                'creator'   => ['@type' => 'Organization', 'name' => AFS_NAME],
                'description'=> $project['summary'] ?? '',
            ]],
        ], 'main');
    }
}
