<?php
/** @var array $instructors */
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// INSTRUCTORS',
    'title'        => 'The team that <em>teaches.</em>',
    'sub'          => 'Every member of the Academy faculty is also a working studio operator. The curriculum reflects what they actually do.',
    'cta_label'    => 'Browse courses',
    'cta_href'     => url('/academy/courses'),
    'illustration' => 'two-figures',
    'variant'      => 'maroon',
]]);
?>

<section data-stagger="80" data-reveal class="grid-cols-3">
  <?php foreach ($instructors as $op) partial('operator-card', ['op' => $op]); ?>
</section>

<?php partial('cert-panel'); ?>
