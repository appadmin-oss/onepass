<?php
/** @var array $op */
$op = $op ?? [];
$pillCls   = ($op['status_pill'] ?? 'live') === 'live' ? 'pill pill-live' : 'pill pill-prod';
$pillBody  = ($op['status_pill'] ?? 'live') === 'live'
    ? '<span class="dot dot-green"></span>Available'
    : '<span class="dot dot-amber"></span>' . e($op['status_label'] ?? 'Booked');
$skills = $op['skills'] ?? [];
if (is_string($skills)) { $skills = array_filter(array_map('trim', explode(',', $skills))); }
?>
<div class="operator-card" data-reveal>
  <div class="op-portrait" style="margin-bottom:18px;">
    <?php if (!empty($op['avatar'])): ?>
      <img src="<?= e($op['avatar']) ?>" alt="<?= e($op['name'] ?? '') ?>" loading="lazy">
    <?php endif; ?>
  </div>
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:12px;">
    <div>
      <div class="ff-display" style="font-weight:600;font-size:18px;"><?= e($op['name'] ?? '') ?></div>
      <div class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);margin-top:4px;">
        <?= e($op['role'] ?? '') ?>
      </div>
    </div>
    <span class="<?= e($pillCls) ?>"><?= $pillBody ?></span>
  </div>
  <div style="display:flex;flex-wrap:wrap;gap:6px;">
    <?php foreach ($skills as $skill): ?>
      <span class="ff-mono" style="font-size:10px;letter-spacing:0.10em;text-transform:uppercase;padding:5px 10px;border:1px solid var(--hairline);border-radius:999px;"><?= e($skill) ?></span>
    <?php endforeach; ?>
  </div>
</div>
