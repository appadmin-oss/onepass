<?php
/** @var array $project
 *  @var array $related
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
$gallery = $project['gallery'] ?? [];
?>

<!-- Header -->
<header data-reveal style="padding:32px 0 56px;border-bottom:1px solid var(--hairline);">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.14em;color:rgba(10,10,10,0.6);text-transform:uppercase;">
    <?= e($project['category'] ?? '') ?>
  </div>
  <h1 class="h-display-2" style="margin:16px 0 24px;max-width:22ch;">
    <?= e($project['title']) ?>
  </h1>
  <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:center;font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.7);">
    <div><span style="color:rgba(10,10,10,0.45);">Client</span> &nbsp; <?= e($project['client'] ?? '') ?></div>
    <div><span style="color:rgba(10,10,10,0.45);">Year</span> &nbsp; <?= e((string)($project['year'] ?? '')) ?></div>
    <div><span style="color:rgba(10,10,10,0.45);">Tags</span> &nbsp; <?= e($project['tags'] ?? '') ?></div>
  </div>
</header>

<!-- Big visual placeholder -->
<section data-reveal style="margin:48px 0;">
  <div class="placeholder" style="aspect-ratio:21/9;width:100%;font-size:13px;border-radius:18px;">
    // <?= e(strtoupper($project['title'])) ?> · KEY VISUAL
  </div>
</section>

<!-- Overview -->
<section data-reveal style="padding:48px 0;border-top:1px solid var(--hairline);">
  <div class="eyebrow">OVERVIEW</div>
  <p class="body-l" style="margin-top:16px;max-width:70ch;color:rgba(10,10,10,0.85);">
    <?= e($project['summary'] ?? '') ?>
  </p>
</section>

<!-- Problem -->
<section data-reveal class="grid-split-1-2" style="padding:48px 0;border-top:1px solid var(--hairline);">
  <div class="eyebrow">THE PROBLEM</div>
  <p class="body-l" style="max-width:62ch;color:rgba(10,10,10,0.85);">
    <?= e($project['problem'] ?? '') ?>
  </p>
</section>

<!-- Solution -->
<section data-reveal class="grid-split-1-2" style="padding:48px 0;border-top:1px solid var(--hairline);">
  <div class="eyebrow">THE SOLUTION</div>
  <p class="body-l" style="max-width:62ch;color:rgba(10,10,10,0.85);">
    <?= e($project['solution'] ?? '') ?>
  </p>
</section>

<!-- Outcome -->
<section data-reveal class="hero-dark" style="padding:64px 60px;margin:48px 0 0;">
  <span class="aurora" style="top:-100px;right:-80px;"></span>
  <div style="position:relative;z-index:2;">
    <div class="ff-mono eyebrow--on-dark" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;">// 04 — THE OUTCOME</div>
    <p class="ff-display" style="font-weight:600;font-size:clamp(28px,4vw,42px);line-height:1.2;letter-spacing:-0.02em;max-width:24ch;margin:18px 0 0;color:#fff;">
      <?= e($project['outcome'] ?? '') ?>
    </p>
  </div>
</section>


<!-- Gallery — PhotoSwipe 5 powered. Anchor href stays a real image so
     a no-JS visitor still gets the image, and crawlers index it. The
     library is loaded only when this section renders. -->
<?php if (!empty($gallery)): ?>
<section data-reveal style="margin:64px 0;">
  <div class="eyebrow">GALLERY</div>
  <div data-stagger="80" class="grid-cols-3" style="margin-top:24px;" id="project-gallery">
    <?php foreach ($gallery as $i => $src):
      // We don't know the natural size of operator-uploaded images at
      // render time. Set a sane default; PhotoSwipe falls back gracefully
      // when these are off because it measures on open. -->
    ?>
      <a href="<?= e($src) ?>"
         data-pswp-width="1600"
         data-pswp-height="1200"
         data-pswp-alt="<?= e($project['title']) ?> · <?= e((string)($i+1)) ?>"
         data-reveal>
        <img src="<?= e($src) ?>" alt="<?= e($project['title']) ?> — image <?= (int)($i+1) ?>"
             loading="lazy" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:14px;">
      </a>
    <?php endforeach; ?>
  </div>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/photoswipe@5.4.4/dist/photoswipe.css">
<script type="module">
  import PhotoSwipeLightbox from 'https://cdn.jsdelivr.net/npm/photoswipe@5.4.4/dist/photoswipe-lightbox.esm.js';
  const lightbox = new PhotoSwipeLightbox({
    gallery: '#project-gallery',
    children: 'a',
    pswpModule: () => import('https://cdn.jsdelivr.net/npm/photoswipe@5.4.4/dist/photoswipe.esm.js'),
  });
  lightbox.init();
</script>
<?php endif; ?>

<!-- Related -->
<?php if (!empty($related)): ?>
<section data-reveal style="margin-top:80px;border-top:1px solid var(--hairline);padding-top:64px;">
  <div class="eyebrow">MORE WORK</div>
  <h2 class="h2" style="margin:12px 0 32px;">Related projects.</h2>
  <div class="project-rows">
    <?php $i = 1; foreach ($related as $p):
      $p['index'] = $i++;
      partial('project-row', ['project' => $p]);
    endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php partial('share-bar', ['share' => ['title' => $project['title'], 'url' => url('/projects/' . $project['slug'])]]); ?>

<?php partial('cta-final'); ?>
