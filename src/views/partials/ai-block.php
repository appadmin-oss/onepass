<?php
/**
 * ai-block — the canonical shell every AI affordance on the site renders
 * itself inside. Enforces the 12 UX patterns from the plan:
 *
 *   1. Predictable position (call the partial below the surface it augments)
 *   2. One-tap invocation (single button or auto-load with `auto=true`)
 *   3. Visible idle state (renders even when AI is paused)
 *   4. Optimistic busy state (handled by ai-action.js)
 *   5. Honest soft timeout (handled by ai-action.js)
 *   6. Edit-then-publish (operator-only; toggle via `editable`)
 *   7. Attribution + provider (footer line, always present)
 *   8. Undo (handled by ai-action.js for rewrites)
 *   9. Hallucination resistance via canned fallback
 *  10. Cost transparency on operator pages (`showBudget=true`)
 *  11. Accessibility (aria-live + role=status on the body)
 *  12. Never blocking the core action of the host page
 *
 * Inputs (all optional except $intent):
 *
 *   string $intent       AI registry intent name (e.g. 'services_brief_match')
 *   string $kind         'suggest' | 'summary' | 'recommend' | 'route' | 'rewrite'
 *   string $label        Button label, e.g. "How would this service apply?"
 *   string $eyebrow      Mono header above the body, e.g. "// MENTOR NOTE"
 *   string $target       data-target-name of the input we rewrite/operate on
 *   bool   $auto         If true, run on page load instead of waiting for a click
 *   bool   $editable     Show the "you can edit this" pencil hint
 *   bool   $showBudget   Show the "AI calls today: x/y" line (admin pages only)
 *   string $context      Pre-baked JSON context (when not derived from a form field)
 *   string $endpoint     POST URL (defaults to /api/ai/suggest)
 *   string $tone         'auto' | 'technical' | 'warm' (forwarded as data attr)
 *   string $note         An additional small note rendered below the affordance
 *   string $bodyHtml     Server-rendered initial body (e.g. cached output)
 *
 * The partial always renders a stable DOM shape so JS can attach to it
 * idempotently. Hide/show happens via state, not via re-rendering.
 */
$intent   = (string)($intent  ?? 'rewrite');
$kind     = (string)($kind    ?? 'suggest');
$label    = (string)($label   ?? 'AI assist');
$eyebrow  = (string)($eyebrow ?? '// AI mentor');
$target   = (string)($target  ?? '');
$auto     = (bool)  ($auto    ?? false);
$editable = (bool)  ($editable?? false);
$showBudget = (bool)($showBudget ?? false);
$context  = (string)($context ?? '');
$endpoint = (string)($endpoint?? '/api/ai/suggest');
$tone     = (string)($tone    ?? 'auto');
$note     = (string)($note    ?? '');
$bodyHtml = (string)($bodyHtml?? '');

// Pull the registry entry so we can show the fallback line server-side
// when AI is disabled. Honest design: the affordance always renders.
$cfg = AiRegistry::resolve($intent);
$aiOn = Ai::enabled();
$budget = $showBudget ? AiBudget::status() : null;

$id = 'ai-' . substr(sha1($intent . ($target ?: '') . microtime(true)), 0, 8);
?>
<section class="ai-block ai-block--<?= e($kind) ?>"
         data-ai-block
         data-ai-intent="<?= e($intent) ?>"
         data-ai-endpoint="<?= e($endpoint) ?>"
         data-ai-target="<?= e($target) ?>"
         data-ai-context="<?= e($context) ?>"
         data-ai-tone="<?= e($tone) ?>"
         data-ai-auto="<?= $auto ? '1' : '0' ?>"
         data-ai-fallback="<?= e($cfg['fallback']) ?>"
         aria-labelledby="<?= e($id) ?>-h">

  <header class="ai-block__head">
    <h3 id="<?= e($id) ?>-h" class="ai-block__eyebrow ff-mono"><?= e($eyebrow) ?></h3>
    <?php if ($editable): ?>
      <span class="ai-block__edit-hint" title="You can edit the AI draft before saving">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Editable
      </span>
    <?php endif; ?>
  </header>

  <?php if (!$aiOn): ?>
    <p class="ai-block__paused">
      AI mentor is paused. <?= e($cfg['fallback']) ?>
    </p>
  <?php else: ?>
    <button type="button" class="ai-block__action btn btn-ghost btn-sm" data-ai-action>
      <?= e($label) ?>
    </button>

    <!-- aria-live region so screen readers announce state changes.
         The body itself is the source of truth — never reflows below. -->
    <div class="ai-block__body" data-ai-body
         role="status" aria-live="polite" aria-atomic="false">
      <?= $bodyHtml ?>
    </div>

    <footer class="ai-block__foot">
      <span class="ai-block__attr" data-ai-attr hidden>
        Drafted by AI · <span data-ai-provider>—</span> · <span data-ai-ms>—</span>ms
      </span>
      <?php if ($showBudget && $budget): ?>
        <span class="ai-block__budget">
          AI calls today: <strong data-ai-spent><?= e((string)$budget['spent']) ?></strong>
          / <?= e((string)$budget['cap']) ?>
        </span>
      <?php endif; ?>
    </footer>
  <?php endif; ?>

  <?php if ($note !== ''): ?>
    <p class="ai-block__note"><?= e($note) ?></p>
  <?php endif; ?>
</section>
