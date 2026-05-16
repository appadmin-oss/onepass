<?php
/** @var array $row */
/** @var array $components */
/** @var array $severities */
/** @var array $kinds */
$ok  = flash_pop('admin_status_ok');
$err = flash_pop('admin_status_error');
$isNew     = empty($row['id']);
$isResolved = !empty($row['resolved_at']);
$selectedComponents = array_filter(array_map('trim', explode(',', (string)($row['components_csv'] ?? ''))));
$action = $isNew
    ? url('/admin/status/incidents')
    : url('/admin/status/incidents/' . (int)$row['id']);
?>

<header style="display:flex;align-items:end;justify-content:space-between;gap:24px;margin-bottom:24px;">
  <div>
    <p class="ff-mono" style="font-size:11px;letter-spacing:0.22em;text-transform:uppercase;color:var(--text-mute);margin:0 0 6px;">
      // STATUS · INCIDENT <?= $isNew ? '· NEW' : '· EDIT' ?>
    </p>
    <h1 class="h-display-2" style="margin:0;">
      <?= $isNew ? 'New incident' : 'Editing incident' ?>
    </h1>
    <?php if (!empty($row['public_id'])): ?>
      <p class="caption" style="margin:8px 0 0;">
        Public link:
        <a class="nav-link" href="<?= e(url('/status/incidents/' . $row['public_id'])) ?>" target="_blank" rel="noopener">
          <?= e(url('/status/incidents/' . $row['public_id'])) ?> ↗
        </a>
      </p>
    <?php endif; ?>
  </div>
  <div style="display:flex;gap:10px;">
    <a class="nav-link" href="<?= e(url('/admin/status/incidents')) ?>">← All incidents</a>
  </div>
</header>

<?php if ($ok): ?>
  <div role="status" class="auth-form__alert" style="background:var(--state-up-bg);border-color:var(--state-up-border);color:var(--state-up);margin-bottom:18px;"><?= e($ok) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div role="alert" class="auth-form__alert" style="margin-bottom:18px;"><?= e($err) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" data-incident-form>
  <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
    <label class="field field--auth">
      <span class="field__label">Title</span>
      <input type="text" name="title" required maxlength="200"
             value="<?= e((string)$row['title']) ?>" placeholder="LMS unreachable from Egbeda peering" autofocus>
    </label>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <label class="field field--auth">
        <span class="field__label">Kind</span>
        <select name="kind" style="height:44px;padding:0 14px;border:1px solid var(--hairline);border-radius:12px;background:#fff;">
          <?php foreach ($kinds as $k): ?>
            <option value="<?= e($k) ?>" <?= ($row['kind'] ?? 'incident') === $k ? 'selected' : '' ?>>
              <?= e(ucfirst($k)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="field field--auth">
        <span class="field__label">Severity</span>
        <select name="severity" style="height:44px;padding:0 14px;border:1px solid var(--hairline);border-radius:12px;background:#fff;">
          <?php foreach ($severities as $s): ?>
            <option value="<?= e($s) ?>" <?= ($row['severity'] ?? 'minor') === $s ? 'selected' : '' ?>>
              <?= e(ucfirst($s)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
  </div>

  <fieldset style="margin-top:18px;padding:14px 18px;border:1px solid var(--hairline);border-radius:12px;background:var(--bone-warm);">
    <legend class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-mute);padding:0 8px;">// COMPONENTS</legend>
    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;">
      <?php foreach ($components as $c): ?>
        <label style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid var(--hairline);border-radius:999px;background:#fff;font-size:13px;cursor:pointer;">
          <input type="checkbox" name="components[]" value="<?= e($c) ?>"
                 <?= in_array($c, $selectedComponents, true) ? 'checked' : '' ?>>
          <?= e(strtoupper($c)) ?>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:18px;">
    <label class="field field--auth">
      <span class="field__label">Started at</span>
      <input type="datetime-local" name="started_at" required value="<?= e((string)$row['started_at']) ?>">
    </label>
    <label class="field field--auth">
      <span class="field__label">Resolved at <span style="color:var(--text-mute);font-weight:400;">(leave blank if active)</span></span>
      <input type="datetime-local" name="resolved_at" value="<?= e((string)($row['resolved_at'] ?? '')) ?>">
    </label>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px;">
    <div>
      <label class="field field--auth">
        <span class="field__label">Body (markdown)</span>
        <textarea name="body" rows="10" spellcheck="true"
                  data-incident-md
                  placeholder="What's happening, who's affected, what we're trying. Plain prose. Markdown links + lists welcome."><?= e((string)$row['body']) ?></textarea>
      </label>
      <p class="caption" style="margin:8px 0 0;">Live preview on the right. Renders the same way the public page does.</p>
    </div>
    <div>
      <p class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-mute);margin:0 0 8px;">// PREVIEW</p>
      <div class="incident__text" data-incident-preview
           style="min-height:240px;padding:14px 16px;border:1px solid var(--hairline);border-radius:12px;background:var(--bone);">
        <p style="color:var(--text-mute);font-style:italic;">Type on the left — preview appears here.</p>
      </div>
    </div>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;margin-top:24px;flex-wrap:wrap;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button type="submit" class="btn btn-primary"><?= $isNew ? 'Create incident' : 'Save changes' ?></button>
      <?php if (!$isNew && !$isResolved): ?>
        <button type="submit" formaction="<?= e(url('/admin/status/incidents/' . (int)$row['id'] . '/resolve')) ?>" class="btn btn-ghost">
          Mark resolved
        </button>
      <?php endif; ?>
    </div>
    <?php if (!$isNew): ?>
      <button type="submit" formaction="<?= e(url('/admin/status/incidents/' . (int)$row['id'] . '/delete')) ?>"
              class="btn btn-ghost btn-sm"
              style="color:var(--state-down);border-color:rgba(192,57,43,0.25);"
              onclick="return confirm('Delete this incident permanently? This cannot be undone.');">
        Delete
      </button>
    <?php endif; ?>
  </div>
</form>

<?php if (!$isNew): ?>
  <!-- Postmortem authoring. AI-drafted skeleton; operator owns the edit. -->
  <section style="margin-top:48px;padding-top:24px;border-top:1px solid var(--hairline);">
    <div style="display:flex;justify-content:space-between;align-items:end;gap:14px;flex-wrap:wrap;margin-bottom:14px;">
      <div>
        <h2 class="h3" style="margin:0 0 4px;">Postmortem</h2>
        <p class="caption" style="margin:0;">
          <?php if (!$isResolved): ?>
            Resolve the incident first — AI postmortems run on closed incidents only.
          <?php else: ?>
            Click "Draft with AI" to append a 3-bullet skeleton you can edit. Cached for 24 hours per resolved incident.
          <?php endif; ?>
        </p>
      </div>
      <?php if ($isResolved): ?>
        <form method="post" action="<?= e(url('/admin/status/incidents/' . (int)$row['id'] . '/postmortem')) ?>" style="display:inline;">
          <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
          <button type="submit" class="btn btn-ghost btn-sm">✨ Draft with AI</button>
        </form>
      <?php endif; ?>
    </div>

    <form method="post" action="<?= e($action) ?>">
      <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
      <!-- Carry through the other fields so the update doesn't blank them. -->
      <input type="hidden" name="title"          value="<?= e((string)$row['title']) ?>">
      <input type="hidden" name="kind"           value="<?= e((string)$row['kind']) ?>">
      <input type="hidden" name="severity"       value="<?= e((string)$row['severity']) ?>">
      <input type="hidden" name="started_at"     value="<?= e((string)$row['started_at']) ?>">
      <input type="hidden" name="resolved_at"    value="<?= e((string)($row['resolved_at'] ?? '')) ?>">
      <input type="hidden" name="body"           value="<?= e((string)($row['body'] ?? '')) ?>">
      <?php foreach ($selectedComponents as $c): ?>
        <input type="hidden" name="components[]" value="<?= e($c) ?>">
      <?php endforeach; ?>
      <label class="field field--auth">
        <span class="field__label">Postmortem (markdown)</span>
        <textarea name="postmortem" rows="8"
                  placeholder="What happened · How we mitigated · What we changed. Plain prose. No blame."><?= e((string)($row['postmortem'] ?? '')) ?></textarea>
      </label>
      <button type="submit" class="btn btn-primary btn-sm" style="margin-top:8px;">Save postmortem</button>
    </form>

    <?php partial('ai-disclosure', ['note' => 'When AI drafted, edited by an operator before publishing.']); ?>
  </section>
<?php endif; ?>

<!-- Live markdown preview via Marked + DOMPurify (same pair the public
     incident detail page uses). Loaded only on this admin route. -->
<script src="https://cdn.jsdelivr.net/npm/marked@13.0.0/marked.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js" defer></script>
<script>
(function () {
  'use strict';
  const src  = document.querySelector('[data-incident-md]');
  const out  = document.querySelector('[data-incident-preview]');
  if (!src || !out) return;
  function render() {
    if (!window.marked || !window.DOMPurify) return;
    const raw = src.value || '';
    if (raw.trim() === '') {
      out.innerHTML = '<p style="color:var(--text-mute);font-style:italic;">Type on the left — preview appears here.</p>';
      return;
    }
    try {
      const html = window.marked.parse(raw, { breaks: true, gfm: true });
      out.innerHTML = window.DOMPurify.sanitize(html, {
        ALLOWED_TAGS: ['p','a','ul','ol','li','strong','em','code','pre','br','blockquote','h2','h3','h4','img','hr'],
        ALLOWED_ATTR: ['href','title','src','alt'],
        ALLOW_DATA_ATTR: false,
      });
    } catch (_) {}
  }
  src.addEventListener('input', render);
  let waited = 0;
  const t = setInterval(function () {
    if ((window.marked && window.DOMPurify) || waited >= 3000) { clearInterval(t); render(); }
    waited += 100;
  }, 100);
})();
</script>
