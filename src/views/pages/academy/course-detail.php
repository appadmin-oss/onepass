<?php
/** @var array $course
 *  @var array $instructors
 *  @var bool  $lmsEnabled
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

$enrollHref = $lmsEnabled && !empty($course['enrol_url'])
    ? $course['enrol_url']
    : url('/academy/apply?track=' . $course['slug']);
?>

<header data-reveal style="padding:32px 0 48px;border-bottom:1px solid var(--hairline);">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.18em;color:var(--crimson);text-transform:uppercase;">
    // <?= e(strtoupper($course['level'])) ?> · <?= e((string)$course['weeks']) ?> WEEKS
  </div>
  <h1 class="h-display-2" style="margin:16px 0 16px;max-width:22ch;">
    <?= e($course['title']) ?>
  </h1>
  <p class="body-l" style="max-width:62ch;color:rgba(10,10,10,0.7);">
    <?= e($course['summary']) ?>
  </p>
  <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
    <a href="<?= e($enrollHref) ?>" class="btn btn-primary"<?= $lmsEnabled ? ' rel="external"' : '' ?>>
      Enrol <?= icon_chev() ?>
    </a>
    <?php if (!empty($course['lms_url'])): ?>
      <a href="<?= e($course['lms_url']) ?>" class="btn btn-ghost" rel="external">Open in LMS</a>
    <?php endif; ?>
    <?php if (!empty($course['price_naira'])): ?>
      <span class="ff-mono" style="font-size:12px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(10,10,10,0.6);">
        ₦ <?= number_format((int)$course['price_naira']) ?> · per seat
      </span>
    <?php endif; ?>
  </div>
</header>

<section data-reveal style="padding:48px 0;border-bottom:1px solid var(--hairline);">
  <div class="eyebrow">OVERVIEW</div>
  <div class="prose" style="margin-top:18px;">
    <?= $course['body'] ?? '' ?>
  </div>
</section>

<!-- "Is this for me?" — visitor jots two lines on what they know +
     want; AI mentor reads them alongside the course outline and
     answers in two short bullets (fit verdict + first unit). Honest
     when it's not a fit. PII intent → never cached.  -->
<section data-reveal style="padding:48px 0;border-bottom:1px solid var(--hairline);">
  <div class="eyebrow">FIT CHECK</div>
  <h2 class="h1" style="margin:12px 0 14px;">Is this course for <em class="accent-italic">you?</em></h2>
  <p class="body-l" style="color:var(--text-mute);margin:0 0 22px;max-width:62ch;">
    Two short sentences: what you already know, and what you want by the end of this. The mentor
    will read it next to the course outline and tell you honestly whether to take it — and where
    to start if you do.
  </p>
  <form class="services-brief" data-course-fit
        style="display:flex;flex-direction:column;gap:14px;max-width:680px;">
    <label class="field field--auth">
      <span class="field__label">Your note</span>
      <textarea name="visitor_note" rows="3" maxlength="800"
                placeholder="e.g. I write JS for a startup, want to learn Kubernetes well enough to run our own clusters in production."></textarea>
    </label>
  </form>
  <?php partial('ai-block', [
    'intent'   => 'course_fit',
    'kind'     => 'summary',
    'label'    => '✨ Should I take this course?',
    'eyebrow'  => '// MENTOR FIT-CHECK',
    'target'   => 'visitor_note',
    'context'  => json_encode([
        'slug'    => $course['slug']    ?? '',
        'title'   => $course['title']   ?? '',
        'level'   => $course['level']   ?? '',
        'weeks'   => (int)($course['weeks'] ?? 0),
        // Strip HTML so the mentor reads clean prose. 700-char cap so
        // we never approach the per-intent context budget.
        'summary' => mb_substr(strip_tags((string)($course['summary'] ?? '')), 0, 320),
        'outline' => mb_substr(strip_tags((string)($course['body']    ?? '')), 0, 700),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
  ]); ?>
</section>

<script>
(function () {
  const form = document.querySelector('[data-course-fit]');
  if (!form) return;
  const ta = form.querySelector('textarea[name="visitor_note"]');
  const block = document.querySelector('[data-ai-block][data-ai-intent="course_fit"]');
  if (!ta || !block) return;
  const baseCtx = (function () {
    try { return JSON.parse(block.dataset.aiContext || '{}'); } catch (_) { return {}; }
  })();
  function syncCtx() {
    const v = (ta.value || '').trim();
    block.dataset.aiContext = JSON.stringify(Object.assign({}, baseCtx, { visitor_paragraph: v }));
  }
  ta.addEventListener('input', syncCtx);
  syncCtx();
})();
</script>

<?php if (!empty($instructors)): ?>
<section data-reveal style="padding:48px 0;border-bottom:1px solid var(--hairline);">
  <div class="eyebrow">INSTRUCTORS</div>
  <h2 class="h1" style="margin:12px 0 32px;">Taught by <em class="accent-italic">operators.</em></h2>
  <div data-stagger="80" class="grid-cols-3">
    <?php foreach (array_slice($instructors, 0, 3) as $op) partial('operator-card', ['op' => $op]); ?>
  </div>
</section>
<?php endif; ?>

<?php partial('cert-panel'); ?>
