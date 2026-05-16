<?php
/** @var array $project */
$project = $project ?? [];
$cover   = $project['featured_image'] ?? '';
?>
<a href="<?= e(url('/projects/' . ($project['slug'] ?? ''))) ?>" class="project-card" data-reveal>
  <div class="project-card__media">
    <?php if ($cover): ?>
      <img src="<?= e($cover) ?>" alt="<?= e($project['title'] ?? '') ?>" loading="lazy">
    <?php else: ?>
      <div class="placeholder">// <?= e(strtoupper($project['title'] ?? 'Project')) ?></div>
    <?php endif; ?>
  </div>
  <div class="project-card__body">
    <div class="project-card__tags"><?= e($project['tags'] ?? '') ?></div>
    <h3 class="project-card__title"><?= e($project['title'] ?? '') ?></h3>
    <div class="caption"><?= e($project['client'] ?? '') ?> · <?= e((string)($project['year'] ?? '')) ?></div>
  </div>
</a>
