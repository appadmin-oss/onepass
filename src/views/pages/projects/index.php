<?php
/** @var array $projects */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
$featured = array_slice(array_filter($projects, fn($p) => !empty($p['is_featured'])), 0, 3);
$rest     = array_filter($projects, fn($p) => empty($p['is_featured']));

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// PROJECTS',
    'title'        => 'Work that <em>speaks.</em>',
    'sub'          => 'Brands you can name. Outcomes you can measure. Each project carries the weight of its vision.',
    'cta_label'    => 'See the case studies',
    'cta_href'     => '#work',
    'illustration' => 'figure-camera',
]]);
?>
<a id="work" aria-hidden="true"></a>

<?php if (!empty($featured)): ?>
<section data-stagger="100" data-reveal style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:64px;">
  <?php foreach ($featured as $project) partial('project-card', ['project' => $project]); ?>
</section>
<?php endif; ?>

<?php if (!empty($rest) || !empty($projects)): ?>
<section style="margin-top:32px;">
  <div class="eyebrow">THE FULL CATALOG</div>
  <div class="project-rows" style="margin-top:24px;">
    <?php $i = 1; foreach (($rest ?: $projects) as $project):
      $project['index'] = $i++;
      partial('project-row', ['project' => $project]);
    endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (empty($projects)): ?>
<section data-reveal style="text-align:center;padding:80px 0;">
  <div data-empty-state style="width:240px;height:240px;margin:0 auto 24px;">
    <?php illustration('empty-state'); ?>
  </div>
  <h2 class="h2" style="margin-bottom:12px;">Projects loading shortly.</h2>
  <p class="body-l" style="color:rgba(10,10,10,0.6);max-width:50ch;margin:0 auto;">
    We're between portfolio updates. <a class="nav-link" href="<?= e(url('/contact')) ?>" style="color:var(--crimson);">Start a brief</a> in the meantime.
  </p>
</section>
<?php endif; ?>

<?php partial('cta-final'); ?>
