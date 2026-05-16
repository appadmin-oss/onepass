<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<header data-reveal style="margin:32px 0 24px;text-align:center;max-width:680px;margin-left:auto;margin-right:auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// TOOL · SHORT LINK</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">Short, <em class="grad-text">memorable, free.</em></h1>
  <p class="body-l" style="color:var(--text-mute);max-width:50ch;margin:0 auto;">
    Paste a URL, get a clean short link. Stays on your device until you copy it. No login, no tracking.
  </p>
</header>

<section class="tool-stage" data-reveal>
  <div class="tool-stage__panel">
    <div class="eyebrow">INPUT</div>
    <div class="field" style="margin-top:14px;">
      <input type="url" id="t-url" placeholder=" " inputmode="url" data-tool-shorten>
      <label>Paste a long URL</label>
    </div>
    <div class="field">
      <input type="text" id="t-alias" placeholder=" " maxlength="20" pattern="[a-zA-Z0-9\-_]+">
      <label>Custom alias (optional, letters &amp; numbers)</label>
    </div>
    <button class="btn btn-primary" type="button" data-tool-shorten-btn>Shorten link <?= icon_chev() ?></button>

    <p class="caption" style="margin-top:24px;">// History — last 10 saved on this device</p>
    <div data-tool-history style="margin-top:8px;font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text-mute);"></div>
  </div>

  <div class="tool-stage__panel tool-stage__result" data-tool-result-host>
    <div data-tool-result-empty>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// READY WHEN YOU ARE</div>
      <p class="ff-display" style="font-weight:600;font-size:24px;line-height:1.2;letter-spacing:-0.015em;margin:14px 0 0;max-width:24ch;">
        Your short link will appear here.
      </p>
    </div>
    <div data-tool-result style="display:none;width:100%;">
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// YOUR SHORT LINK</div>
      <div class="tool-result__code" data-tool-result-code></div>
      <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" data-tool-copy>Copy</button>
        <a class="btn btn-ghost" target="_blank" rel="noopener" data-tool-open>Open →</a>
      </div>
      <div class="caption" style="margin-top:18px;" data-tool-status></div>
    </div>
  </div>
</section>

<?php partial('promo-academy'); ?>

<script>
  (function(){
    var KEY = 'afs_short_links';
    var urlIn  = document.getElementById('t-url');
    var aliasIn = document.getElementById('t-alias');
    var btn    = document.querySelector('[data-tool-shorten-btn]');
    var empty  = document.querySelector('[data-tool-result-empty]');
    var result = document.querySelector('[data-tool-result]');
    var code   = document.querySelector('[data-tool-result-code]');
    var openA  = document.querySelector('[data-tool-open]');
    var copy   = document.querySelector('[data-tool-copy]');
    var status = document.querySelector('[data-tool-status]');
    var hist   = document.querySelector('[data-tool-history]');

    function load() { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch(e){ return []; } }
    function save(list) { try { localStorage.setItem(KEY, JSON.stringify(list.slice(0,10))); } catch(e){} }

    function renderHistory() {
      var list = load();
      if (!list.length) { hist.textContent = '// nothing yet'; return; }
      hist.innerHTML = list.map(function(r){
        return '<div style="padding:8px 0;border-top:1px solid var(--hairline);display:flex;justify-content:space-between;gap:10px;align-items:center;">' +
          '<span><strong style="color:var(--crimson);">/s/' + r.code + '</strong> → <span style="opacity:0.7;">' + r.url.replace(/^https?:\/\//,'').slice(0,40) + '</span></span>' +
          (r.persisted ? '<span class="pill pill-live" style="font-size:9px;">LIVE</span>' : '<span class="pill" style="font-size:9px;">LOCAL</span>') +
        '</div>';
      }).join('');
    }
    renderHistory();

    async function shorten() {
      var raw = (urlIn.value || '').trim();
      var fld = urlIn.closest('.field');
      if (!/^https?:\/\//.test(raw)) {
        fld.classList.add('shake', 'is-error');
        setTimeout(function(){ fld.classList.remove('shake'); }, 360);
        status.textContent = '// URL must start with http:// or https://';
        return;
      }
      fld.classList.remove('is-error');
      btn.disabled = true; btn.textContent = 'Shortening…';
      var csrf = document.querySelector('meta[name="csrf-token"]');
      var fd = new FormData();
      fd.append('url', raw);
      if (aliasIn.value) fd.append('alias', aliasIn.value);
      if (csrf) fd.append('_csrf', csrf.content);
      try {
        var r = await fetch('/api/short-links', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: fd,
          credentials: 'same-origin',
        });
        var data = await r.json();
        if (!r.ok) {
          status.textContent = '// ' + (data.message || 'Could not create link');
          status.style.color = 'var(--crimson)';
          return;
        }
        empty.style.display = 'none';
        result.style.display = 'block';
        code.textContent = data.short;
        openA.href = data.short;
        status.style.color = '';
        status.textContent = data.persisted
          ? '// Live · ' + data.short + ' redirects to your URL globally.'
          : '// Local fallback — DB unavailable, link works only in this session.';
        var list = load();
        list.unshift({ code: data.code, url: raw, at: Date.now(), persisted: !!data.persisted });
        save(list);
        renderHistory();
      } catch(e) {
        status.style.color = 'var(--crimson)';
        status.textContent = '// Network error — try again.';
      } finally {
        btn.disabled = false;
        btn.innerHTML = 'Shorten link <svg class="chev" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4"/></svg>';
      }
    }
    btn.addEventListener('click', shorten);
    urlIn.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); shorten(); } });

    copy.addEventListener('click', async function(){
      try { await navigator.clipboard.writeText(code.textContent); status.textContent = '// copied to clipboard ✓'; }
      catch(e){ status.textContent = '// select and copy manually'; }
    });
  })();
</script>
