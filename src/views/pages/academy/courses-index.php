<?php
/** @var array $courses
 *  @var bool  $lmsEnabled
 */
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// COURSES',
    'title'        => 'Cohort-based. <em>Operator-led.</em>',
    'sub'          => 'Each course is taught live by senior studio operators. Small groups. Real briefs. Working software you keep.',
    'cta_label'    => 'Browse the catalog',
    'cta_href'     => '#catalog',
    'illustration' => 'figure-document',
    'variant'      => 'maroon',
]]);
?>
<a id="catalog" aria-hidden="true"></a>
<?php if ($lmsEnabled): ?>
  <p class="ff-mono" style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);margin:0 0 32px;">
    // CATALOG SOURCE: Moodle LMS — <?= e(LMS_BASE_URL) ?>
  </p>
<?php endif; ?>

<?php if (!empty($courses)): ?>
<section data-stagger="80" data-reveal class="grid-cols-3">
  <?php foreach ($courses as $c) partial('course-card', ['c' => $c]); ?>
</section>
<?php else: ?>
<section data-reveal style="text-align:center;padding:80px 0;">
  <h2 class="h2">Catalog opening soon.</h2>
</section>
<?php endif; ?>

<?php partial('cert-panel'); ?>
