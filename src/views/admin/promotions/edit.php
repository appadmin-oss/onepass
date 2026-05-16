<?php /** @var ?array $row */ ?>
<header style="margin-bottom:24px;">
  <a class="nav-link" href="<?= e(url('/admin/promotions')) ?>">← Promotions</a>
  <h1 class="ff-display" style="font-weight:700;font-size:28px;letter-spacing:-0.02em;margin:8px 0 0;">
    <?= $row ? 'Edit promotion' : 'New promotion' ?>
  </h1>
  <p class="caption" style="margin:6px 0 0;">Upload an image, write copy, pick where it surfaces. Active promotions appear automatically.</p>
</header>

<form method="post" action="<?= e(url('/admin/promotions/save')) ?>" class="admin-card"
      style="display:grid;grid-template-columns:2fr 1fr;gap:32px;"
      data-promotion-form data-smart>
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= e((string)($row['id'] ?? '')) ?>">

  <div>
    <div class="field">
      <input name="title" placeholder=" " value="<?= e($row['title'] ?? '') ?>" required
             data-smart-suggest-target>
      <label>Title</label>
    </div>
    <div style="display:flex;gap:8px;margin:-12px 0 14px;">
      <button type="button" class="btn btn-ghost btn-sm"
              data-smart-suggest="title" data-smart-intent="promo_title"
              title="Rewrite the title in our brand voice">✨ Polish title</button>
      <span class="caption" style="align-self:center;">Sends only the text above — never any other field.</span>
    </div>
    <div class="field">
      <input name="subtitle" placeholder=" " value="<?= e($row['subtitle'] ?? '') ?>">
      <label>Subtitle / supporting line</label>
    </div>
    <div style="display:flex;gap:8px;margin:-12px 0 14px;">
      <button type="button" class="btn btn-ghost btn-sm"
              data-smart-suggest="subtitle" data-smart-intent="promo_sub"
              title="Rewrite the subtitle in our brand voice">✨ Polish subtitle</button>
    </div>
    <div class="field"><input name="eyebrow" placeholder=" " value="<?= e($row['eyebrow'] ?? '') ?>"><label>Eyebrow (// SHORT LABEL)</label></div>
    <div class="field"><input name="slug" placeholder=" " value="<?= e($row['slug'] ?? '') ?>"><label>Slug (auto from title if blank)</label></div>
    <div class="field"><input name="badge" placeholder=" " value="<?= e($row['badge'] ?? '') ?>"><label>Badge (e.g. Cohort · Funded · Event)</label></div>
    <div class="field"><textarea name="body" rows="5" placeholder=" "><?= e($row['body'] ?? '') ?></textarea><label>Body (optional, longer copy)</label></div>

    <div class="field">
      <input name="image_url" id="promo-image" placeholder=" " value="<?= e($row['image_url'] ?? '') ?>">
      <label>Image URL (Unsplash, CDN, or use upload →)</label>
    </div>

    <div style="display:flex;gap:10px;align-items:center;margin-top:8px;">
      <label class="btn btn-ghost btn-sm" style="cursor:pointer;">
        <input type="file" accept="image/*" data-promo-upload style="display:none;">
        Upload image
      </label>
      <span class="caption" data-promo-upload-status></span>
    </div>

    <div class="grid-cols-2" style="gap:14px;margin-top:18px;">
      <div class="field"><input name="cta_label" placeholder=" " value="<?= e($row['cta_label'] ?? '') ?>"><label>CTA label</label></div>
      <div class="field"><input name="cta_href"  placeholder=" " value="<?= e($row['cta_href']  ?? '') ?>"><label>CTA URL</label></div>
    </div>
  </div>

  <aside style="display:flex;flex-direction:column;gap:18px;">
    <div class="field" style="margin:0;">
      <label class="eyebrow">// KIND</label>
      <select name="kind" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
        <?php foreach (['banner','cohort','service','scholarship','hackathon','interstitial','feature'] as $k): ?>
          <option value="<?= e($k) ?>" <?= ($row['kind'] ?? 'banner') === $k ? 'selected' : '' ?>><?= e(ucfirst($k)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="margin:0;">
      <label class="eyebrow">// TONE</label>
      <select name="tone" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
        <?php foreach (['crimson','ink','peach','maroon','bone'] as $t): ?>
          <option value="<?= e($t) ?>" <?= ($row['tone'] ?? 'crimson') === $t ? 'selected' : '' ?>><?= e(ucfirst($t)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <input name="placements" placeholder=" " value="<?= e($row['placements'] ?? 'interstitial') ?>">
      <label>Placements (comma-separated)</label>
      <span class="caption" style="margin-top:4px;display:block;">ribbon · interstitial · home · academy · sidebar</span>
    </div>
    <div class="field"><input name="sort" type="number" placeholder=" " value="<?= e((string)($row['sort'] ?? 0)) ?>"><label>Sort (lower = first)</label></div>
    <div class="grid-cols-2" style="gap:14px;">
      <div class="field"><input name="starts_at" type="datetime-local" placeholder=" " value="<?= e($row['starts_at'] ? substr(str_replace(' ', 'T', $row['starts_at']), 0, 16) : '') ?>"><label>Starts at</label></div>
      <div class="field"><input name="ends_at"   type="datetime-local" placeholder=" " value="<?= e($row['ends_at']   ? substr(str_replace(' ', 'T', $row['ends_at']),   0, 16) : '') ?>"><label>Ends at</label></div>
    </div>
    <div class="field" style="margin:0;">
      <label class="eyebrow">// STATUS</label>
      <select name="status" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
        <?php foreach (['draft','active','paused'] as $st): ?>
          <option value="<?= e($st) ?>" <?= ($row['status'] ?? 'draft') === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Save promotion</button>
  </aside>
</form>

<style>@media(max-width:800px){form.admin-card{grid-template-columns:1fr !important;}}</style>

<!-- Cross-channel promo variants. Mentor reads the current title +
     subtitle + body and drafts tweet / LinkedIn / email subject +
     preheader. JSON output is parsed into four copy-to-clipboard
     cards below. Edit-then-publish — the operator still owns the
     final wording on each channel. -->
<section class="admin-card" style="margin-top:24px;" data-promo-variants>
  <div class="eyebrow">// CROSS-CHANNEL DRAFT</div>
  <h2 class="ff-display" style="font-weight:700;font-size:22px;letter-spacing:-0.01em;margin:8px 0 6px;">
    Adapt this promo for tweet, LinkedIn, and email.
  </h2>
  <p class="caption" style="margin:0 0 16px;">
    One mentor pass writes four drafts in the brand voice. Tweak, then copy into each channel.
  </p>
  <?php partial('ai-block', [
    'intent'    => 'promo_variants',
    'kind'      => 'recommend',
    'label'     => '✨ Draft cross-channel copy',
    'eyebrow'   => '// MENTOR DRAFT',
    'editable'  => true,
    'context'   => '{}',
  ]); ?>
  <div class="promo-variants-out" hidden style="margin-top:18px;display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
    <?php foreach ([
        ['tweet',           'Tweet',          'X / Twitter · up to 240 chars'],
        ['linkedin',        'LinkedIn',       'Two short paragraphs'],
        ['email_subject',   'Email subject',  '60 chars max'],
        ['email_preheader', 'Email preheader','90 chars max'],
    ] as $v): ?>
      <div class="promo-variant" data-variant="<?= e($v[0]) ?>" style="border:1px solid var(--hairline);padding:14px 14px 12px;border-radius:4px;display:flex;flex-direction:column;gap:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
          <div>
            <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);"><?= e($v[1]) ?></div>
            <div class="caption" style="margin-top:2px;"><?= e($v[2]) ?></div>
          </div>
          <button type="button" class="btn btn-ghost btn-sm" data-variant-copy>Copy</button>
        </div>
        <textarea rows="<?= $v[0] === 'linkedin' ? 5 : 2 ?>" style="width:100%;border:1px solid var(--hairline);padding:8px;font-size:13px;font-family:inherit;resize:vertical;"></textarea>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
// promo-variants: sync title/subtitle/body into the ai-block's context,
// then parse the JSON result into four editable cards with copy-to-clipboard.
(function () {
  const root = document.querySelector('[data-promo-variants]');
  if (!root) return;
  const form = document.querySelector('[data-promotion-form]');
  const block = root.querySelector('[data-ai-block][data-ai-intent="promo_variants"]');
  if (!form || !block) return;

  function syncCtx() {
    const get = (name) => (form.elements.namedItem(name)?.value || '').trim();
    block.dataset.aiContext = JSON.stringify({
      title:    get('title'),
      subtitle: get('subtitle'),
      body:     get('body'),
    });
  }
  ['title','subtitle','body'].forEach((n) => {
    const el = form.elements.namedItem(n);
    if (el) el.addEventListener('input', syncCtx);
  });
  syncCtx();

  const out = root.querySelector('.promo-variants-out');
  block.addEventListener('ai:result', (ev) => {
    let txt = (ev.detail?.text || '').trim();
    if (txt.startsWith('```')) txt = txt.replace(/^```[a-z]*\s*|\s*```$/gi, '');
    let obj;
    try { obj = JSON.parse(txt); } catch (_) { obj = null; }
    if (!obj) return;
    out.hidden = false;
    out.querySelectorAll('.promo-variant').forEach((card) => {
      const k = card.dataset.variant;
      const ta = card.querySelector('textarea');
      ta.value = (obj[k] ?? '').toString();
    });
  });

  out.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-variant-copy]');
    if (!btn) return;
    const ta = btn.closest('.promo-variant').querySelector('textarea');
    navigator.clipboard?.writeText(ta.value || '').then(() => {
      const prev = btn.textContent;
      btn.textContent = 'Copied ✓';
      setTimeout(() => { btn.textContent = prev; }, 1200);
    });
  });
})();
</script>

<style>
@media(max-width:680px){
  [data-promo-variants] .promo-variants-out { grid-template-columns:1fr !important; }
}
</style>

<script>
  // Inline image upload — POSTs to /admin/promotions/upload, returns URL,
  // drops it straight into image_url.
  (function(){
    var input = document.querySelector('[data-promo-upload]');
    var url   = document.getElementById('promo-image');
    var status = document.querySelector('[data-promo-upload-status]');
    var csrf  = document.querySelector('meta[name="csrf-token"]');
    if (!input) return;
    input.addEventListener('change', async function(){
      var f = input.files[0];
      if (!f) return;
      status.textContent = '// uploading…';
      var fd = new FormData();
      fd.append('file', f);
      if (csrf) fd.append('_csrf', csrf.content);
      try {
        var r = await fetch('/admin/promotions/upload', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd,
          credentials: 'same-origin',
        });
        var data = await r.json();
        if (!r.ok || !data.ok) { status.style.color = 'var(--crimson)'; status.textContent = '// ' + (data.error || 'failed'); return; }
        url.value = data.url;
        status.style.color = 'var(--success)';
        status.textContent = '// uploaded ✓ ' + data.url;
      } catch (e) {
        status.style.color = 'var(--crimson)';
        status.textContent = '// upload error';
      }
    });
  })();
</script>
