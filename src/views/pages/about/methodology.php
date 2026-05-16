<?php
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// METHODOLOGY',
    'title'        => 'A method that <em>ships.</em>',
    'sub'          => 'Strategy in week one. Working drafts every week. Final delivery by week six. Repeat for media, project, and digital.',
    'cta_label'    => 'Start a brief',
    'cta_href'     => url('/contact'),
    'illustration' => 'figure-laptop',
]]);
?>

<section class="hero-dark" data-reveal style="padding:80px 60px;margin:48px 0 0;">
  <span class="aurora" style="top:-200px;left:30%;animation-delay:-2s;"></span>
  <div style="position:relative;z-index:2;">
    <div class="process" data-process-connector>
      <?php
      $steps = [
        ['num' => '01', 'title' => 'Brief',     'desc' => 'A working session that locks the scope. We come out with a one-page brief everyone signs.'],
        ['num' => '02', 'title' => 'Strategy',  'desc' => 'Senior-led intensive. Positioning, audience, voice. Compressed, not skipped.'],
        ['num' => '03', 'title' => 'Build',     'desc' => 'Identity, design, media, code. Working software shipped weekly.'],
        ['num' => '04', 'title' => 'Ship',      'desc' => 'Final delivery, guidelines, walkthrough. Then we keep the lights on.'],
        ['num' => '05', 'title' => 'Iterate',   'desc' => 'Optional 30/60/90 reviews to tune the brand once it\'s in the wild.'],
      ];
      foreach ($steps as $i => $step): $alt = $i % 2 === 1; ?>
        <div class="process__step <?= $alt ? 'process__step--alt' : '' ?>" data-reveal>
          <div class="process__copy">
            <span class="process__num"><?= e($step['num']) ?></span>
            <h3 class="process__title"><?= e($step['title']) ?></h3>
            <p><?= e($step['desc']) ?></p>
          </div>
          <div class="process__art">
            <div class="placeholder placeholder--dark">// <?= e(strtoupper($step['title'])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php partial('cta-final'); ?>
