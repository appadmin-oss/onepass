<?php
/** @var array $faqs */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// FAQ',
    'title'        => 'Direct answers. <em>Honest ones.</em>',
    'sub'          => 'The questions we get most often, answered the way we\'d answer them on a call.',
    'cta_label'    => 'Read the questions',
    'cta_href'     => '#questions',
    'illustration' => 'figure-question',
]]);
?>
<a id="questions" aria-hidden="true"></a>

<!-- AI-assisted search. The fuzzy filter is the primary path; AI is the
     fallback when no FAQ id matches a substring of the query.
     Both update the list in place by toggling [hidden] on each <details>. -->
<section data-reveal style="margin:24px 0 16px;">
  <form class="faq-search" data-faq-search role="search"
        style="display:flex;align-items:center;gap:10px;max-width:560px;">
    <label class="field field--auth" style="flex:1;margin:0;">
      <span class="sr-only">Search the FAQ</span>
      <input type="search" name="q" autocomplete="off"
             placeholder="Ask anything — 'when does the next cohort open?'"
             aria-label="Search the FAQ">
    </label>
    <button type="button" class="btn btn-ghost btn-sm" data-faq-ai>
      ✨ Ask the AI
    </button>
  </form>
  <p class="faq-search__status caption" data-faq-status
     role="status" aria-live="polite" style="margin:10px 0 0;">
    <?= count($faqs) ?> questions · type to filter, or ask the AI to find the best match.
  </p>
</section>

<section class="faq" data-reveal data-stagger="80"
         style="border-top:1px solid var(--hairline);"
         data-faq-list>
  <?php foreach ($faqs as $i => $f):
    $id = (int)($f['id'] ?? ($i + 1));
    $haystack = strtolower($f['q'] . ' ' . $f['a']);
  ?>
    <details data-reveal
             data-faq-id="<?= e((string)$id) ?>"
             data-faq-search-blob="<?= e($haystack) ?>"
             style="border-bottom:1px solid var(--hairline);padding:24px 4px;">
      <summary style="display:flex;align-items:center;justify-content:space-between;gap:24px;">
        <div style="display:flex;align-items:flex-start;gap:18px;">
          <span class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);padding-top:4px;"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="ff-display" style="font-weight:600;font-size:20px;line-height:1.3;"><?= e($f['q']) ?></span>
        </div>
        <span class="toggle">+</span>
      </summary>
      <p class="body-l" style="margin:18px 0 0;color:var(--text-mute);max-width:70ch;padding-left:42px;">
        <?= e($f['a']) ?>
      </p>
    </details>
  <?php endforeach; ?>
</section>

<p class="caption" data-faq-empty hidden
   style="margin:24px 0;padding:24px;border:1px dashed var(--hairline);border-radius:14px;text-align:center;color:var(--text-mute);">
  Nothing matches. <a class="nav-link" href="<?= e(url('/contact')) ?>">Email us the question</a> — we read every brief.
</p>

<?php
// Pack the QA list into a compact array the AI can read. The intent
// expects {query, all_qa:[{id, q, a}]} and returns {ids:[…], why}.
$qaForAi = array_map(fn($f, $i) => [
    'id' => (int)($f['id'] ?? ($i + 1)),
    'q'  => mb_substr((string)$f['q'], 0, 120),
    'a'  => mb_substr((string)$f['a'], 0, 240),
], $faqs, array_keys($faqs));
?>
<script>
(function () {
  'use strict';
  const form   = document.querySelector('[data-faq-search]');
  const inputEl= form?.querySelector('input[name="q"]');
  const aiBtn  = form?.querySelector('[data-faq-ai]');
  const list   = document.querySelector('[data-faq-list]');
  const empty  = document.querySelector('[data-faq-empty]');
  const status = document.querySelector('[data-faq-status]');
  if (!form || !inputEl || !list) return;

  const ITEMS = [...list.querySelectorAll('[data-faq-id]')];
  const QA    = <?= json_encode($qaForAi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  const FAQ_COUNT = ITEMS.length;

  function showAll() {
    ITEMS.forEach(el => { el.hidden = false; el.removeAttribute('data-ai-pick'); });
    if (empty) empty.hidden = true;
    if (status) status.textContent = FAQ_COUNT + ' questions · type to filter, or ask the AI to find the best match.';
  }

  function applyFilter(q) {
    q = q.trim().toLowerCase();
    if (!q) { showAll(); return; }
    let shown = 0;
    ITEMS.forEach(el => {
      const blob = el.dataset.faqSearchBlob || '';
      const match = blob.includes(q);
      el.hidden = !match;
      el.removeAttribute('data-ai-pick');
      if (match) shown++;
    });
    if (empty) empty.hidden = shown > 0;
    if (status) status.textContent = shown + ' match' + (shown === 1 ? '' : 'es') + ' for "' + q + '"';
  }

  inputEl.addEventListener('input', e => applyFilter(e.target.value));
  // Pre-fill from ?q=
  const url = new URL(location.href);
  if (url.searchParams.has('q')) {
    inputEl.value = url.searchParams.get('q');
    applyFilter(inputEl.value);
  }

  // AI fallback — POST to /api/ai/suggest with intent=faq_search.
  // The mentor returns {ids:[…], why}. Client whitelists ids against
  // our own catalogue — never inventing a question.
  async function askAi() {
    const q = (inputEl.value || '').trim();
    if (!q) { inputEl.focus(); return; }
    const labelBefore = aiBtn.textContent;
    aiBtn.disabled = true; aiBtn.textContent = 'Thinking…';
    if (status) status.textContent = 'Asking the AI mentor…';

    const fd = new FormData();
    fd.append('intent', 'faq_search');
    fd.append('context', JSON.stringify({ query: q, all_qa: QA }));
    fd.append('_csrf', document.querySelector('meta[name="csrf-token"]')?.content || '');
    try {
      const r = await fetch('/api/ai/suggest', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
      });
      const data = await r.json().catch(() => ({}));
      if (!r.ok || !data.ok || !data.text) throw new Error(data?.error || 'no_text');

      // Liberal JSON parser — strip code fences, fallback regex.
      const raw = String(data.text).trim()
        .replace(/^```(?:json)?\s*/i, '').replace(/```\s*$/i, '').trim();
      let parsed = null;
      try { parsed = JSON.parse(raw); }
      catch (_) { const m = raw.match(/\{[\s\S]*\}/); if (m) try { parsed = JSON.parse(m[0]); } catch (e) {} }
      if (!parsed || !Array.isArray(parsed.ids)) throw new Error('bad_json');

      // Whitelist ids — never highlight one we don't own.
      const knownIds = new Set(QA.map(x => x.id));
      const picks = parsed.ids
        .map(i => parseInt(i, 10))
        .filter(i => knownIds.has(i));

      if (picks.length === 0) {
        ITEMS.forEach(el => { el.hidden = true; el.removeAttribute('data-ai-pick'); });
        if (empty) empty.hidden = false;
        if (status) status.textContent = 'AI couldn\'t find a match. ' + (parsed.why || '');
      } else {
        ITEMS.forEach(el => {
          const id = parseInt(el.dataset.faqId, 10);
          const isPick = picks.includes(id);
          el.hidden = !isPick;
          if (isPick) el.setAttribute('data-ai-pick', '1');
          // Open the first match so the visitor sees the answer.
          if (isPick && id === picks[0]) el.open = true;
        });
        if (empty) empty.hidden = true;
        if (status) {
          const why = (parsed.why || '').trim();
          status.textContent = picks.length + ' AI match' + (picks.length === 1 ? '' : 'es')
            + (why ? ' · ' + why : '');
        }
      }
    } catch (e) {
      if (status) status.textContent = 'AI mentor offline — using plain filter.';
      applyFilter(q);
    } finally {
      aiBtn.disabled = false; aiBtn.textContent = labelBefore;
    }
  }
  aiBtn?.addEventListener('click', askAi);
  form.addEventListener('submit', e => { e.preventDefault(); askAi(); });
})();
</script>

<?php partial('cta-final'); ?>
