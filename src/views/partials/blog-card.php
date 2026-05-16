<?php
/** @var array $post */
$post = $post ?? [];
?>
<a href="<?= e(url('/blog/' . ($post['slug'] ?? ''))) ?>" class="course-card" data-reveal style="text-decoration:none;color:inherit;">
  <div class="course-card__cover placeholder" style="border-radius:0;">// <?= e(strtoupper($post['category'] ?? 'Field notes')) ?></div>
  <div class="course-card__body">
    <div class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);">
      <?= e(strtoupper($post['category'] ?? 'Editorial')) ?> · <?= e(read_time($post['body'] ?? $post['excerpt'] ?? '')) ?>
    </div>
    <h4 class="ff-display" style="font-weight:600;font-size:19px;line-height:1.2;margin:8px 0;"><?= e($post['title'] ?? '') ?></h4>
    <p class="caption" style="margin:6px 0 14px;"><?= e(excerpt($post['excerpt'] ?? $post['body'] ?? '', 22)) ?></p>
    <div style="display:flex;align-items:center;justify-content:space-between;">
      <span class="nav-link" style="color:var(--crimson);">Read more →</span>
      <span class="ff-mono" style="font-size:11px;color:rgba(10,10,10,0.55);"><?= e(date_pretty($post['published_at'] ?? $post['created_at'] ?? date('c'))) ?></span>
    </div>
  </div>
</a>
