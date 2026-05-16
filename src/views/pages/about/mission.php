<?php
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// MISSION & VISION',
    'title'        => 'To strengthen the legacies of <em>African brands.</em>',
    'sub'          => 'Empower businesses and individuals through branding, design, media, and project execution that compounds over time. Every brief carries the weight of a real vision — and a real outcome.',
    'cta_label'    => 'See our methodology',
    'cta_href'     => url('/about/methodology'),
    'illustration' => 'figure-trophy',
]]);
?>

<section data-reveal class="grid-split" style="padding:48px 0;border-top:1px solid var(--hairline);">
  <div>
    <div class="eyebrow">MISSION</div>
    <p class="body-l" style="margin-top:16px;color:rgba(10,10,10,0.85);">
      Empower businesses and individuals through branding, design, media, and project execution that compounds over time. Every brief we take on carries the weight of a real vision — and a real outcome.
    </p>
  </div>
  <div>
    <div class="eyebrow">VISION</div>
    <p class="body-l" style="margin-top:16px;color:rgba(10,10,10,0.85);">
      A continent of brands that hold up under pressure. Recognisable from a billboard in Lagos, a shelf in Ibadan, or a screen anywhere on the continent. Built to last. Made to move.
    </p>
  </div>
</section>

<section data-reveal style="margin:64px 0;">
  <div class="eyebrow">PRINCIPLES</div>
  <h2 class="h1" style="margin:12px 0 32px;">Five things we don't compromise on.</h2>
  <ol class="principles-grid" data-stagger="80">
    <?php $principles = [
      ['t' => 'Senior throughout',         'd' => 'No junior teams running on senior budgets. Every brief gets people who have shipped.'],
      ['t' => 'Strategy first',            'd' => 'We compress the discovery into a tight, intentional intensive — never skip it.'],
      ['t' => 'Things that ship',          'd' => 'We build working systems and shipped objects. Not decks. Not theatre.'],
      ['t' => 'Direct communication',      'd' => 'If a brief can\'t be done in twelve weeks, we say so on the call.'],
      ['t' => 'Operators first',           'd' => 'Everyone here has shipped under pressure. We hire for that.'],
      ['t' => 'African by design',         'd' => 'Our work belongs to and stands for the brands shaping this continent.'],
    ]; foreach ($principles as $i => $p): ?>
      <li data-reveal style="padding:24px 4px;border-bottom:1px solid var(--hairline);<?= ($i % 2 === 0) ? 'border-right:1px solid var(--hairline);padding-right:24px;' : 'padding-left:24px;' ?>">
        <div class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);margin-bottom:8px;"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></div>
        <div class="ff-display" style="font-weight:600;font-size:22px;line-height:1.2;"><?= e($p['t']) ?></div>
        <p class="body-m" style="color:rgba(10,10,10,0.7);margin:8px 0 0;"><?= e($p['d']) ?></p>
      </li>
    <?php endforeach; ?>
  </ol>
</section>

<?php partial('cta-final'); ?>
