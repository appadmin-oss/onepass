<?php
/**
 * image-pattern — full-bleed photo (not a cutout) with a thin Afrocentric
 * pattern overlay along one edge. Use for project hero shots, blog
 * lead images, course hero, etc.
 *
 *   partial('image-pattern', [
 *     'src'     => 'https://images.unsplash.com/photo-…?w=1200',
 *     'alt'     => 'A Lagos office at dusk',
 *     'pattern' => 'arched-light',      // pattern to slip behind the image
 *     'overlay' => 'right',             // right | left | top | bottom | none
 *     'aspect'  => '4/3',               // any valid aspect-ratio
 *     'caption' => 'CACENTRE · Egbeda',
 *   ]);
 *
 * Difference from cutout: this expects a normal photo (no transparent
 * background). The pattern peeks from one side as a designed "shoulder"
 * rather than wrapping the subject.
 */
$src     = trim((string)($src     ?? ''));
$alt     = (string)($alt     ?? '');
$pattern = (string)($pattern ?? 'sahel-contour');
$overlay = (string)($overlay ?? 'right');
$aspect  = (string)($aspect  ?? '4/3');
$caption = (string)($caption ?? '');

$validPatterns = ['sahel-contour', 'terracotta-grid', 'diagonal-weave', 'arched-light'];
$validOverlays = ['right', 'left', 'top', 'bottom', 'none'];
if (!in_array($pattern, $validPatterns, true)) $pattern = 'sahel-contour';
if (!in_array($overlay, $validOverlays, true)) $overlay = 'right';
?>
<figure class="imgpat imgpat--<?= e($overlay) ?>" style="--imgpat-aspect:<?= e($aspect) ?>;">
  <?php if ($overlay !== 'none'): ?>
    <div class="imgpat__band imgpat__band--<?= e($pattern) ?>" aria-hidden="true">
      <?php
        $p = AFS_ROOT . '/assets/svg/patterns/' . $pattern . '.svg';
        if (is_file($p)) echo (string) file_get_contents($p);
      ?>
    </div>
  <?php endif; ?>
  <div class="imgpat__media">
    <?php if ($src !== ''): ?>
      <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" loading="lazy" decoding="async">
    <?php else: ?>
      <div class="imgpat__placeholder" aria-hidden="true">// IMAGE PENDING</div>
    <?php endif; ?>
  </div>
  <?php if ($caption !== ''): ?>
    <figcaption class="imgpat__caption"><?= e($caption) ?></figcaption>
  <?php endif; ?>
</figure>
