<?php
/** @var array $service Expects: name, slug, tagline, icon, index, total, timeline */
$service = $service ?? [];
$idx = str_pad((string)($service['index'] ?? 1), 2, '0', STR_PAD_LEFT);
$tot = str_pad((string)($service['total'] ?? 6), 2, '0', STR_PAD_LEFT);
$timeline = $service['timeline'] ?? '4–6 WEEKS';
$tag      = $service['tag'] ?? 'CORE';
?>
<a href="<?= e(url('/services/' . ($service['slug'] ?? ''))) ?>" class="cap-tile" data-reveal>
  <div class="cap-tile__head">
    <span class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(10,10,10,0.6);">
      <?= e($idx) ?> / <?= e($tot) ?>
    </span>
    <span class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);">
      <?= e($tag) ?>
    </span>
  </div>
  <h3 class="cap-tile__title"><?= e($service['name'] ?? '') ?></h3>
  <p class="cap-tile__desc"><?= e($service['tagline'] ?? '') ?></p>
  <div class="cap-tile__foot ff-mono" style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(10,10,10,0.55);">
    // AVG TIMELINE — <?= e($timeline) ?>
  </div>
</a>
