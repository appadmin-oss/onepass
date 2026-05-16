<?php /** @var string $bodyContent */
require_once AFS_ROOT . '/src/views/partials/icons.php';
?>
<!DOCTYPE html>
<html lang="<?= e(AFS_LOCALE) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Admin · Afrostrength') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link href="https://api.fontshare.com/v2/css?f[]=garet@600,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset_v('css/custom.css')) ?>">
  <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
  <meta name="robots" content="noindex,nofollow">
</head>
<body>
<?php if (!Auth::check() && current_path() !== '/admin/login'): ?>
  <?= $bodyContent ?>
<?php else: ?>
<div class="admin-shell">
  <aside class="admin-side">
    <a href="<?= e(url('/admin')) ?>" style="display:inline-flex;align-items:center;gap:8px;color:#fff;font-family:Garet,sans-serif;font-weight:700;font-size:16px;">
      <?= icon_logo(20, '#FFFFFF') ?>
      <span>Afrostrength</span>
    </a>
    <h6>Studio</h6>
    <a href="<?= e(url('/admin')) ?>" class="<?= is_active('/admin') && current_path() === '/admin' ? 'is-active' : '' ?>">Dashboard</a>
    <a href="<?= e(url('/admin/inquiries')) ?>" class="<?= is_active('/admin/inquiries') ? 'is-active' : '' ?>">Inquiries</a>
    <a href="<?= e(url('/admin/posts')) ?>"     class="<?= is_active('/admin/posts') ? 'is-active' : '' ?>">Field notes</a>
    <a href="<?= e(url('/admin/projects')) ?>"  class="<?= is_active('/admin/projects') ? 'is-active' : '' ?>">Projects</a>
    <a href="<?= e(url('/admin/services')) ?>"  class="<?= is_active('/admin/services') ? 'is-active' : '' ?>">Services</a>
    <a href="<?= e(url('/admin/content')) ?>"   class="<?= is_active('/admin/content') ? 'is-active' : '' ?>">Content blocks</a>
    <a href="<?= e(url('/admin/promotions')) ?>" class="<?= is_active('/admin/promotions') ? 'is-active' : '' ?>">Promotions</a>
    <a href="<?= e(url('/admin/status/incidents')) ?>" class="<?= is_active('/admin/status') ? 'is-active' : '' ?>">Status &amp; incidents</a>

    <h6>Academy</h6>
    <a href="<?= e(url('/admin/academy/courses')) ?>"        class="<?= is_active('/admin/academy/courses') ? 'is-active' : '' ?>">Courses</a>
    <a href="<?= e(url('/admin/academy/instructors')) ?>"    class="<?= is_active('/admin/academy/instructors') ? 'is-active' : '' ?>">Instructors</a>
    <a href="<?= e(url('/admin/academy/certifications')) ?>" class="<?= is_active('/admin/academy/certifications') ? 'is-active' : '' ?>">Certifications</a>
    <a href="<?= e(url('/admin/academy/enrollments')) ?>"    class="<?= is_active('/admin/academy/enrollments') ? 'is-active' : '' ?>">Enrolments</a>

    <h6>Account</h6>
    <form method="post" action="<?= e(url('/admin/logout')) ?>">
      <?= Csrf::field() ?>
      <button style="background:transparent;border:0;color:rgba(255,255,255,0.7);padding:10px 12px;text-align:left;width:100%;cursor:pointer;font-size:14px;">Sign out</button>
    </form>
  </aside>
  <section class="admin-main">
    <?= $bodyContent ?>
  </section>
</div>
<?php endif; ?>
<script src="<?= e(asset_v('js/app.js')) ?>" defer></script>
</body>
</html>
