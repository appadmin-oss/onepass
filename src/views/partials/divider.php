<?php
/** Reusable SVG dividers — clean, brand-consistent, no folk-art motifs.
 *  Variants: wave | dots | rule | chevrons
 *  Usage: <?php divider('wave'); ?>  or  <?php divider('rule', ['width' => 'narrow']); ?>
 */
function divider(string $variant = 'rule', array $opts = []): void {
    $width = $opts['width'] ?? 'full';   // full | narrow
    $tone  = $opts['tone']  ?? 'crimson'; // crimson | ink | bone
    echo '<div class="divider divider--' . htmlspecialchars($width) . '" aria-hidden="true">';
    switch ($variant) {
        case 'wave':
            echo '<svg viewBox="0 0 1200 24" preserveAspectRatio="none" width="100%" height="24">'
               . '<path d="M0 12 Q 100 2, 200 12 T 400 12 T 600 12 T 800 12 T 1000 12 T 1200 12" '
               . 'fill="none" stroke="#C0392B" stroke-width="1.4" stroke-linecap="round"/></svg>';
            break;
        case 'dots':
            echo '<svg viewBox="0 0 1200 12" width="100%" height="12">';
            for ($i = 0; $i < 60; $i++) {
                $cx = 10 + $i * 20;
                $r  = ($i % 5 === 0) ? 3 : 1.5;
                $fill = ($i % 5 === 0) ? '#C0392B' : '#0A0A0A';
                $op   = ($i % 5 === 0) ? 1 : 0.35;
                echo "<circle cx=\"$cx\" cy=\"6\" r=\"$r\" fill=\"$fill\" opacity=\"$op\"/>";
            }
            echo '</svg>';
            break;
        case 'chevrons':
            echo '<svg viewBox="0 0 1200 12" width="100%" height="12">';
            for ($i = 0; $i < 30; $i++) {
                $x = 10 + $i * 40;
                echo '<path d="M' . $x . ' 4 L' . ($x+8) . ' 8 L' . ($x+16) . ' 4" stroke="#C0392B" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>';
            }
            echo '</svg>';
            break;
        case 'rule':
        default:
            // Two-stop tapered rule: hairline → crimson → hairline
            echo '<svg viewBox="0 0 1200 4" preserveAspectRatio="none" width="100%" height="4">'
               . '<defs><linearGradient id="afsRule" x1="0" y1="0" x2="1" y2="0">'
               . '<stop offset="0%"   stop-color="#0A0A0A" stop-opacity="0.05"/>'
               . '<stop offset="35%"  stop-color="#C0392B" stop-opacity="0.95"/>'
               . '<stop offset="65%"  stop-color="#8B0000" stop-opacity="0.95"/>'
               . '<stop offset="100%" stop-color="#0A0A0A" stop-opacity="0.05"/>'
               . '</linearGradient></defs>'
               . '<rect x="0" y="1" width="1200" height="2" fill="url(#afsRule)" rx="1"/></svg>';
            break;
    }
    echo '</div>';
}
