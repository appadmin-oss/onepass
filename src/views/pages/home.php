<?php
/**
 * @var array $services
 * @var array $projects
 * @var array $testimonials
 * @var array $posts
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';

$serviceMeta = [
    'brand-development'         => ['variant' => 'feature',  'illust' => 'abstract-orbs'],
    'creative-design'           => ['variant' => 'peach',    'illust' => null],
    'media-solutions'           => ['variant' => 'ink',      'illust' => null],
    'project-event-management'  => ['variant' => 'standard', 'illust' => null],
    'digital-solutions'         => ['variant' => 'standard', 'illust' => null],
    'client-training'           => ['variant' => 'small',    'illust' => null],
];
?>

<section class="home-hero" data-reveal>
  <div style="position:relative;z-index:2;">
    <h1 class="home-hero__title">
      <?= ContentBlock::html('hero.title', 'Building brands. {{accent}}Strengthening legacies.{{/accent}}') ?>
    </h1>
    <p class="home-hero__sub">
      <?= e(ContentBlock::get('hero.sub')) ?>
    </p>
    <div class="home-hero__ctas">
      <a href="<?= e(url('/contact')) ?>" class="btn btn-primary"><?= e(ContentBlock::get('hero.cta_primary', 'Start a brief')) ?> <?= icon_chev() ?></a>
      <a href="<?= e(url('/projects')) ?>" class="btn btn-ghost"><?= e(ContentBlock::get('hero.cta_secondary', 'See the work')) ?></a>
    </div>
  </div>

  <div class="home-hero__art" aria-hidden="true">
    <?php partial('cutout', [
      'src'     => '/assets/images/cutouts/hero.webp',
      'alt'     => '',
      'pattern' => 'sahel-contour',
      'aspect'  => 'portrait',
      'tone'    => 'bone',
    ]); ?>
  </div>
</section>

<section id="services" aria-labelledby="services-h">
  <?php partial('section-header', [
    'eyebrow' => 'Services',
    'title'   => 'What we do.',
    'lead'    => 'Strategy, identity, media, project execution, digital, and training. Senior teams against tight briefs.',
    'cta'     => ['label' => 'All services', 'href' => url('/services')],
  ]); ?>

  <div class="bento" data-stagger="80" data-reveal>
    <?php
    $i = 1; $tot = count($services);
    foreach ($services as $svc):
      $meta = $serviceMeta[$svc['slug']] ?? ['variant' => 'standard', 'illust' => null];
      $cellCls = 'bento-cell bento-cell--' . $meta['variant'];
      $iconName = $svc['icon'] ?? 'brand';
    ?>
      <a class="<?= e($cellCls) ?>" href="<?= e(url('/services/' . $svc['slug'])) ?>" data-reveal>
        <div class="bento-cell__head">
          <span class="bento-cell__icon"><?= icon($iconName, 20) ?></span>
        </div>
        <div>
          <h3 class="bento-cell__title"><?= e($svc['name']) ?></h3>
          <p class="bento-cell__desc"><?= e($svc['tagline'] ?? '') ?></p>
        </div>
        <div class="bento-cell__foot">
          <span><?= e($svc['timeline'] ?? '4–6 weeks') ?></span>
          <span class="bento-cell__arrow"><?= icon('arrow-out', 16) ?></span>
        </div>
        <?php if ($meta['variant'] === 'feature'): ?>
          <div class="bento-cell__art"><?php illustration('abstract-orbs'); ?></div>
        <?php endif; ?>
      </a>
    <?php $i++; endforeach; ?>
  </div>
</section>

<section id="work" aria-labelledby="work-h">
  <?php partial('section-header', [
    'eyebrow' => 'Selected work',
    'title'   => 'Recent projects.',
    'lead'    => 'Brands you can name. Outcomes you can measure.',
    'cta'     => ['label' => 'All projects', 'href' => url('/projects')],
  ]); ?>

  <div class="project-rows">
    <?php $i = 1; foreach ($projects as $project):
      $project['index'] = $i++;
      partial('project-row', ['project' => $project]);
    endforeach; ?>
  </div>
</section>

<section id="method" class="hero-dark home-process" data-reveal aria-labelledby="method-h">
  <div class="home-process__inner">
    <header class="home-process__head">
      <h2 id="method-h" class="home-process__title">
        Strategy in week one. Deliverables by week six.
      </h2>
    </header>

    <div data-stagger="100" class="process-grid--dark" style="grid-template-columns:repeat(5,1fr);">
      <?php
      $steps = [
        ['title' => 'Discover',           'desc' => 'We listen first — goals, audience, competitors, and where you want the brand to land.'],
        ['title' => 'Strategise',         'desc' => 'A clear creative and technical roadmap. Deliverables, timelines, and success metrics agreed upfront.'],
        ['title' => 'Create',             'desc' => 'A senior team executes: design built to inspire, code built to perform, production built to captivate.'],
        ['title' => 'Refine',             'desc' => 'Tight feedback loops. We revise until it\'s right — not just acceptable.'],
        ['title' => 'Deliver',            'desc' => 'On-time handoff with post-launch support. The relationship doesn\'t end at delivery.'],
      ];
      foreach ($steps as $step): ?>
        <div data-reveal style="background:#0F0F0F;padding:28px 22px;color:#fff;display:flex;flex-direction:column;justify-content:space-between;min-height:220px;">
          <div>
            <h3 class="ff-display" style="font-weight:600;font-size:20px;line-height:1.2;letter-spacing:-0.02em;margin:0 0 10px;"><?= e($step['title']) ?></h3>
            <p style="font-size:13px;line-height:1.6;color:rgba(255,255,255,0.7);margin:0;"><?= e($step['desc']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php $primary = $testimonials[0] ?? null; if ($primary): ?>
<section id="testimonials" data-reveal aria-labelledby="testimonials-h">
  <?php partial('section-header', [
    'eyebrow' => 'In their words',
    'title'   => 'What clients say.',
    'cta'     => ['label' => 'More testimonials', 'href' => url('/testimonials')],
  ]); ?>

  <div class="feature-quote feature-quote--quiet" data-reveal>
    <blockquote class="feature-quote__body"><?= $primary['quote_html'] ?></blockquote>
    <div class="feature-quote__byline">
      <?= e($primary['client_name']) ?> · <?= e($primary['role'] ?? '') ?> · <?= e($primary['company'] ?? '') ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section id="field-notes" aria-labelledby="field-notes-h">
  <?php partial('section-header', [
    'eyebrow' => 'Field notes',
    'title'   => 'Writing from the studio.',
    'lead'    => 'One letter a month. Project breakdowns, hiring notes, and the occasional studio essay.',
    'cta'     => ['label' => 'All notes', 'href' => url('/blog')],
  ]); ?>
  <div data-stagger="80" class="grid-cols-3">
    <?php foreach ($posts as $post) partial('blog-card', ['post' => $post]); ?>
  </div>
</section>

<?php partial('cert-panel'); ?>

<?php
$homePromos = Promotion::forPlacement('home', 2);
if (!empty($homePromos)): ?>
<section id="whats-on" data-reveal aria-labelledby="whats-on-h">
  <?php partial('section-header', [
    'eyebrow' => 'What\'s on',
    'title'   => 'Currently open.',
    'lead'    => 'Live cohorts, scholarships, and events. Curated by the studio.',
  ]); ?>
  <div class="grid-cols-2" style="gap:16px;">
    <?php foreach ($homePromos as $p):
      $tone = $p['tone'] ?? 'crimson';
    ?>
      <a href="<?= e($p['cta_href'] ?? '#') ?>" class="promo-card promo-card--<?= e($tone) ?>" data-reveal>
        <?php if (!empty($p['image_url'])): ?>
          <div class="promo-card__media" style="background-image:url('<?= e($p['image_url']) ?>');"></div>
        <?php endif; ?>
        <div class="promo-card__body">
          <?php if (!empty($p['badge'])): ?>
            <span class="promo-card__badge"><?= e($p['badge']) ?></span>
          <?php endif; ?>
          <h3 class="promo-card__title"><?= e($p['title']) ?></h3>
          <?php if (!empty($p['subtitle'])): ?>
            <p class="promo-card__sub"><?= e($p['subtitle']) ?></p>
          <?php endif; ?>
          <span class="promo-card__cta"><?= e($p['cta_label'] ?? 'Open →') ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php partial('cta-final'); ?>
