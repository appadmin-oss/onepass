<?php
/** Promo card for the Academy. Drop into any page to push prospects
 *  to /academy/apply. Subtle on light pages, bold on dark ones. */
require_once AFS_ROOT . '/src/views/partials/icons.php';
?>
<aside class="grad-crimson" data-reveal style="border-radius:20px;padding:32px;display:grid;grid-template-columns:1fr auto;gap:24px;align-items:center;margin:48px 0;">
  <div>
    <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:rgba(255,255,255,0.78);text-transform:uppercase;">// AFROTECH ACADEMY</div>
    <h3 class="ff-display" style="font-weight:700;font-size:clamp(24px,3vw,32px);line-height:1.1;letter-spacing:-0.02em;margin:8px 0 8px;color:#fff;">
      Learn smartly. Build cleanly. <em style="font-style:italic;color:#FCB7AB;">Get certified.</em>
    </h3>
    <p style="margin:0;color:rgba(255,255,255,0.82);font-size:14px;line-height:1.6;max-width:60ch;">
      Trust-first application · 3 minutes · no login. Pick a track, share your skill profile, and we'll match you to the right cohort.
    </p>
  </div>
  <a href="<?= e(url('/academy/apply')) ?>" class="btn btn-on-dark" style="white-space:nowrap;">
    Apply free <?= icon_chev() ?>
  </a>
</aside>
