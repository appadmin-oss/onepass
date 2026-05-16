<?php
/**
 * Subpage hero — peach rounded card with an inline character illustration.
 * Pattern lifted from the Musixmatch "Contribute" page reference.
 *
 * Args ($hero):
 *   eyebrow       — small mono label (e.g. "// SERVICES")
 *   title         — display headline; wrap punch words in <em> for crimson italic
 *   sub           — supporting paragraph
 *   cta_label     — link label (renders the inline arrow CTA)
 *   cta_href      — link target
 *   illustration  — basename of an svg in assets/svg/illustrations/ (e.g. "two-figures")
 *   variant       — "" | "ink" | "maroon"  (defaults to peach)
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
$hero = $hero ?? [];
$variant = $hero['variant'] ?? '';
$variantCls = $variant ? ' subpage-hero--' . $variant : '';
?>
<section class="subpage-hero<?= e($variantCls) ?>" data-reveal>
  <div>
    <?php if (!empty($hero['eyebrow'])): ?>
      <div class="subpage-hero__eyebrow"><?= e(ltrim((string)$hero['eyebrow'], '/ ')) ?></div>
    <?php endif; ?>
    <h1 class="subpage-hero__title"><?= $hero['title'] ?? '' ?></h1>
    <?php if (!empty($hero['sub'])): ?>
      <p class="subpage-hero__sub"><?= e($hero['sub']) ?></p>
    <?php endif; ?>
    <?php if (!empty($hero['cta_label']) && !empty($hero['cta_href'])): ?>
      <a class="subpage-hero__cta" href="<?= e($hero['cta_href']) ?>">
        <?= e($hero['cta_label']) ?> <?= icon_chev() ?>
      </a>
    <?php endif; ?>
  </div>

  <div class="subpage-hero__art" aria-hidden="true">
    <?php
    $illust = $hero['illustration'] ?? 'two-figures';
    $path = AFS_ROOT . '/assets/svg/illustrations/' . preg_replace('/[^a-z0-9\-]/', '', $illust) . '.svg';
    if (is_file($path)) readfile($path);
    ?>
  </div>
</section>
