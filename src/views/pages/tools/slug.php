<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<header data-reveal style="margin:32px 0 24px;text-align:center;max-width:680px;margin-left:auto;margin-right:auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// TOOL · SLUG + SEO PREVIEW</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">Slugs that rank, <em class="grad-text">previewed live.</em></h1>
  <p class="body-l" style="color:var(--text-mute);max-width:54ch;margin:0 auto;">
    Type a title and meta. See the Google search snippet update in real time —
    including title length warning, description length warning, and clean URL slug.
  </p>
</header>

<section class="tool-stage" data-reveal>
  <div class="tool-stage__panel">
    <div class="eyebrow">META</div>
    <div class="field" style="margin-top:14px;">
      <input type="text" id="s-title" placeholder=" " maxlength="80">
      <label>Page title</label>
      <div class="helper" data-slug-title-len>0 chars · Google shows ~60</div>
    </div>
    <div class="field">
      <textarea rows="2" id="s-desc" placeholder=" " maxlength="200"></textarea>
      <label>Meta description</label>
      <div class="helper" data-slug-desc-len>0 chars · Google shows ~155</div>
    </div>
    <div class="field">
      <input type="text" id="s-base" placeholder=" " value="afrostrength.com">
      <label>Site domain (no protocol)</label>
    </div>
  </div>

  <div class="tool-stage__panel" style="background:#fff;">
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;" data-slug-tabs>
      <button type="button" class="skill-pill is-selected" data-tab="google">Google</button>
      <button type="button" class="skill-pill" data-tab="x">X / Twitter</button>
      <button type="button" class="skill-pill" data-tab="og">Facebook / LinkedIn</button>
    </div>

    <!-- Google -->
    <div data-tab-panel="google">
      <div class="eyebrow">GOOGLE SEARCH SNIPPET</div>
      <div style="margin-top:14px;padding:18px;border:1px solid var(--hairline);border-radius:12px;background:#fff;">
        <div data-slug-url style="font-size:12.5px;color:#0A6F26;line-height:1.4;">afrostrength.com › <span data-slug-slug>your-slug</span></div>
        <div data-slug-h   style="font-family:Arial,sans-serif;font-size:18px;line-height:1.3;color:#1a0dab;margin:6px 0 4px;text-decoration:underline;cursor:pointer;">Your page title appears here · Afrostrength</div>
        <div data-slug-d   style="font-family:Arial,sans-serif;font-size:13px;line-height:1.55;color:#4d5156;">Your meta description appears here.</div>
      </div>
    </div>

    <!-- X / Twitter -->
    <div data-tab-panel="x" style="display:none;">
      <div class="eyebrow">X · TWITTER CARD</div>
      <div style="margin-top:14px;border:1px solid var(--hairline);border-radius:14px;overflow:hidden;background:#fff;">
        <div data-slug-card-img style="aspect-ratio:1.91/1;background:linear-gradient(135deg,#C0392B,#8B0000);background-size:cover;background-position:center;"></div>
        <div style="padding:12px 14px;">
          <div data-slug-card-domain style="font-family:Arial,sans-serif;font-size:11px;color:#536471;">afrostrength.com</div>
          <div data-slug-card-title  style="font-family:Arial,sans-serif;font-size:15px;color:#0f1419;line-height:1.3;margin:2px 0;">Your page title appears here · Afrostrength</div>
        </div>
      </div>
    </div>

    <!-- Facebook / LinkedIn / OG -->
    <div data-tab-panel="og" style="display:none;">
      <div class="eyebrow">FACEBOOK · LINKEDIN · OG</div>
      <div style="margin-top:14px;border:1px solid var(--hairline);border-radius:8px;overflow:hidden;background:#fff;">
        <div data-slug-card-img-2 style="aspect-ratio:1.91/1;background:linear-gradient(135deg,#C0392B,#8B0000);background-size:cover;background-position:center;"></div>
        <div style="padding:10px 12px;background:#f0f2f5;">
          <div data-slug-card-domain-2 style="font-family:Helvetica,Arial,sans-serif;font-size:11px;text-transform:uppercase;color:#65676B;letter-spacing:0.06em;">afrostrength.com</div>
          <div data-slug-card-title-2  style="font-family:Helvetica,Arial,sans-serif;font-size:16px;font-weight:600;color:#1c1e21;line-height:1.25;margin:2px 0 4px;">Your page title appears here · Afrostrength</div>
          <div data-slug-card-desc-2   style="font-family:Helvetica,Arial,sans-serif;font-size:12px;color:#65676B;line-height:1.4;">Your meta description appears here.</div>
        </div>
      </div>
    </div>

    <div class="field" style="margin-top:18px;">
      <input type="url" id="s-og" placeholder=" " inputmode="url">
      <label>OG image URL (optional — uses crimson gradient if blank)</label>
    </div>

    <div class="eyebrow" style="margin-top:18px;">// CLEAN SLUG</div>
    <div class="tool-result__code" style="margin:10px 0 0;" data-slug-out>your-slug</div>
    <button class="btn btn-primary btn-sm" type="button" data-slug-copy style="margin-top:12px;">Copy slug</button>
    <button class="btn btn-ghost btn-sm" type="button" data-slug-copy-meta style="margin-top:12px;">Copy &lt;meta&gt; tags</button>
    <div class="caption" data-slug-status style="margin-top:10px;"></div>
  </div>
</section>

<?php partial('promo-academy'); ?>

<script>
  (function(){
    var t = document.getElementById('s-title');
    var d = document.getElementById('s-desc');
    var base = document.getElementById('s-base');
    var og   = document.getElementById('s-og');
    var tlen = document.querySelector('[data-slug-title-len]');
    var dlen = document.querySelector('[data-slug-desc-len]');
    var pH   = document.querySelector('[data-slug-h]');
    var pD   = document.querySelector('[data-slug-d]');
    var pU   = document.querySelector('[data-slug-url]');
    var pS   = document.querySelector('[data-slug-slug]');
    var out  = document.querySelector('[data-slug-out]');
    var copy = document.querySelector('[data-slug-copy]');
    var copyMeta = document.querySelector('[data-slug-copy-meta]');
    var status = document.querySelector('[data-slug-status]');

    // Tabs
    document.querySelectorAll('[data-slug-tabs] button').forEach(function(b){
      b.addEventListener('click', function(){
        document.querySelectorAll('[data-slug-tabs] button').forEach(function(x){ x.classList.remove('is-selected'); });
        b.classList.add('is-selected');
        document.querySelectorAll('[data-tab-panel]').forEach(function(p){ p.style.display = (p.dataset.tabPanel === b.dataset.tab) ? '' : 'none'; });
      });
    });

    function slugify(s) {
      return (s||'')
        .toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    }
    function render() {
      var title = t.value || 'Your page title appears here · Afrostrength';
      var desc  = d.value || 'Your meta description appears here. Aim for 150–160 characters to fill the search snippet without truncation.';
      var slug  = slugify(t.value) || 'your-slug';
      var dom   = (base.value || 'afrostrength.com').replace(/^https?:\/\//,'');

      pH.textContent = title;
      pD.textContent = desc;
      pU.innerHTML = dom + ' › <span>' + slug + '</span>';
      pS.textContent = slug;
      out.textContent = slug;

      tlen.textContent = t.value.length + ' chars · Google shows ~60' + (t.value.length > 60 ? '  ⚠ truncates' : '');
      tlen.style.color = t.value.length > 60 ? 'var(--crimson)' : 'var(--text-dim)';
      dlen.textContent = d.value.length + ' chars · Google shows ~155' + (d.value.length > 155 ? '  ⚠ truncates' : '');
      dlen.style.color = d.value.length > 155 ? 'var(--crimson)' : 'var(--text-dim)';

      // X preview
      document.querySelector('[data-slug-card-domain]').textContent = dom;
      document.querySelector('[data-slug-card-title]').textContent  = title;
      // OG preview
      document.querySelector('[data-slug-card-domain-2]').textContent = dom;
      document.querySelector('[data-slug-card-title-2]').textContent  = title;
      document.querySelector('[data-slug-card-desc-2]').textContent   = desc;

      var ogUrl = (og.value || '').trim();
      if (ogUrl) {
        document.querySelector('[data-slug-card-img]').style.backgroundImage = 'url("' + ogUrl + '")';
        document.querySelector('[data-slug-card-img-2]').style.backgroundImage = 'url("' + ogUrl + '")';
      } else {
        document.querySelector('[data-slug-card-img]').style.backgroundImage = '';
        document.querySelector('[data-slug-card-img-2]').style.backgroundImage = '';
      }
    }
    [t, d, base, og].forEach(function(el){ el.addEventListener('input', render); });

    copy.addEventListener('click', async function(){
      try { await navigator.clipboard.writeText(out.textContent); status.textContent = '// slug copied'; }
      catch(e){ status.textContent = '// select and copy manually'; }
    });
    copyMeta.addEventListener('click', async function(){
      var url = 'https://' + (base.value || 'afrostrength.com').replace(/^https?:\/\//,'') + '/' + (slugify(t.value) || 'your-slug');
      var ogu = (og.value||'').trim();
      var blob =
'<title>' + (t.value || '') + '</title>\n' +
'<meta name="description" content="' + (d.value || '').replace(/"/g,'&quot;') + '">\n' +
'<link rel="canonical" href="' + url + '">\n' +
'<meta property="og:title" content="' + (t.value || '').replace(/"/g,'&quot;') + '">\n' +
'<meta property="og:description" content="' + (d.value || '').replace(/"/g,'&quot;') + '">\n' +
'<meta property="og:url" content="' + url + '">\n' +
'<meta property="og:type" content="website">\n' +
(ogu ? '<meta property="og:image" content="' + ogu + '">\n' : '') +
'<meta name="twitter:card" content="summary_large_image">\n' +
'<meta name="twitter:title" content="' + (t.value || '').replace(/"/g,'&quot;') + '">\n' +
'<meta name="twitter:description" content="' + (d.value || '').replace(/"/g,'&quot;') + '">' +
(ogu ? '\n<meta name="twitter:image" content="' + ogu + '">' : '');
      try { await navigator.clipboard.writeText(blob); status.textContent = '// <meta> block copied (' + blob.split('\n').length + ' lines)'; }
      catch(e){ status.textContent = '// select and copy manually'; }
    });
    render();
  })();
</script>
