<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<header data-reveal style="margin:32px 0 24px;text-align:center;max-width:680px;margin-left:auto;margin-right:auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// TOOL · UTM BUILDER</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">Clean campaign URLs, <em class="grad-text">tracked properly.</em></h1>
  <p class="body-l" style="color:var(--text-mute);max-width:54ch;margin:0 auto;">
    Fill the fields, get a properly-encoded marketing URL. Source · medium · campaign — the three that actually matter.
  </p>
</header>

<section class="tool-stage" data-reveal>
  <div class="tool-stage__panel">
    <div class="eyebrow">INPUT</div>
    <div class="field" style="margin-top:14px;">
      <input type="url" id="u-url" placeholder=" " inputmode="url">
      <label>Destination URL</label>
    </div>
    <div class="grid-cols-2" style="gap:14px;">
      <div class="field"><input type="text" id="u-source"   placeholder=" "><label>utm_source (e.g. instagram)</label></div>
      <div class="field"><input type="text" id="u-medium"   placeholder=" "><label>utm_medium (e.g. social)</label></div>
    </div>
    <div class="field"><input type="text" id="u-campaign" placeholder=" "><label>utm_campaign (e.g. afrotech-2026)</label></div>
    <div class="grid-cols-2" style="gap:14px;">
      <div class="field"><input type="text" id="u-content"  placeholder=" "><label>utm_content (optional)</label></div>
      <div class="field"><input type="text" id="u-term"     placeholder=" "><label>utm_term (optional)</label></div>
    </div>
    <button class="btn btn-primary" type="button" data-utm-build>Build URL <?= icon_chev() ?></button>
  </div>

  <div class="tool-stage__panel tool-stage__result">
    <div data-utm-empty>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// READY WHEN YOU ARE</div>
      <p class="ff-display" style="font-weight:600;font-size:24px;line-height:1.2;letter-spacing:-0.015em;margin:14px 0 0;max-width:26ch;">
        Your tracked URL will appear here.
      </p>
    </div>
    <div data-utm-result style="display:none;width:100%;">
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// YOUR CAMPAIGN URL</div>
      <div class="tool-result__code" data-utm-output></div>
      <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" data-utm-copy>Copy</button>
        <a class="btn btn-ghost" target="_blank" rel="noopener" data-utm-open>Open →</a>
        <a class="btn btn-ghost" data-utm-qr>Make QR →</a>
      </div>
      <div class="caption" data-utm-status style="margin-top:18px;"></div>
    </div>
  </div>
</section>

<?php partial('promo-academy'); ?>

<script>
  (function(){
    var ids = ['u-url','u-source','u-medium','u-campaign','u-content','u-term'];
    var btn = document.querySelector('[data-utm-build]');
    var empty = document.querySelector('[data-utm-empty]');
    var res   = document.querySelector('[data-utm-result]');
    var out   = document.querySelector('[data-utm-output]');
    var copy  = document.querySelector('[data-utm-copy]');
    var open  = document.querySelector('[data-utm-open]');
    var qr    = document.querySelector('[data-utm-qr]');
    var status= document.querySelector('[data-utm-status]');
    btn.addEventListener('click', function(){
      var url = document.getElementById('u-url').value.trim();
      if (!/^https?:\/\//.test(url)) { document.getElementById('u-url').closest('.field').classList.add('shake'); setTimeout(function(){ document.getElementById('u-url').closest('.field').classList.remove('shake'); }, 360); return; }
      var u = new URL(url);
      ['source','medium','campaign','content','term'].forEach(function(p){
        var v = document.getElementById('u-'+p).value.trim();
        if (v) u.searchParams.set('utm_'+p, v);
      });
      empty.style.display = 'none';
      res.style.display = 'block';
      out.textContent = u.toString();
      open.href = u.toString();
      qr.href = '/tools/qr#' + encodeURIComponent(u.toString());
      status.textContent = '// Encoded · ready to paste into a post or DM';
    });
    copy.addEventListener('click', async function(){
      try { await navigator.clipboard.writeText(out.textContent); status.textContent = '// copied to clipboard ✓'; }
      catch(e){ status.textContent = '// select and copy manually'; }
    });
  })();
</script>
