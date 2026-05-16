<?php
/** @var array $c */
$c = $c ?? [];
?>
<a href="<?= e(url('/academy/courses/' . ($c['slug'] ?? ''))) ?>" class="course-card" data-reveal style="text-decoration:none;color:inherit;display:block;">
  <div class="course-card__cover placeholder" style="border-radius:0;">// COURSE COVER</div>
  <div class="course-card__body">
    <div class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);">
      <?= e(strtoupper($c['level'] ?? 'FOUNDATIONS')) ?> · <?= e((string)($c['weeks'] ?? 6)) ?> WEEKS
    </div>
    <h4 class="ff-display" style="font-weight:600;font-size:19px;line-height:1.2;margin:8px 0 4px;"><?= e($c['title'] ?? '') ?></h4>
    <div class="caption">w/ <?= e($c['instructor'] ?? '') ?></div>
    <div style="margin-top:14px;display:flex;align-items:center;justify-content:space-between;">
      <span class="nav-link" style="color:var(--crimson);">Enroll →</span>
      <span class="ff-mono" style="font-size:11px;color:rgba(10,10,10,0.55);">₦ <?= number_format((float)($c['price_naira'] ?? 0)) ?></span>
    </div>
  </div>
</a>
