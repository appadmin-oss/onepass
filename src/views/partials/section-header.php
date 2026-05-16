<?php
/**
 * Section header — quiet by default. A small uppercase eyebrow, a
 * sentence-case title, an optional lead, and a single right-side CTA.
 *
 * Inputs (all optional except $title):
 *   string $eyebrow  short label e.g. "Services"
 *   string $title    HTML allowed
 *   string $lead     supporting paragraph; ~62ch reads best
 *   array  $cta      ['label' => 'See the work', 'href' => '/projects']
 *   string $align    'start' (default) or 'center'
 *   string $tone     '' (default) | 'on-dark'
 *
 * $num and $aside are accepted for backwards compatibility but no
 * longer rendered — they were noise.
 */
$eyebrow = $eyebrow ?? '';
$title   = $title   ?? '';
$lead    = $lead    ?? ($support ?? '');
$cta     = $cta     ?? null;
$align   = ($align ?? 'start') === 'center' ? 'center' : 'start';
$tone    = ($tone  ?? '')      === 'on-dark' ? 'on-dark' : '';
$headingId = 'section-' . substr(sha1(strip_tags((string)$title)), 0, 8);
?>
<header class="section-hd section-hd--<?= e($align) ?><?= $tone ? ' section-hd--' . $tone : '' ?>"
        data-reveal
        aria-labelledby="<?= e($headingId) ?>">
  <div class="section-hd__body">
    <?php if ($eyebrow !== ''): ?>
      <span class="section-hd__eyebrow"><?= e($eyebrow) ?></span>
    <?php endif; ?>
    <h2 id="<?= e($headingId) ?>" class="section-hd__title"><?= $title ?></h2>
    <?php if ($lead !== ''): ?>
      <p class="section-hd__lead"><?= $lead ?></p>
    <?php endif; ?>
    <?php if ($cta && !empty($cta['href'])): ?>
      <a class="section-hd__cta" href="<?= e($cta['href']) ?>">
        <?= e($cta['label'] ?? 'Learn more') ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
          <path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
    <?php endif; ?>
  </div>
</header>
