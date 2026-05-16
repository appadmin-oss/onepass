<?php
/** @var array $services */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => 'Services',
    'title'        => 'Six capabilities. One studio.',
    'sub'          => 'From positioning to packaging to production, we run a senior studio against tight briefs. Strategy in week one. Deliverables shipping by week six.',
    'cta_label'    => 'Start a brief',
    'cta_href'     => url('/contact'),
    'illustration' => 'figure-laptop',
]]);
?>

<section data-reveal data-stagger="80" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:48px;">
  <?php
  $audiences = [
      ['title' => 'For Brands',    'sub' => 'Reposition with weight.',          'icon' => 'brand',    'href' => url('/services/brand-development')],
      ['title' => 'For Founders',  'sub' => 'Build the first version, right.',  'icon' => 'founders', 'href' => url('/services/digital-solutions')],
      ['title' => 'For Agencies',  'sub' => 'Senior overflow capacity.',        'icon' => 'media',    'href' => url('/contact')],
      ['title' => 'For Teams',     'sub' => 'Train, certify, retain.',          'icon' => 'teams',    'href' => url('/services/client-training')],
  ];
  foreach ($audiences as $a) partial('audience-tile', ['a' => $a]);
  ?>
</section>

<div class="cap-grid" data-stagger="80" data-reveal>
  <?php $i = 1; $total = count($services); foreach ($services as $service):
    $service['index'] = $i++;
    $service['total'] = $total;
    partial('service-card', ['service' => $service]);
  endforeach; ?>
</div>

<?php partial('cta-final'); ?>
