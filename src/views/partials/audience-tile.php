<?php
/** @var array $a Expects: title, sub, icon */
require_once AFS_ROOT . '/src/views/partials/icons.php';
$a = $a ?? [];
?>
<a href="<?= e($a['href'] ?? '#') ?>" class="audience-tile" data-reveal>
  <span style="color:var(--crimson);"><?= icon($a['icon'] ?? 'brand', 22) ?></span>
  <div>
    <div class="audience-tile__title"><?= e($a['title'] ?? '') ?></div>
    <div class="audience-tile__sub"><?= e($a['sub'] ?? '') ?></div>
  </div>
</a>
