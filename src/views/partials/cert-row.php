<?php
/** @var array $cert */
require_once AFS_ROOT . '/src/views/partials/icons.php';
$cert = $cert ?? [];
?>
<a href="<?= e($cert['href'] ?? '#') ?>" class="cert-row" data-reveal>
  <span class="cert-row__title"><?= e($cert['title'] ?? '') ?></span>
  <span class="arrow-circle">
    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="#fff" stroke-width="1.6" aria-hidden="true">
      <path d="M3 11L11 3M5 3h6v6"/>
    </svg>
  </span>
</a>
