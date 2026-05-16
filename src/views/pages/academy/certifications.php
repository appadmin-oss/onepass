<?php
/** @var array $certs */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// CERTIFICATIONS',
    'title'        => 'Credentials our clients <em>hire against.</em>',
    'sub'          => 'Every certificate is mapped to a working role we hire for in the studio. Pass it, and we know what you can deliver.',
    'cta_label'    => 'See the tracks',
    'cta_href'     => '#tracks',
    'illustration' => 'figure-trophy',
    'variant'      => 'maroon',
]]);
?>
<a id="tracks" aria-hidden="true"></a>

<section data-reveal style="margin:24px 0 64px;">
  <div class="cert-panel cert-panel--split">
    <div>
      <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.7);">// FOUR TRACKS</div>
      <h2 class="ff-display" style="font-weight:700;font-size:clamp(36px,5vw,48px);line-height:0.95;margin:18px 0 24px;">
        Skill up.<br><em class="accent-italic" style="font-style:italic;color:#FCB7AB;">Move up.</em>
      </h2>
      <p style="font-size:14px;line-height:1.65;color:rgba(255,255,255,0.7);max-width:30ch;">
        Each track combines a course, a working portfolio piece, and a senior review. Pass all three and we issue a credential signed by the studio.
      </p>
    </div>
    <div>
      <?php foreach ($certs as $c) partial('cert-row', ['cert' => array_merge($c, ['href' => '#'])]); ?>
    </div>
  </div>
</section>

<section data-reveal style="margin:64px 0;">
  <h2 class="h1" style="margin:0 0 32px;max-width:22ch;">How a certification works.</h2>
  <ol data-stagger="80" class="process-grid">
    <?php $steps = [
      ['t' => 'Enrol on the course', 'd' => 'The cohort runs for 4–8 weeks live in the LMS.'],
      ['t' => 'Build the portfolio', 'd' => 'A piece of work tied to a real brief — yours, or a studio prompt.'],
      ['t' => 'Senior review',       'd' => 'Reviewed by a studio operator. Pass, fail, or one chance to revise.'],
      ['t' => 'Credential issued',   'd' => 'Verifiable certificate, listed on your profile, signed by Afrostrength.'],
    ]; foreach ($steps as $i => $s): ?>
      <li class="cap-tile" data-reveal style="background:var(--bone);">
        <div class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?> / 04</div>
        <h3 class="ff-display" style="font-weight:600;font-size:20px;line-height:1.2;margin:30px 0 8px;"><?= e($s['t']) ?></h3>
        <p class="body-m" style="color:rgba(10,10,10,0.7);"><?= e($s['d']) ?></p>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
