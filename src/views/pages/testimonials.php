<?php
/** @var array $testimonials */
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// IN THEIR WORDS',
    'title'        => 'The receipts. <em>In their words.</em>',
    'sub'          => 'Founders, MDs, and innovation leads on what shipping with the studio felt like.',
    'cta_label'    => 'Start a brief',
    'cta_href'     => url('/contact'),
    'illustration' => 'figure-trophy',
]]);
?>

<section class="hero-dark" data-reveal style="padding:64px 60px;margin:24px 0 0;">
  <span class="aurora" style="bottom:-180px;right:-100px;"></span>
  <div style="position:relative;z-index:2;">
    <div data-stagger="80" class="grid-cols-2">
      <?php foreach ($testimonials as $t) partial('testimonial-glass', ['t' => $t]); ?>
    </div>
  </div>
</section>

<?php partial('cta-final'); ?>
