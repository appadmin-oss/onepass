<?php
/** @var array $posts
 *  @var array $categories
 */
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// FIELD NOTES',
    'title'        => 'Editorial. <em>Direct from the studio.</em>',
    'sub'          => 'One letter a month. Project breakdowns, hiring notes, and the occasional studio essay.',
    'cta_label'    => 'Subscribe',
    'cta_href'     => '#newsletter',
    'illustration' => 'figure-document',
]]);
?>

<?php if (!empty($categories)): ?>
<nav data-reveal style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px;" aria-label="Categories">
  <button class="btn btn-ghost btn-sm">All</button>
  <?php foreach ($categories as $cat): ?>
    <button class="btn btn-ghost btn-sm"><?= e($cat) ?></button>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<?php if (!empty($posts)): ?>
<section data-stagger="80" data-reveal class="grid-cols-3">
  <?php foreach ($posts as $post) partial('blog-card', ['post' => $post]); ?>
</section>
<?php else: ?>
<section data-reveal style="text-align:center;padding:80px 0;">
  <div data-empty-state style="width:240px;height:240px;margin:0 auto 24px;">
    <?php illustration('empty-state'); ?>
  </div>
  <h2 class="h2">No posts yet.</h2>
  <p class="body-l" style="color:rgba(10,10,10,0.6);">The first issue lands soon.</p>
</section>
<?php endif; ?>

<?php partial('cta-final'); ?>
