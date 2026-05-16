<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// FREE TOOLS',
    'title'        => 'A toolkit, <em>on the studio.</em>',
    'sub'          => 'Sharp utilities for marketers, founders, designers and developers. Client-side. Free forever. No login. Made by the team behind Afrotech Academy.',
    'cta_label'    => 'Jump to the toolkit',
    'cta_href'     => '#toolkit',
    'illustration' => 'figure-laptop',
]]);

// Catalogue, grouped by audience. Each tool gets at least one tag so
// the live filter input can search by category as well as by name.
$catalogue = [
  'For marketers + writers' => [
    ['Short-link generator', 'Long URL in, clean memorable link out. Local history.',                                '/tools/short-link',  'arrow-out',  ['link','marketing','seo']],
    ['UTM builder',          'Source / medium / campaign tags, properly encoded.',                                  '/tools/utm',         'pulse',      ['analytics','utm','marketing']],
    ['Slug + SEO preview',   'Live Google snippet preview with length warnings.',                                   '/tools/slug',        'method',     ['seo','slug','title']],
    ['Open Graph preview',   'See exactly how your card unfurls on X, LinkedIn, Slack and Discord.',                '/tools/og',          'editorial',  ['social','og','twitter','linkedin']],
    ['JSON-LD builder',      'Article, Organization, Product, Event, BreadcrumbList — paste-ready &lt;script&gt; tag.', '/tools/jsonld',  'cert',       ['seo','schema','jsonld']],
    ['Afro-NG placeholder',  'Brand-shaped placeholder copy. Names, businesses, headlines.',                        '/tools/ipsum',       'editorial',  ['copy','placeholder','filler']],
  ],
  'For designers' => [
    ['Palette extractor',    'Drop an image, get six brand colours with hex codes.',                                '/tools/palette',     'brand',      ['color','palette','design']],
    ['Contrast checker',     'WCAG ratio on any two colours. AA, AAA, body, large.',                                '/tools/contrast',    'check',      ['contrast','wcag','accessibility']],
    ['Favicon set generator','One image in — every favicon size out (32 · 180 · 192 · 512).',                       '/tools/favicon',     'spark',      ['favicon','icon','pwa']],
    ['QR-code generator',    'Brand-coloured QR codes — flyers, decks, collateral.',                                '/tools/qr',          'deliver',    ['qr','print','design']],
  ],
  'For developers' => [
    ['Regex tester',         'Live-test JavaScript regexes. Named captures, flag explainer.',                       '/tools/regex',       'method',     ['regex','dev','javascript']],
    ['JSON ↔ YAML',          'Convert between JSON and YAML in real time.',                                         '/tools/yaml',        'stack',      ['json','yaml','dev','config']],
    ['Text diff',            'Side-by-side diff. Line + word + character modes.',                                   '/tools/diff',        'method',     ['diff','compare','text']],
    ['Hash generator',       'MD5 + SHA-1/256/384/512 via Web Crypto. File-drop supported.',                        '/tools/hash',        'spark',      ['hash','sha','md5','crypto']],
  ],
  'For learners' => [
    ['Career path quiz',     'Six honest questions. We match you to the Afrotech track that fits — now AI-mentored.','/tools/career-path', 'compass',    ['career','quiz','learn','ai']],
    ['NG tech salary calc',  'Real bands by role, stack, experience and city. Calibrated to 2026.',                 '/tools/salary',      'outcomes',   ['salary','nigeria','tech']],
  ],
];

// Flatten for live filtering JSON.
$flat = [];
foreach ($catalogue as $group => $tools) {
    foreach ($tools as $t) {
        $flat[] = [
            'title' => $t[0], 'desc' => $t[1], 'href' => $t[2],
            'icon'  => $t[3], 'tags' => $t[4], 'group' => $group,
        ];
    }
}
?>

<a id="toolkit"></a>

<section data-reveal class="tools-index">
  <header class="tools-index__head">
    <div>
      <h2 class="h2" style="margin:0 0 6px;"><?= count($flat) ?> tools, four audiences.</h2>
      <p class="body-m" style="margin:0;color:var(--text-mute);">All client-side. Your data never leaves your browser unless you choose to share.</p>
    </div>
    <div class="tools-index__search">
      <span aria-hidden="true"><?= icon('search', 16) ?></span>
      <input type="search" data-tools-filter placeholder="Filter — try 'seo' or 'json'…" aria-label="Filter tools">
    </div>
  </header>

  <?php foreach ($catalogue as $group => $tools): ?>
    <div class="tools-group" data-tools-group>
      <h3 class="tools-group__h"><?= e($group) ?></h3>
      <div class="tools-grid">
        <?php foreach ($tools as $t): ?>
          <a class="tool-card"
             href="<?= e(url($t[2])) ?>"
             data-tool-card
             data-tool-search="<?= e(strtolower($t[0] . ' ' . $t[1] . ' ' . implode(' ', $t[4]) . ' ' . $group)) ?>">
            <div class="tool-card__icon"><?= icon($t[3], 20) ?></div>
            <div>
              <div class="tool-card__title"><?= e($t[0]) ?></div>
              <p class="tool-card__desc"><?= $t[1] ?></p>
            </div>
            <div class="tool-card__meta">
              <span>// <?= e($t[4][0]) ?></span>
              <span>Open →</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <p class="tools-index__empty" data-tools-empty hidden>
    No tool matches that search. Try a broader term, or
    <a href="<?= e(url('/contact')) ?>">tell us what's missing</a>.
  </p>
</section>

<?php partial('promo-academy'); ?>
<?php partial('cta-final'); ?>

<script>
(function () {
  'use strict';
  const input = document.querySelector('[data-tools-filter]');
  const cards = document.querySelectorAll('[data-tool-card]');
  const groups = document.querySelectorAll('[data-tools-group]');
  const empty = document.querySelector('[data-tools-empty]');
  if (!input) return;
  function apply() {
    const q = input.value.trim().toLowerCase();
    let total = 0;
    cards.forEach(c => {
      const match = !q || c.dataset.toolSearch.includes(q);
      c.hidden = !match;
      if (match) total++;
    });
    // Hide groups that have no visible card.
    groups.forEach(g => {
      const any = [...g.querySelectorAll('[data-tool-card]')].some(c => !c.hidden);
      g.hidden = !any;
    });
    if (empty) empty.hidden = total > 0;
  }
  input.addEventListener('input', apply);
  // ?q=... deep-link
  const url = new URL(location.href);
  if (url.searchParams.has('q')) { input.value = url.searchParams.get('q'); apply(); }
})();
</script>
