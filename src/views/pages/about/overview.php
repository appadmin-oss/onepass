<?php
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// ABOUT',
    'title'        => 'A studio built like <em>an operator.</em>',
    'sub'          => 'A small, senior studio working from two Lagos branches — CACENTRE Ayobo and CACENTRE Egbeda. We deploy operator-grade teams against tight briefs — strategy in week one, deliverables shipping by week six.',
    'cta_label'    => 'Meet the operators',
    'cta_href'     => url('/about/team'),
    'illustration' => 'two-figures',
]]);
?>

<nav data-reveal style="display:flex;flex-wrap:wrap;gap:8px;margin:0 0 56px;border-top:1px solid var(--hairline);padding-top:16px;">
  <a class="nav-link" href="<?= e(url('/about/overview')) ?>"    data-href="/about/overview">Overview</a>
  <span style="color:var(--hairline);padding:0 12px;">·</span>
  <a class="nav-link" href="<?= e(url('/about/mission')) ?>"     data-href="/about/mission">Mission &amp; Vision</a>
  <span style="color:var(--hairline);padding:0 12px;">·</span>
  <a class="nav-link" href="<?= e(url('/about/team')) ?>"        data-href="/about/team">Team</a>
  <span style="color:var(--hairline);padding:0 12px;">·</span>
  <a class="nav-link" href="<?= e(url('/about/methodology')) ?>" data-href="/about/methodology">Methodology</a>
</nav>

<section data-reveal style="margin:48px 0;">
  <div class="eyebrow">WHO WE ARE</div>
  <p class="body-l" style="margin-top:16px;color:var(--text);max-width:68ch;">
    Afrostrength is a dynamic, full-service creative and technology organisation specialising in
    <strong>Digital Design &amp; Branding</strong>, <strong>Development</strong>, and <strong>Production</strong>.
    We are the convergence point of Art, Media, and Technology — three disciplines that, when unified,
    create brands that don't just compete but <em class="accent-italic">dominate</em>.
  </p>
  <p class="body-l" style="margin-top:16px;color:var(--text-mute);max-width:68ch;">
    Driven by the philosophy of creating timeless legacies, we don't merely build brands —
    we engineer enduring identities that command reverence long after the first impression.
    Whether crafting a visual identity, building a digital product, or producing compelling media,
    our standard is singular: exceptional quality that stands the test of time.
  </p>
</section>

<!-- Pull quote — uses brand gradient on dark -->
<section data-reveal class="grad-ink" style="border-radius:18px;padding:48px 40px;margin:48px 0;position:relative;overflow:hidden;">
  <span class="aurora" style="top:-160px;right:-100px;"></span>
  <div style="position:relative;z-index:2;max-width:60ch;">
    <div class="ff-display" style="font-weight:700;font-size:64px;line-height:0.6;color:var(--crimson);margin-bottom:-16px;">"</div>
    <p class="ff-display" style="font-weight:500;font-style:italic;font-size:clamp(20px,2.4vw,28px);line-height:1.35;letter-spacing:-0.01em;color:#fff;margin:0;">
      We create from the heart of impact, preservation and long reign. With Afrostrength, you are not just building a brand — you are <span class="grad-text">strengthening a legacy</span>.
    </p>
  </div>
</section>

<section data-reveal class="grid-split" style="margin:48px 0;">
  <div>
    <div class="eyebrow">OUR MISSION</div>
    <p class="body-l" style="margin-top:16px;color:var(--text);">
      To deliver exceptional quality, innovation and creativity in Design, Development and Production —
      empowering businesses to build brands that leave an indelible mark on their audiences and the world.
    </p>
  </div>
  <div>
    <div class="eyebrow">OUR PHILOSOPHY</div>
    <p class="body-l" style="margin-top:16px;color:var(--text);">
      Time is the ultimate test of a great brand. Every pixel, every line of code, every frame produced is
      guided by one question: <em class="accent-italic">Will this still matter in ten years?</em>
    </p>
  </div>
</section>

<!-- Track record — gradient cards -->
<section data-reveal data-stagger="80" class="grid-cols-3" style="margin:80px 0;">
  <?php $stats = [
    ['num' => '5+',  'label' => 'Years of experience'],
    ['num' => '50+', 'label' => 'Global &amp; national clients'],
    ['num' => '50+', 'label' => 'Completed projects'],
  ]; foreach ($stats as $s): ?>
    <div data-reveal class="grad-ink" style="padding:36px;border-radius:14px;position:relative;overflow:hidden;">
      <span class="aurora" style="top:-100px;right:-80px;opacity:0.25;"></span>
      <div class="ff-display" style="font-weight:700;font-size:64px;line-height:1;letter-spacing:-0.025em;position:relative;z-index:1;" class="grad-text">
        <span class="grad-text"><?= $s['num'] ?></span>
      </div>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.55);margin-top:14px;position:relative;z-index:1;"><?= $s['label'] ?></div>
    </div>
  <?php endforeach; ?>
</section>

<!-- Why partner -->
<section data-reveal style="margin:80px 0;">
  <h2 class="h1" style="margin:0 0 32px;max-width:24ch;">Why partner with <em class="accent-italic">Afrostrength?</em></h2>
  <div class="grid-cols-2" style="gap:32px;">
    <?php $why = [
      ['t' => 'End-to-end creative partner', 'd' => 'Design, development and production under one roof. No fragmented handoffs, no lost context — just seamless execution from concept to delivery.'],
      ['t' => 'National &amp; global reach',     'd' => 'We have partnered with local startups and international enterprises alike, bringing cultural depth and global perspective to every project.'],
      ['t' => 'Legacy-first thinking',        'd' => "We don't optimise for today's trend. We build for enduring relevance — brands and digital products that grow stronger with time."],
      ['t' => 'Quality without compromise',   'd' => 'From our first project to our fiftieth, our benchmark has never shifted: exceptional quality, innovative solutions, and measurable impact.'],
    ]; foreach ($why as $w): ?>
      <div data-reveal class="hairline" style="padding:28px;border-radius:14px;border-left:3px solid var(--crimson);">
        <h3 class="ff-display" style="font-weight:600;font-size:20px;letter-spacing:-0.015em;margin:0 0 10px;"><?= $w['t'] ?></h3>
        <p class="body-m" style="color:var(--text-mute);margin:0;"><?= $w['d'] ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Meet the team — real operators, real bylines -->
<section data-reveal style="margin:80px 0 64px;">
  <div class="eyebrow">MEET THE TEAM</div>
  <div style="display:grid;grid-template-columns:1fr auto;gap:24px;align-items:end;margin:8px 0 28px;flex-wrap:wrap;">
    <h2 class="h1" style="margin:0;max-width:22ch;">Small, senior, and <em class="accent-italic">on the ground.</em></h2>
    <a class="nav-link" href="<?= e(url('/about/team')) ?>" style="font-size:14px;">All operators →</a>
  </div>

  <div class="grid-cols-3" style="gap:18px;" data-stagger="80">
    <?php
    $team = [
      [
        'name'  => 'Adaeze Okeke',
        'role'  => 'Lead Brand Strategist',
        'bio'   => 'Twelve years across studios in Lagos and London. Naming, voice, identity.',
        'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&q=80',
        'links' => ['LinkedIn' => '#', 'X' => '#'],
      ],
      [
        'name'  => 'Tope Adeyemi',
        'role'  => 'Creative Director',
        'bio'   => 'Editorial and design systems. Type-led, print-rooted, screen-fluent.',
        'photo' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=600&q=80',
        'links' => ['LinkedIn' => '#', 'Behance' => '#'],
      ],
      [
        'name'  => 'Kemi Balogun',
        'role'  => 'Head of Production',
        'bio'   => 'Film, events, logistics. Two thousand attendees, zero compromises.',
        'photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=600&q=80',
        'links' => ['LinkedIn' => '#', 'Instagram' => '#'],
      ],
    ];
    foreach ($team as $p): ?>
      <article class="hairline team-card" data-reveal>
        <div class="team-card__media" style="background-image:url('<?= e($p['photo']) ?>');"></div>
        <div style="padding:18px 20px;">
          <div class="ff-display" style="font-weight:600;font-size:18px;letter-spacing:-0.01em;"><?= e($p['name']) ?></div>
          <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);margin-top:4px;"><?= e($p['role']) ?></div>
          <p class="caption" style="margin:10px 0 12px;line-height:1.5;color:var(--text-mute);"><?= e($p['bio']) ?></p>
          <div style="display:flex;gap:10px;">
            <?php foreach ($p['links'] as $label => $href): ?>
              <a class="ff-mono" href="<?= e($href) ?>" style="font-size:10px;letter-spacing:0.16em;text-transform:uppercase;color:var(--text-dim);text-decoration:none;border-bottom:1px solid var(--hairline);padding-bottom:2px;"><?= e($label) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<style>
  .team-card { border-radius: 16px; overflow: hidden; transition: transform .3s var(--ease-out), border-color .25s ease; }
  .team-card:hover { transform: translateY(-3px); border-color: var(--ink); }
  .team-card__media { aspect-ratio: 4/5; background-size: cover; background-position: center; background-color: var(--bg-soft); }
</style>

<?php partial('cta-final'); ?>
