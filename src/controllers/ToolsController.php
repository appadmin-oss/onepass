<?php

/**
 * Free tools — promotional surfaces that draw prospects to the Academy.
 *
 * Each tool ships with a persistent Afrotech promo card so every visit
 * surfaces our offer. Three tools at launch:
 *   - Short link generator (localStorage-only; the shortened URL is
 *     created on the client and saved on the visitor's device)
 *   - QR code generator (uses api.qrserver.com — free public endpoint)
 *   - UTM builder (constructs a properly-encoded marketing URL)
 *
 * Every tool funnels back to /academy/apply via a sticky promo block.
 */
class ToolsController extends Controller {
    public function index(): void {
        $this->view('pages/tools/index', [
            'title'        => 'Free tools · Afrostrength',
            'description'  => 'Free tools to ship faster: short links, QR codes, UTM builders. Built by the Afrotech Academy team.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools'],
            ],
        ], 'main');
    }

    public function shortener(): void {
        $this->view('pages/tools/shortener', [
            'title'        => 'Free short-link generator · Afrostrength',
            'description'  => 'Make any URL short, memorable, and trackable. Free, no signup.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Short link'],
            ],
        ], 'main');
    }

    public function qr(): void {
        $this->view('pages/tools/qr', [
            'title'        => 'Free QR-code generator · Afrostrength',
            'description'  => 'Generate brand-coloured QR codes for any URL. Free, instant, no signup.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'QR generator'],
            ],
        ], 'main');
    }

    public function utm(): void {
        $this->view('pages/tools/utm', [
            'title'        => 'Free UTM builder · Afrostrength',
            'description'  => 'Build clean, trackable marketing URLs. Free, instant.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'UTM builder'],
            ],
        ], 'main');
    }

    public function careerPath(): void {
        $this->view('pages/tools/career-path', [
            'title'        => 'Free tech career-path quiz · Afrotech Academy',
            'description'  => 'Six questions, one personalised Afrotech Academy track. No login.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Career path quiz'],
            ],
        ], 'main');
    }

    public function salary(): void {
        $this->view('pages/tools/salary', [
            'title'        => 'Free NG tech salary calculator · Afrostrength',
            'description'  => 'See realistic salary bands for Nigerian tech roles by stack, level, and city.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Salary calc'],
            ],
        ], 'main');
    }

    public function palette(): void {
        $this->view('pages/tools/palette', [
            'title'        => 'Free palette extractor · Afrostrength',
            'description'  => 'Drop in an image, get a 6-colour brand palette with hex codes — runs entirely in your browser.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Palette extractor'],
            ],
        ], 'main');
    }

    public function contrast(): void {
        $this->view('pages/tools/contrast', [
            'title'        => 'WCAG colour-contrast checker · Afrostrength',
            'description'  => 'Paste any two colours and see whether the pair passes WCAG AA and AAA at body and large-text sizes.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Contrast checker'],
            ],
        ], 'main');
    }

    public function favicon(): void {
        $this->view('pages/tools/favicon', [
            'title'        => 'Favicon set generator · Afrostrength',
            'description'  => 'Upload a square image. We export the four sizes browsers and OSes actually use — 32, 180, 192 and 512 px — entirely in your browser.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Favicon generator'],
            ],
        ], 'main');
    }

    public function og(): void {
        $this->view('pages/tools/og', [
            'title'        => 'Open Graph card preview · Afrostrength',
            'description'  => 'Paste your title, description, image and URL. See exactly how the card renders on Twitter/X, LinkedIn, Facebook, Slack, Discord — side by side, in real time.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'OG preview'],
            ],
        ], 'main');
    }

    public function regex(): void {
        $this->view('pages/tools/regex', [
            'title'        => 'Regex tester · Afrostrength',
            'description'  => 'Test JavaScript regular expressions against sample text in real time. Captures, named groups, flag explanations — runs entirely in your browser.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Regex tester'],
            ],
        ], 'main');
    }

    public function jsonld(): void {
        $this->view('pages/tools/jsonld', [
            'title'        => 'Schema.org JSON-LD builder · Afrostrength',
            'description'  => 'Build well-formed schema.org JSON-LD for Article, Organization, Product, Event and BreadcrumbList. Drop into <head>, validates as you type.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'JSON-LD builder'],
            ],
        ], 'main');
    }

    public function yaml(): void {
        $this->view('pages/tools/yaml', [
            'title'        => 'JSON ↔ YAML converter · Afrostrength',
            'description'  => 'Convert between JSON and YAML in real time. Sample-paste, edit either side, copy out. Runs entirely in your browser.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'JSON ↔ YAML'],
            ],
        ], 'main');
    }

    public function diff(): void {
        $this->view('pages/tools/diff', [
            'title'        => 'Side-by-side diff · Afrostrength',
            'description'  => 'Paste two blocks of text. See what changed, line by line, character by character. No upload, no sign-in.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Diff'],
            ],
        ], 'main');
    }

    public function hash(): void {
        $this->view('pages/tools/hash', [
            'title'        => 'Hash generator · Afrostrength',
            'description'  => 'MD5, SHA-1, SHA-256, SHA-384, SHA-512 — computed locally via Web Crypto. Type, paste, or drop a file; never uploaded.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Hash generator'],
            ],
        ], 'main');
    }

    public function ipsum(): void {
        $this->view('pages/tools/ipsum', [
            'title'        => 'Afro-NG placeholder text · Afrostrength',
            'description'  => 'Brand-shaped placeholder copy. Names, business types, cities, headlines and paragraphs sampled from a curated Nigerian + pan-African corpus.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Placeholder text'],
            ],
        ], 'main');
    }

    /** POST /api/short-links — create a short link. */
    public function shortenerCreate(): void {
        Csrf::require();
        $url   = (string) $this->input('url', '');
        $alias = (string) $this->input('alias', '');
        $res = ShortLink::create($url, $alias);
        if (!$res['ok']) {
            $this->json(['error' => $res['error'], 'message' => $res['message'] ?? 'Could not create link.'], 422);
            return;
        }
        $short = rtrim(AFS_URL, '/') . '/s/' . $res['code'];
        $this->json([
            'ok'       => true,
            'code'     => $res['code'],
            'short'    => $short,
            'persisted'=> $res['persisted'] ?? true,
            'stats'    => $res['persisted'] ? url('/api/short-links/' . $res['code']) : null,
        ]);
    }

    /** GET /api/short-links/{code} — JSON stats. */
    public function shortenerStats(string $code): void {
        $row = ShortLink::stats(strtolower($code));
        if (!$row) { $this->json(['error' => 'not_found'], 404); return; }
        $this->json($row);
    }

    /**
     * GET /s/{code} — redirect handler with promotional interstitial.
     *
     * Default behaviour: render a fast interstitial that shows rotating
     * promotions (cohort drops, scholarships, etc.) for ~4 seconds, then
     * redirects. ?go=1 or a META-refresh / noscript fallback handles
     * users who skip / disable JS.
     */
    public function shortenerRedirect(string $code): void {
        $code = strtolower($code);
        $target = ShortLink::resolve($code);
        if (!$target) {
            http_response_code(404);
            $this->view('pages/tools/short-link-missing', [
                'title'       => 'Short link not found · Afrostrength',
                'description' => 'That short link does not exist or has been removed.',
                'code'        => $code,
                'breadcrumbs' => [
                    ['label' => 'Home',  'href' => url('/')],
                    ['label' => 'Tools', 'href' => url('/tools')],
                    ['label' => 'Not found'],
                ],
            ], 'main');
            return;
        }
        // Direct-redirect shortcut (used by mid-flow scripts).
        if (!empty($_GET['go'])) {
            header('Location: ' . $target, true, 302);
            exit;
        }
        $this->view('pages/tools/short-link-redirect', [
            'title'       => 'Redirecting · Afrostrength',
            'description' => 'You are being redirected through an Afrostrength short link.',
            'code'        => $code,
            'target'      => $target,
            'promos'      => Promotion::forPlacement('interstitial', 4),
            'breadcrumbs' => [],
        ], 'main');
    }

    public function slug(): void {
        $this->view('pages/tools/slug', [
            'title'        => 'Free slug + SEO meta preview · Afrostrength',
            'description'  => 'Turn any phrase into a clean URL slug and preview how it looks in Google search.',
            'breadcrumbs'  => [
                ['label' => 'Home',  'href' => url('/')],
                ['label' => 'Tools', 'href' => url('/tools')],
                ['label' => 'Slug · SEO preview'],
            ],
        ], 'main');
    }
}
