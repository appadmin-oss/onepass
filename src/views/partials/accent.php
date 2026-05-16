<?php
/**
 * Accent — Coursera-style geometric shape that lives in a corner of a
 * card or section. ONE per surface, low opacity, brand colour. Use it
 * as a visual anchor, not as decoration fill.
 *
 *   partial('accent', [
 *     'shape'   => 'arc',       // arc | dots | rings | bar
 *     'corner'  => 'tr',        // tr | tl | br | bl
 *     'tone'    => 'crimson',   // crimson | ink | peach
 *     'size'    => 220,         // px
 *     'opacity' => 0.18,
 *   ]);
 *
 * Use cases:
 *   - top-right of a hero card (a quarter-arc that softens the corner)
 *   - bottom-left of a stat tile (small ring cluster)
 *   - section header band (long thin bar)
 *
 * Renders an absolutely-positioned SVG inside the host element. The
 * host must be position: relative + isolation: isolate; we sit at
 * z-index 0 so content above z-index 1 always wins legibility.
 */
$shape   = (string)($shape   ?? 'arc');
$corner  = (string)($corner  ?? 'tr');
$tone    = (string)($tone    ?? 'crimson');
$size    = (int)   ($size    ?? 200);
$opacity = (float) ($opacity ?? 0.18);

$validShapes  = ['arc','dots','rings','bar'];
$validCorners = ['tr','tl','br','bl'];
$validTones   = ['crimson','ink','peach','maroon'];
if (!in_array($shape,  $validShapes,  true)) $shape  = 'arc';
if (!in_array($corner, $validCorners, true)) $corner = 'tr';
if (!in_array($tone,   $validTones,   true)) $tone   = 'crimson';
$size    = max(80, min(640, $size));
$opacity = max(0.05, min(0.5, $opacity));

$colour = [
  'crimson' => '#C0392B',
  'maroon'  => '#5C0000',
  'ink'     => '#0A0A0A',
  'peach'   => '#F8D2BF',
][$tone];

$bodies = [
  // Quarter-arc, anchored at the corner the host element places us in.
  'arc' => '<path d="M0 0 A ' . $size . ' ' . $size . ' 0 0 1 ' . $size . ' ' . $size . '" fill="' . $colour . '"/>',
  // Dot grid, 6×6, fades from the anchor corner outward.
  'dots' => (function () use ($colour) {
      $out = '';
      for ($y = 0; $y < 6; $y++) {
        for ($x = 0; $x < 6; $x++) {
          $r = max(0.6, 4 - ($x + $y) * 0.4);
          $out .= '<circle cx="' . (12 + $x * 22) . '" cy="' . (12 + $y * 22) . '" r="' . $r . '" fill="' . $colour . '"/>';
        }
      }
      return $out;
  })(),
  // Concentric rings.
  'rings' => '<g fill="none" stroke="' . $colour . '" stroke-width="1.5">'
           . '<circle cx="0" cy="0" r="' . ($size * 0.9) . '"/>'
           . '<circle cx="0" cy="0" r="' . ($size * 0.7) . '"/>'
           . '<circle cx="0" cy="0" r="' . ($size * 0.5) . '"/>'
           . '<circle cx="0" cy="0" r="' . ($size * 0.3) . '"/>'
           . '</g>',
  // Long thin bar — used along an edge as a separator-style accent.
  'bar' => '<rect x="0" y="' . ($size - 6) . '" width="' . $size . '" height="6" fill="' . $colour . '"/>'
         . '<rect x="0" y="' . ($size - 16) . '" width="' . ($size * 0.55) . '" height="2" fill="' . $colour . '" opacity="0.5"/>',
];

// SVG viewBox is anchored so that the corner sits at (0,0) and the
// shape grows into the host. We don't try to flip for each corner —
// the .accent--<corner> CSS class positions us and the artwork is
// designed to look right at any corner.
$viewBox = '0 0 ' . $size . ' ' . $size;
?>
<div class="accent accent--<?= e($corner) ?>" style="width:<?= (int)$size ?>px;height:<?= (int)$size ?>px;opacity:<?= e((string)$opacity) ?>;" aria-hidden="true">
  <svg viewBox="<?= e($viewBox) ?>" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
    <?= $bodies[$shape] ?>
  </svg>
</div>
