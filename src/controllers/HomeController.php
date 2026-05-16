<?php

class HomeController extends Controller {
    public function index(): void {
        $this->view('pages/home', [
            'title'           => 'Afrostrength — Building Brands, Strengthening Legacies',
            'description'     => AFS_DESC,
            'services'        => Service::all(),
            'projects'        => Project::featured(3),
            'testimonials'    => Testimonial::featured(3),
            'posts'           => Post::recent(3),
            'needsSvgJs'      => true,
            'needsGraphicsJs' => true,
        ], 'main');
    }

    public function sitemap(): void {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $urls = ['/', '/about', '/services', '/projects', '/blog', '/contact', '/faq', '/testimonials',
                 '/academy', '/academy/courses', '/academy/instructors', '/academy/certifications'];
        foreach (Service::all()       as $s) $urls[] = '/services/' . $s['slug'];
        foreach (Project::all()       as $p) $urls[] = '/projects/' . $p['slug'];
        foreach (Post::all()          as $p) $urls[] = '/blog/' . $p['slug'];
        foreach (Course::all()        as $c) $urls[] = '/academy/courses/' . $c['slug'];
        foreach (array_unique($urls) as $u) {
            echo '<url><loc>' . htmlspecialchars(url($u)) . '</loc><changefreq>weekly</changefreq></url>' . "\n";
        }
        echo '</urlset>';
        exit;
    }

    public function robots(): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: " . url('/sitemap.xml') . "\n";
        exit;
    }
}
