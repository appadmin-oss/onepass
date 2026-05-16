<?php require_once AFS_ROOT . '/src/views/partials/icons.php'; ?>
<header class="site-nav" role="banner">
  <div class="container">
    <div class="site-nav__row">
      <a href="<?= e(url('/')) ?>" class="site-nav__brand" aria-label="Afrostrength home">
        <?= icon_logo(22, '#0A0A0A') ?>
        <span>Afrostrength</span>
      </a>
      <nav class="site-nav__links" aria-label="Primary">
        <a class="nav-link" data-href="/" href="<?= e(url('/')) ?>">Studio</a>
        <a class="nav-link" data-href="/services" href="<?= e(url('/services')) ?>">Services</a>
        <a class="nav-link" data-href="/projects" href="<?= e(url('/projects')) ?>">Projects</a>
        <a class="nav-link" data-href="/about"    href="<?= e(url('/about')) ?>">About</a>
        <a class="nav-link" data-href="/blog"     href="<?= e(url('/blog')) ?>">Field notes</a>
      </nav>
      <div class="site-nav__login" style="display:flex;align-items:center;gap:18px;">
        <button class="nav-link" aria-label="Locale" style="display:inline-flex;gap:8px;align-items:center;background:transparent;border:0;padding:0;">
          <?= icon('globe', 18) ?> <span>EN</span>
        </button>
        <a class="nav-link" href="<?= e(url('/admin/login')) ?>">Log in</a>
      </div>
      <button class="site-nav__hamburger" data-drawer-open aria-label="Open menu"><?= icon('menu', 18) ?></button>
    </div>

    <div class="site-nav__cta-row">
      <div class="site-nav__sub-links">
        <span class="ff-display" style="font-weight:700;font-size:16px;">Academy</span>
        <a class="nav-link" data-href="/academy/courses"        href="<?= e(url('/academy/courses')) ?>">Courses</a>
        <a class="nav-link" data-href="/academy/live-sessions"  href="<?= e(url('/academy/live-sessions')) ?>">Live sessions</a>
        <a class="nav-link" data-href="/academy/certifications" href="<?= e(url('/academy/certifications')) ?>">Certifications</a>
        <a class="nav-link" data-href="/academy/instructors"    href="<?= e(url('/academy/instructors')) ?>">Instructors</a>
      </div>
      <a href="<?= e(url('/academy/courses')) ?>" class="btn btn-primary btn-sm">Browse the catalog <?= icon_chev(12) ?></a>
    </div>
  </div>
</header>

<div class="drawer" data-drawer aria-hidden="true">
  <div class="drawer__panel" role="dialog" aria-label="Academy menu">
    <div style="display:flex;align-items:center;justify-content:space-between;">
      <a class="site-nav__brand" href="<?= e(url('/academy')) ?>"><?= icon_logo(20) ?><span>Afrostrength · Academy</span></a>
      <button class="drawer__close" data-drawer-close aria-label="Close menu"><?= icon('close', 14) ?></button>
    </div>
    <nav class="drawer__nav" aria-label="Mobile primary">
      <a href="<?= e(url('/academy/courses')) ?>">Courses</a>
      <a href="<?= e(url('/academy/live-sessions')) ?>">Live sessions</a>
      <a href="<?= e(url('/academy/certifications')) ?>">Certifications</a>
      <a href="<?= e(url('/academy/instructors')) ?>">Instructors</a>
      <a href="<?= e(url('/academy/apply')) ?>">Enroll</a>
      <a href="<?= e(url('/')) ?>">Back to Studio</a>
    </nav>
    <div style="margin-top:auto;padding-top:24px;border-top:1px solid var(--hairline);">
      <a href="<?= e(url('/academy/apply')) ?>" class="btn btn-primary btn-block">Get certified <?= icon_chev() ?></a>
    </div>
  </div>
</div>
