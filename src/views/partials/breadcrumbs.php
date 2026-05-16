<?php
/** @var array $breadcrumbs */
if (empty($breadcrumbs)) return;
?>
<nav class="ff-mono" style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.6);margin-bottom:16px;" aria-label="Breadcrumbs">
  <?php foreach ($breadcrumbs as $i => $crumb):
    $isLast = $i === array_key_last($breadcrumbs); ?>
    <?php if (!$isLast && !empty($crumb['href'])): ?>
      <a href="<?= e($crumb['href']) ?>"><?= e($crumb['label']) ?></a> <span style="color:var(--crimson);">/</span>
    <?php else: ?>
      <span style="color:var(--ink);"><?= e($crumb['label']) ?></span>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
