<?php
/** @var array $t */
$t = $t ?? [];
?>
<div class="glass" data-reveal>
  <div class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.7);margin-bottom:12px;">
    <?= e($t['company'] ?? '') ?>
  </div>
  <p class="ff-body" style="font-size:15px;line-height:1.55;margin:0;">
    <?= $t['quote_html'] ?? e($t['quote'] ?? '') ?>
  </p>
  <div class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.55);margin-top:18px;">
    — <?= e($t['client_name'] ?? '') ?><?= !empty($t['role']) ? ', ' . e($t['role']) : '' ?>
  </div>
</div>
