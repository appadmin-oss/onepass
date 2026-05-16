<?php
/**
 * Cutout — a real (transparent-background PNG or WebP) cut-out image
 * paired with an Afrocentric pattern frame. The pattern is the constant;
 * the image drops in when the operator ships it.
 *
 *   partial('cutout', [
 *     'src'      => '/assets/images/cutouts/founder.webp',
 *     'alt'      => 'Studio founder, full-length cut-out portrait',
 *     'pattern'  => 'sahel-contour',  // or terracotta-grid | diagonal-weave | arched-light
 *     'aspect'   => 'portrait',       // portrait | landscape | square
 *     'tone'     => 'bone',           // bone | peach | ink | crimson
 *     'caption'  => 'Founder · Lagos',
 *     'eager'    => false,            // true to skip lazy-loading
 *   ]);
 *
 * When the source image doesn't exist yet, the frame still renders —
 * with just the pattern + a soft placeholder mark — so the operator
 * can wire the layout before art arrives and the production page never
 * shows a broken-image icon.
 *
 * The pattern + image relationship is intentional:
 *   - The pattern uses currentColor at low opacity; the wrapper sets
 *     `color` per the `tone` choice so the same SVG works on every
 *     background.
 *   - The cut-out image sits at 92% of the frame, centred, with a
 *     gentle drop-shadow only when the host surface is ink/peach.
 *   - There is no inner border, no decorative chrome — just frame
 *     pattern + image. Calm by construction.
 */
$src     = trim((string)($src     ?? ''));
$alt     = (string)($alt     ?? '');
$pattern = (string)($pattern ?? 'sahel-contour');
$aspect  = (string)($aspect  ?? 'portrait');
$tone    = (string)($tone    ?? 'bone');
$caption = (string)($caption ?? '');
$eager   = (bool)($eager     ?? false);

$validPatterns = ['sahel-contour', 'terracotta-grid', 'diagonal-weave', 'arched-light'];
$validAspects  = ['portrait', 'landscape', 'square'];
$validTones    = ['bone', 'peach', 'ink', 'crimson'];

if (!in_array($pattern, $validPatterns, true)) $pattern = 'sahel-contour';
if (!in_array($aspect,  $validAspects,  true)) $aspect  = 'portrait';
if (!in_array($tone,    $validTones,    true)) $tone    = 'bone';

// Resolve filesystem path so we can check existence without a 404 over HTTP.
$fsPath = $src !== '' && str_starts_with($src, '/')
    ? AFS_ROOT . $src
    : null;
$hasImage = $fsPath && is_file($fsPath);
?>
<figure class="cutout cutout--<?= e($aspect) ?> cutout--<?= e($tone) ?>" data-cutout>
  <div class="cutout__pattern" aria-hidden="true">
    <?php
      $p = AFS_ROOT . '/assets/svg/patterns/' . $pattern . '.svg';
      if (is_file($p)) {
          // Inline the SVG so currentColor works without a wrapping <object>.
          $svg = (string) file_get_contents($p);
          // The pattern SVGs are designed to tile. We swap the viewBox-bound
          // <svg> into a repeated background by stamping it into a CSS
          // background-image. Inlined here as a fallback so server-side
          // rendering shows the pattern even before CSS arrives.
          echo $svg;
      }
    ?>
  </div>

  <?php if ($hasImage): ?>
    <img class="cutout__img"
         src="<?= e($src) ?>"
         alt="<?= e($alt) ?>"
         <?= $eager ? '' : 'loading="lazy" decoding="async"' ?>>
  <?php else: ?>
    <!-- Layout-stable placeholder. Operator: drop the cut-out PNG at
         <?= e($src ?: '/assets/images/cutouts/<name>.webp') ?> and it
         replaces this glyph without re-flowing the page. -->
    <div class="cutout__placeholder" aria-hidden="true">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <circle cx="9" cy="9" r="2"/>
        <path d="M21 15l-5-5L5 21"/>
      </svg>
    </div>
  <?php endif; ?>

  <?php if ($caption !== ''): ?>
    <figcaption class="cutout__caption"><?= e($caption) ?></figcaption>
  <?php endif; ?>
</figure>
