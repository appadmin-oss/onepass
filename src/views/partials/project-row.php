<?php
/** @var array $project */
require_once AFS_ROOT . '/src/views/partials/icons.php';
$project = $project ?? [];
$status  = $project['status_label'] ?? 'live';
$pillCls = match ($status) {
    'live'       => 'pill pill-live',
    'production' => 'pill pill-prod',
    default      => 'pill pill-case',
};
$pillDot = match ($status) {
    'live'       => '<span class="dot dot-green"></span>Live',
    'production' => '<span class="dot dot-amber"></span>In Production',
    default      => 'Case Study',
};
$num = str_pad((string)($project['index'] ?? 1), 2, '0', STR_PAD_LEFT);
?>
<a href="<?= e(url('/projects/' . ($project['slug'] ?? ''))) ?>" class="project-row" data-reveal>
  <div class="project-row__num"><?= e($num) ?></div>
  <div>
    <div class="project-row__title"><?= e($project['title'] ?? '') ?></div>
    <div class="project-row__tags"><?= e($project['tags'] ?? '') ?></div>
  </div>
  <div class="project-row__client"><?= e($project['client'] ?? '') ?></div>
  <div class="project-row__year ff-mono" style="font-size:12px;color:rgba(10,10,10,0.7);"><?= e((string)($project['year'] ?? '')) ?></div>
  <div class="project-row__status"><span class="<?= e($pillCls) ?>"><?= $pillDot ?></span></div>
  <div class="project-row__arrow"><?= icon('arrow-out', 22) ?></div>
</a>
