<?php require_once AFS_ROOT . '/src/views/partials/icons.php'; ?>
<header class="site-nav" role="banner">
  <div class="container">
    <div class="site-nav__row">
      <a href="<?= e(url('/')) ?>" class="site-nav__brand" aria-label="Afrostrength home">
        <?= icon_logo(22, '#0A0A0A') ?>
        <span>Afrostrength</span>
      </a>
      <nav class="site-nav__links" aria-label="Primary">
        <a class="nav-link<?= is_active('/services') ? ' is-active' : '' ?>" href="<?= e(url('/services')) ?>">Services</a>
        <a class="nav-link<?= is_active('/projects') ? ' is-active' : '' ?>" href="<?= e(url('/projects')) ?>">Projects</a>
        <a class="nav-link<?= is_active('/academy')  ? ' is-active' : '' ?>" href="<?= e(url('/academy'))  ?>">Academy</a>
        <a class="nav-link<?= is_active('/about')    ? ' is-active' : '' ?>" href="<?= e(url('/about'))    ?>">About</a>
        <a class="nav-link<?= is_active('/blog')     ? ' is-active' : '' ?>" href="<?= e(url('/blog'))     ?>">Field notes</a>
        <a class="nav-link<?= is_active('/tools')    ? ' is-active' : '' ?>" href="<?= e(url('/tools'))    ?>">Tools</a>
      </nav>
      <div class="site-nav__login" style="display:flex;align-items:center;gap:14px;">
        <button class="nav-link" type="button" data-cmdk-trigger aria-label="Search (Cmd/Ctrl + K)"
                style="background:transparent;border:0;padding:0;display:inline-flex;gap:8px;align-items:center;">
          <?= icon('search', 16) ?> <span>Search</span>
        </button>
        <a class="nav-link" href="<?= e(url('/contact')) ?>">Contact</a>
        <a class="btn btn-primary btn-sm" href="<?= e(url('/academy/apply')) ?>">Apply <?= icon_chev(12) ?></a>
      </div>
      <button class="site-nav__hamburger" type="button" data-drawer-open aria-label="Open menu"><?= icon('menu', 18) ?></button>
    </div>
  </div>
</header>

<div class="drawer" data-drawer aria-hidden="true">
  <div class="drawer__panel" role="dialog" aria-label="Site menu">
    <div style="display:flex;align-items:center;justify-content:space-between;">
      <a class="site-nav__brand" href="<?= e(url('/')) ?>"><?= icon_logo(20) ?><span>Afrostrength</span></a>
      <button class="drawer__close" type="button" data-drawer-close aria-label="Close menu"><?= icon('close', 14) ?></button>
    </div>
    <nav class="drawer__nav" aria-label="Mobile primary">
      <a href="<?= e(url('/services')) ?>">Services</a>
      <a href="<?= e(url('/projects')) ?>">Projects</a>
      <a href="<?= e(url('/academy')) ?>">Academy</a>
      <a href="<?= e(url('/about')) ?>">About</a>
      <a href="<?= e(url('/blog')) ?>">Field notes</a>
      <a href="<?= e(url('/tools')) ?>">Tools</a>
      <a href="<?= e(url('/contact')) ?>">Contact</a>
    </nav>
    <div style="margin-top:auto;padding-top:24px;border-top:1px solid var(--hairline);">
      <a href="<?= e(url('/academy/apply')) ?>" class="btn btn-primary btn-block">Apply to the Academy <?= icon_chev() ?></a>
    </div>
  </div>
</div>
