<?php
/** @var array $operators */
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// TEAM',
    'title'        => 'Operators. <em>Not interns.</em>',
    'sub'          => 'Senior throughout. Every member of the team has shipped under real-world pressure — brand, product, film, event.',
    'cta_label'    => 'Work with us',
    'cta_href'     => url('/contact'),
    'illustration' => 'two-figures',
]]);
?>

<section data-reveal data-stagger="80" class="grid-cols-3">
  <?php foreach ($operators as $op) partial('operator-card', ['op' => $op]); ?>
</section>

<?php partial('cta-final'); ?>
