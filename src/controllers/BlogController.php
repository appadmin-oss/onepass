<?php

class BlogController extends Controller {
    public function index(): void {
        $posts = Post::all();
        $categories = array_values(array_unique(array_map(fn($p) => $p['category'] ?? 'Editorial', $posts)));
        $this->view('pages/blog/index', [
            'title'        => 'Field notes · Afrostrength',
            'description'  => 'Project breakdowns, hiring notes, and the occasional studio essay.',
            'posts'        => $posts,
            'categories'   => $categories,
            'breadcrumbs'  => [
                ['label' => 'Home', 'href' => url('/')],
                ['label' => 'Field notes'],
            ],
            'needsSvgJs'   => empty($posts),
        ], 'main');
    }

    public function show(string $slug): void {
        $post = Post::find($slug);
        if (!$post) { $this->notFound(); return; }
        $related = array_slice(array_filter(Post::all(), fn($p) => $p['slug'] !== $slug), 0, 3);

        $this->view('pages/blog/detail', [
            'title'        => $post['title'] . ' · Field notes · Afrostrength',
            'description'  => $post['excerpt'] ?? AFS_DESC,
            'post'         => $post,
            'related'      => $related,
            'breadcrumbs'  => [
                ['label' => 'Home',        'href' => url('/')],
                ['label' => 'Field notes', 'href' => url('/blog')],
                ['label' => $post['title']],
            ],
            'jsonld' => [[
                '@context'      => 'https://schema.org',
                '@type'         => 'BlogPosting',
                'headline'      => $post['title'],
                'description'   => $post['excerpt'] ?? '',
                'author'        => ['@type' => 'Person', 'name' => $post['author'] ?? 'Afrostrength Studio'],
                'datePublished' => $post['published_at'] ?? $post['created_at'] ?? null,
                'publisher'     => ['@type' => 'Organization', 'name' => AFS_NAME],
            ]],
        ], 'main');
    }
}
