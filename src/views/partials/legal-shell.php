<?php
/**
 * Wrapper rhythm + side-nav used by every legal page.
 *
 * @var string $heading       Page H1
 * @var string $effective     "Effective: 14 May 2026" or similar
 * @var array  $sections      [['id' => slug, 'h' => "Heading", 'body' => "html or text"]]
 * @var array  $breadcrumbs
 * @var string $intro         Optional standalone intro paragraph
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<header data-reveal style="margin:24px 0 32px;max-width:760px;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// LEGAL · AFROSTRENGTH LIMITED</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;"><?= e($heading) ?></h1>
  <p class="caption" style="margin:0 0 18px;"><?= e($effective ?? '') ?></p>
  <?php if (!empty($intro)): ?>
    <p class="body-l" style="color:var(--text-mute);max-width:62ch;"><?= $intro ?></p>
  <?php endif; ?>
</header>

<section style="display:grid;grid-template-columns:220px 1fr;gap:48px;margin-bottom:96px;" data-reveal>
  <aside style="position:sticky;top:96px;align-self:start;">
    <div class="eyebrow" style="margin-bottom:14px;">// CONTENTS</div>
    <ol style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;font-size:13px;">
      <?php foreach ($sections as $i => $sec): ?>
        <li><a href="#<?= e($sec['id']) ?>" class="nav-link" style="font-size:13px;display:block;">
          <?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?> · <?= e($sec['h']) ?>
        </a></li>
      <?php endforeach; ?>
    </ol>
    <div style="margin-top:32px;padding-top:18px;border-top:1px solid var(--hairline);">
      <div class="eyebrow" style="margin-bottom:10px;">// OTHER LEGAL</div>
      <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px;font-size:13px;">
        <li><a class="nav-link" href="<?= e(url('/legal/privacy')) ?>" style="font-size:13px;">Privacy Policy</a></li>
        <li><a class="nav-link" href="<?= e(url('/legal/terms')) ?>" style="font-size:13px;">Terms of Service</a></li>
        <li><a class="nav-link" href="<?= e(url('/legal/cookies')) ?>" style="font-size:13px;">Cookie Policy</a></li>
        <li><a class="nav-link" href="<?= e(url('/legal/acceptable-use')) ?>" style="font-size:13px;">Acceptable Use</a></li>
        <li><a class="nav-link" href="<?= e(url('/legal/imprint')) ?>" style="font-size:13px;">Imprint</a></li>
      </ul>
    </div>
  </aside>

  <article class="prose" style="max-width:70ch;">
    <?php foreach ($sections as $i => $sec): ?>
      <section id="<?= e($sec['id']) ?>" style="margin-bottom:40px;">
        <div class="ff-mono" style="font-size:11px;letter-spacing:0.18em;color:var(--crimson);text-transform:uppercase;margin-bottom:8px;">
          // <?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?>
        </div>
        <h2 style="font-family:'Garet',sans-serif;font-weight:700;font-size:24px;letter-spacing:-0.02em;margin:0 0 14px;line-height:1.2;"><?= e($sec['h']) ?></h2>
        <div><?= $sec['body'] ?></div>
      </section>
    <?php endforeach; ?>

    <hr style="border:0;border-top:1px solid var(--hairline);margin:32px 0;">

    <p style="font-size:14px;color:var(--text-mute);">
      Questions or a formal data-subject request?
      Email <a class="nav-link" style="color:var(--crimson);font-size:14px;" href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a>
      with the subject line <strong>“Legal — &lt;your topic&gt;”</strong> and we'll respond within 7 working days.
    </p>
  </article>
</section>

<style>
  @media (max-width: 900px) {
    section[data-reveal][style*="grid-template-columns:220px 1fr"] {
      grid-template-columns: 1fr !important;
    }
    section[data-reveal] aside { position: static !important; }
  }
</style>
