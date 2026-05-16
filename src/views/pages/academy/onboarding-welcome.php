<?php
/** @var array $pending */
require_once AFS_ROOT . '/src/views/partials/icons.php';
require_once AFS_ROOT . '/src/views/partials/divider.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<section class="onboarding" data-reveal>
  <div class="onboarding__step onboarding__success">
    <div class="onboarding__success-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
        <path d="M4 12l5 5L20 6" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// APPLICATION RECEIVED</div>
    <h1 class="ff-display" style="font-weight:700;font-size:clamp(32px,4vw,44px);line-height:1.1;letter-spacing:-0.025em;margin:14px 0 12px;">
      Welcome, <em class="accent-italic"><?= e(strtok($pending['name'] ?? 'student', ' ')) ?>.</em>
    </h1>
    <p class="body-l" style="color:var(--text-mute);max-width:50ch;margin:0 auto;">
      We've received your application for
      <strong style="color:var(--text);"><?= e($pending['track'] ?? 'an Afrotech Academy track') ?></strong>.
      A confirmation has been sent to <code style="background:var(--bg-soft);padding:2px 8px;border-radius:6px;font-family:'JetBrains Mono',monospace;font-size:13px;"><?= e($pending['email'] ?? '') ?></code>.
    </p>

    <?php divider('rule', ['width' => 'narrow']); ?>

    <div class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-dim);text-align:center;">// WHAT HAPPENS NEXT</div>

    <ol class="onboarding__next-list">
      <li>
        <span class="num">01</span>
        <div>
          <strong style="display:block;">Within one working day</strong>
          <span class="caption">An admissions operator emails to confirm your seat and share payment details.</span>
        </div>
      </li>
      <li>
        <span class="num">02</span>
        <div>
          <strong style="display:block;">Cohort orientation</strong>
          <span class="caption">A live 30-minute session with your cohort lead. Calendar invite arrives once payment clears.</span>
        </div>
      </li>
      <li>
        <span class="num">03</span>
        <div>
          <strong style="display:block;">Day one</strong>
          <span class="caption">First live session, mentor pairing, and access to the cohort workspace. Hands on the keyboard from week one.</span>
        </div>
      </li>
    </ol>

    <div style="margin-top:36px;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
      <a href="<?= e(url('/academy')) ?>" class="btn btn-ghost">Back to Academy</a>
      <a href="<?= e(url('/academy/courses')) ?>" class="btn btn-primary">Browse other tracks <?= icon_chev() ?></a>
    </div>
  </div>
</section>

<script>
  // Crimson confetti burst, brand-coloured. canvas-confetti loads from CDN.
  (function fire(){
    if (typeof confetti !== 'function') return setTimeout(fire, 120);
    var colors = ['#C0392B', '#8B0000', '#FCB7AB', '#FCE7DD', '#0A0A0A'];
    var end = Date.now() + 1400;
    (function frame(){
      confetti({ particleCount: 4, angle: 60,  spread: 60, origin: { x: 0, y: 0.85 }, colors: colors });
      confetti({ particleCount: 4, angle: 120, spread: 60, origin: { x: 1, y: 0.85 }, colors: colors });
      if (Date.now() < end) requestAnimationFrame(frame);
    })();
  })();
</script>
