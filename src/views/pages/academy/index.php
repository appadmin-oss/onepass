<?php
/** @var array $courses
 *  @var array $instructors
 *  @var array $certs
 *  @var array $sessions
 *  @var bool  $lmsEnabled
 *  @var bool  $jaasEnabled
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<!-- Hero — Afrotech Academy promo card (real cut-out portrait) -->
<section class="academy-promo" data-reveal>
  <div class="academy-promo__copy">
    <div class="academy-promo__mantra">Learn smartly · Build cleanly · Get certified</div>
    <h1 class="academy-promo__title" data-words data-words-step="60">
      Get globally certified with <em>Afrotech Academy.</em>
    </h1>
    <p class="academy-promo__sub">
      Master cloud infrastructure, cybersecurity, data, software development, and AI/ML — building and deploying advanced models for a secure and analytical future.
    </p>
    <div class="hero-dark__ctas" style="margin-top:8px;">
      <a href="<?= e(url('/academy/apply')) ?>" class="btn btn-primary">Apply to next cohort <?= icon_chev() ?></a>
      <a href="<?= e(url('/academy/courses')) ?>" class="btn" style="background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.25);">Browse tracks</a>
    </div>
    <?php if ($lmsEnabled): ?>
      <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.55);margin-top:24px;">
        // LIVE CATALOG · MOODLE WIRED · <a href="<?= e(LMS_BASE_URL) ?>" style="color:#FCB7AB;text-decoration:underline;text-underline-offset:3px;">academy.afrostrength.com/lms</a>
      </div>
    <?php endif; ?>
  </div>
  <div class="academy-promo__art" style="background-image:url('<?= e(asset('images/promo/4.png')) ?>');" role="img" aria-label="Afrotech Academy student in graduation regalia"></div>
</section>


<!-- Featured courses -->
<section style="margin:80px 0;">
  <?php partial('section-header', [
      'num'     => '01 — CATALOG',
      'title'   => 'Three programmes. <em class="accent-italic">One curriculum.</em>',
      'support' => 'Foundations, intermediate, and advanced. Built to stack — finish one, the next plugs straight in.',
  ]); ?>

  <div data-stagger="80" data-reveal class="grid-cols-3">
    <?php foreach ($courses as $c) partial('course-card', ['c' => $c]); ?>
  </div>
  <div style="margin-top:32px;text-align:right;"><a class="nav-link" href="<?= e(url('/academy/courses')) ?>">All courses →</a></div>
</section>


<!-- Live sessions teaser -->
<?php if (!empty($sessions)): ?>
<section data-reveal style="margin:80px 0;">
  <?php partial('section-header', [
      'num'     => '02 — LIVE SESSIONS',
      'title'   => 'Live with the studio. <em class="accent-italic">No replays.</em>',
      'support' => $jaasEnabled
        ? 'Powered by Jitsi-as-a-Service. Working sessions, type clinics, and open Q&As — all live, all interactive.'
        : 'Working sessions, type clinics, and open Q&As — all live, all interactive. Powered by Jitsi when configured.',
  ]); ?>

  <div data-stagger="80" class="grid-cols-3">
    <?php foreach ($sessions as $s):
      $startTs = strtotime($s['starts_at']);
    ?>
      <div class="hairline" data-reveal style="padding:24px;border-radius:18px;background:var(--bone);">
        <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);">
          // <?= e(date('D · d M', $startTs)) ?> · <?= e(date('H:i', $startTs)) ?> WAT
        </div>
        <h3 class="ff-display" style="font-weight:600;font-size:22px;line-height:1.2;margin:14px 0 8px;"><?= e($s['title']) ?></h3>
        <p class="body-m" style="color:rgba(10,10,10,0.7);"><?= e($s['summary'] ?? '') ?></p>
        <div class="ff-mono" style="font-size:11px;color:rgba(10,10,10,0.55);margin-top:18px;"><?= e($s['instructor']) ?> · <?= e((string)$s['duration_minutes']) ?> min</div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top:32px;text-align:right;"><a class="nav-link" href="<?= e(url('/academy/live-sessions')) ?>">All live sessions →</a></div>
</section>
<?php endif; ?>


<!-- Certifications panel (maroon) -->
<?php partial('cert-panel'); ?>


<!-- Instructors -->
<section style="margin:80px 0;">
  <?php partial('section-header', [
      'num'     => '04 — INSTRUCTORS',
      'title'   => 'Operators. <em class="accent-italic">Not academics.</em>',
      'support' => 'Every instructor ships in the studio. The curriculum is what they actually do, written down.',
  ]); ?>
  <div data-stagger="80" data-reveal class="grid-cols-3">
    <?php foreach ($instructors as $op) partial('operator-card', ['op' => $op]); ?>
  </div>
</section>


<!-- Final CTA -->
<section class="final-cta" data-reveal style="margin-top:80px;background:var(--maroon);">
  <span class="aurora" style="top:-120px;right:-80px;background:radial-gradient(circle,#8B0000,#5C0000,transparent);"></span>
  <span class="aurora" style="bottom:-180px;left:-100px;animation-delay:-4s;background:radial-gradient(circle,#8B0000,#5C0000,transparent);"></span>
  <div class="ff-mono eyebrow--on-dark" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;">// READY TO ENROL</div>
  <h3 class="final-cta__title" data-words data-words-step="60">
    The next cohort opens <em class="accent-italic" style="color:#FCB7AB;">in two weeks.</em>
  </h3>
  <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
    <a href="<?= e(url('/academy/apply')) ?>" class="btn btn-on-dark">Reserve a seat <?= icon_chev() ?></a>
    <span class="ff-mono" style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.65);">// 600+ alumni · 3 cities · 24 cohorts run</span>
  </div>
</section>
