<?php
/** @var array $opps */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// OPPORTUNITIES &amp; SCHOLARSHIPS',
    'title'        => 'Doors that <em>open.</em>',
    'sub'          => 'Active scholarships, fellowships, bursaries, hackathons and partner placements. Designed to lower the bar for the right people.',
    'cta_label'    => 'See active opportunities',
    'cta_href'     => '#opps',
    'illustration' => 'two-figures',
]]);
?>

<a id="opps" aria-hidden="true"></a>

<?php
function days_until($d) {
    $diff = (strtotime($d) - time()) / 86400;
    return (int) ceil($diff);
}
?>

<section data-stagger="80" style="margin:24px 0 32px;display:flex;flex-direction:column;gap:18px;">
  <?php foreach ($opps as $opp):
    $days = days_until($opp['deadline']);
    $urgent = $days <= 14;
  ?>
    <article data-reveal class="hairline" style="padding:28px 32px;border-radius:18px;border-left:4px solid <?= $urgent ? 'var(--crimson)' : 'var(--ink)' ?>;background:var(--surface);display:grid;grid-template-columns:1.4fr 1fr auto;gap:32px;align-items:center;">
      <div>
        <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:<?= $urgent ? 'var(--crimson)' : 'var(--text-dim)' ?>;text-transform:uppercase;">
          <?= e($opp['eyebrow']) ?>
        </div>
        <h2 class="ff-display" style="font-weight:600;font-size:24px;line-height:1.2;letter-spacing:-0.018em;margin:10px 0 8px;"><?= e($opp['title']) ?></h2>
        <p class="body-m" style="color:var(--text-mute);margin:0 0 14px;max-width:60ch;"><?= e($opp['summary']) ?></p>

        <details>
          <summary style="cursor:pointer;font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);list-style:none;">// Eligibility &amp; details</summary>
          <ul style="list-style:none;padding:12px 0 0;margin:0;display:grid;gap:6px;">
            <?php foreach ($opp['eligibility'] as $req): ?>
              <li style="display:flex;gap:10px;align-items:flex-start;font-size:14px;color:var(--text-mute);">
                <span style="color:var(--crimson);flex-shrink:0;line-height:1.5;"><?= icon('check', 14) ?></span>
                <span><?= e($req) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </details>
      </div>

      <div style="display:flex;flex-direction:column;gap:10px;border-left:1px solid var(--hairline);padding-left:24px;">
        <div>
          <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-dim);">Closes in</div>
          <div class="ff-display" style="font-weight:700;font-size:32px;line-height:1;letter-spacing:-0.02em;color:<?= $urgent ? 'var(--crimson)' : 'var(--text)' ?>;margin-top:4px;">
            <?= $days > 0 ? $days . ' days' : 'today' ?>
          </div>
        </div>
        <div>
          <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-dim);">Seats</div>
          <div class="ff-display" style="font-weight:600;font-size:18px;line-height:1;letter-spacing:-0.01em;margin-top:4px;"><?= e((string)$opp['seats']) ?></div>
        </div>
        <div>
          <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-dim);">Type</div>
          <div class="ff-display" style="font-weight:600;font-size:15px;line-height:1.2;margin-top:4px;"><?= e($opp['kind']) ?></div>
        </div>
      </div>

      <a class="btn btn-primary" href="<?= e($opp['cta_href']) ?>" style="white-space:nowrap;align-self:start;">
        <?= e($opp['cta']) ?> <?= icon_chev() ?>
      </a>
    </article>
  <?php endforeach; ?>
</section>

<style>
  @media (max-width: 900px) {
    section[data-stagger="80"] article { grid-template-columns: 1fr !important; gap: 18px !important; }
    section[data-stagger="80"] article > div:nth-child(2) { border-left: 0 !important; padding-left: 0 !important; border-top: 1px solid var(--hairline); padding-top: 16px !important; flex-direction: row !important; justify-content: space-between; }
  }
</style>

<?php partial('promo-academy'); ?>

<section data-reveal style="margin:32px 0 64px;">
  <h2 class="h2" style="margin:0 0 16px;">Want to sponsor an opportunity?</h2>
  <p class="body-l" style="color:var(--text-mute);max-width:60ch;margin:0 0 18px;">
    Corporates, alumni and foundations partner with Afrostrength to fund seats and place graduates.
    Tell us what you'd like to back — full track tuition, hackathon prizes, or year-long mentorship.
  </p>
  <a class="btn btn-ghost" href="<?= e(url('/contact')) ?>">Talk to the studio <?= icon_chev() ?></a>
</section>
