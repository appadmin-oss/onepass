<?php
/** @var string $code */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<section data-reveal style="text-align:center;padding:64px 24px;max-width:520px;margin:24px auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// SHORT LINK · NOT FOUND</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">
    <code style="background:rgba(192,57,43,0.08);color:var(--crimson);padding:4px 10px;border-radius:6px;font-family:'JetBrains Mono',monospace;font-size:0.6em;">/s/<?= e($code) ?></code> isn't <em class="grad-text">on file.</em>
  </h1>
  <p class="body-l" style="color:var(--text-mute);margin:8px auto 28px;">
    The link might have expired, been removed, or never existed. You can create your own short link in a few seconds.
  </p>
  <a href="<?= e(url('/tools/short-link')) ?>" class="btn btn-primary">Make a new short link <?= icon_chev() ?></a>
</section>

<?php partial('promo-academy'); ?>
