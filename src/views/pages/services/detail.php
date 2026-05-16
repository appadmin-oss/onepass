<?php
/** @var array $service
 *  @var array $related
 *  @var array $projects
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

$process      = $service['process']      ?? [];
$deliverables = $service['deliverables'] ?? [];
?>

<!-- Service hero -->
<section data-reveal style="padding:48px 0 56px;border-bottom:1px solid var(--hairline);">
  <div class="ff-mono" style="font-size:12px;letter-spacing:0.18em;color:var(--crimson);text-transform:uppercase;">
    // <?= e($service['tag'] ?? 'CORE') ?> · <?= e($service['timeline'] ?? '4–6 WEEKS') ?>
  </div>
  <h1 class="h-display-2" style="margin:16px 0 16px;max-width:22ch;">
    <?= e($service['name']) ?> <em class="accent-italic">built for ambition.</em>
  </h1>
  <p class="body-l" style="max-width:62ch;color:rgba(10,10,10,0.7);">
    <?= e($service['tagline'] ?? '') ?>
  </p>
  <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:28px;">
    <a href="<?= e(url('/contact')) ?>" class="btn btn-primary">Start a brief <?= icon_chev() ?></a>
    <a href="<?= e(url('/projects')) ?>" class="btn btn-ghost">See related work</a>
  </div>
</section>

<!-- Overview + who it's for -->
<section data-reveal class="grid-split" style="padding:64px 0;border-bottom:1px solid var(--hairline);">
  <div>
    <div class="eyebrow">OVERVIEW</div>
    <p class="body-l" style="margin-top:16px;max-width:58ch;color:rgba(10,10,10,0.85);">
      <?= e($service['overview'] ?? '') ?>
    </p>
  </div>
  <div>
    <div class="eyebrow">WHO IT'S FOR</div>
    <p class="body-l" style="margin-top:16px;max-width:58ch;color:rgba(10,10,10,0.85);">
      <?= e($service['who_for'] ?? '') ?>
    </p>
  </div>
</section>

<!-- Process -->
<?php if (!empty($process)): ?>
<section data-reveal style="padding:64px 0;border-bottom:1px solid var(--hairline);">
  <div class="eyebrow">PROCESS</div>
  <h2 class="h1" style="margin:12px 0 40px;max-width:18ch;">A method that <em class="accent-italic">ships.</em></h2>

  <div data-stagger="100" style="display:grid;grid-template-columns:repeat(<?= min(4, count($process)) ?>,1fr);gap:1px;background:var(--hairline);border:1px solid var(--hairline);">
    <?php $i = 1; foreach ($process as $step): ?>
      <div class="cap-tile" style="background:var(--bone);" data-reveal>
        <span class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);"><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?> / <?= str_pad((string)count($process), 2, '0', STR_PAD_LEFT) ?></span>
        <div style="margin-top:30px;color:var(--crimson);"><?= icon('method', 28) ?></div>
        <h3 class="ff-display" style="font-weight:600;font-size:22px;line-height:1.2;margin:14px 0 8px;"><?= e($step['title']) ?></h3>
        <p class="body-m" style="color:rgba(10,10,10,0.7);"><?= e($step['desc']) ?></p>
      </div>
    <?php $i++; endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Deliverables -->
<?php if (!empty($deliverables)): ?>
<section data-reveal style="padding:64px 0;border-bottom:1px solid var(--hairline);">
  <div class="eyebrow">DELIVERABLES</div>
  <h2 class="h1" style="margin:12px 0 40px;max-width:18ch;">What you walk away with.</h2>
  <ul data-stagger="60" style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(2,1fr);gap:0;border-top:1px solid var(--hairline);">
    <?php foreach ($deliverables as $d): ?>
      <li data-reveal style="display:flex;align-items:center;gap:18px;padding:20px 4px;border-bottom:1px solid var(--hairline);font-size:16px;">
        <span style="color:var(--crimson);"><?= icon('check', 20) ?></span>
        <span><?= e($d) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<!-- Case references (related projects) -->
<!-- AI brief-match — paste a brief, the mentor maps it to this service.
     Cached for 1 hour per identical brief; falls back to the canned
     "email us" line when AI is off / budget tripped. -->
<section data-reveal style="padding:64px 0;border-bottom:1px solid var(--hairline);">
  <div class="eyebrow">HOW THIS APPLIES TO YOU</div>
  <h2 class="h1" style="margin:12px 0 18px;">Map this to your brief.</h2>
  <p class="body-l" style="color:var(--text-mute);margin:0 0 24px;max-width:62ch;">
    Paste a sentence or two on what you're trying to do. Our AI mentor will pull from this service's
    overview + process + deliverables and tell you, in three bullets, where it fits — and where it
    doesn't. Honest by design.
  </p>
  <form class="services-brief" data-services-brief
        style="display:flex;flex-direction:column;gap:14px;max-width:680px;">
    <label class="field field--auth">
      <span class="field__label">Your brief</span>
      <textarea name="brief" rows="3" maxlength="800"
                placeholder="e.g. We're a Lagos lighting studio launching a flagship line in Q3 — need identity + packaging + a small launch event."></textarea>
    </label>
  </form>
  <?php partial('ai-block', [
    'intent'   => 'services_brief_match',
    'kind'     => 'summary',
    'label'    => '✨ Match my brief to this service',
    'eyebrow'  => '// MENTOR MATCH',
    'target'   => 'brief',
    'context'  => json_encode([
        'service'        => $service['slug']     ?? '',
        'name'           => $service['name']     ?? '',
        'overview'       => mb_substr((string)($service['overview']    ?? ''), 0, 600),
        'deliverables'   => $service['deliverables'] ?? $service['deliverables_json'] ?? [],
        'process'        => $service['process']      ?? $service['process_json']      ?? [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
  ]); ?>
</section>

<script>
// services-brief: when the textarea changes, pre-bake the context with
// the user's brief into the ai-block's data-ai-context so AfsAi.run
// reads it. We never touch the partial's other context fields.
(function () {
  const form = document.querySelector('[data-services-brief]');
  if (!form) return;
  const ta = form.querySelector('textarea[name="brief"]');
  const block = document.querySelector('[data-ai-block][data-ai-intent="services_brief_match"]');
  if (!ta || !block) return;
  const baseCtx = (function () {
    try { return JSON.parse(block.dataset.aiContext || '{}'); } catch (_) { return {}; }
  })();
  function syncCtx() {
    const brief = (ta.value || '').trim();
    block.dataset.aiContext = JSON.stringify(Object.assign({}, baseCtx, { user_brief: brief }));
  }
  ta.addEventListener('input', syncCtx);
  syncCtx();
})();
</script>

<?php if (!empty($projects)): ?>
<section data-reveal style="padding:64px 0;">
  <div class="eyebrow">CASE REFERENCES</div>
  <h2 class="h1" style="margin:12px 0 32px;">Recent work.</h2>
  <div class="project-rows">
    <?php $i = 1; foreach ($projects as $project):
      $project['index'] = $i++;
      partial('project-row', ['project' => $project]);
    endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Other services -->
<?php if (!empty($related)): ?>
<section data-reveal style="margin-top:64px;">
  <div class="eyebrow">MORE FROM THE STUDIO</div>
  <h2 class="h2" style="margin:12px 0 24px;">Pair this with another capability.</h2>
  <div class="cap-grid">
    <?php $i = 1; $total = count($related); foreach (array_slice($related, 0, 3) as $svc):
      $svc['index'] = $i++; $svc['total'] = $total;
      partial('service-card', ['service' => $svc]);
    endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php partial('cta-final'); ?>
