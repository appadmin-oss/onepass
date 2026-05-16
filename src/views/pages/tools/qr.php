<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<header data-reveal style="margin:32px 0 24px;text-align:center;max-width:680px;margin-left:auto;margin-right:auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// TOOL · QR CODE</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">QR codes, <em class="grad-text">brand-coloured.</em></h1>
  <p class="body-l" style="color:var(--text-mute);max-width:50ch;margin:0 auto;">
    Any URL. Brand colours. Optional Afrostrength logo overlay.
    Download a print-ready PNG with adequate error correction.
  </p>
</header>

<section class="tool-stage" data-reveal>
  <div class="tool-stage__panel">
    <div class="eyebrow">INPUT</div>
    <div class="field" style="margin-top:14px;">
      <input type="url" id="q-url" placeholder=" " inputmode="url" value="https://afrostrength.com/academy/apply">
      <label>URL or text to encode</label>
    </div>

    <div class="eyebrow" style="margin-top:18px;">// FOREGROUND</div>
    <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;" data-qr-tones>
      <button type="button" data-tone="0A0A0A" class="is-selected" data-tip="Ink"     style="width:36px;height:36px;border-radius:50%;border:2px solid transparent;background:#0A0A0A;cursor:pointer;"></button>
      <button type="button" data-tone="C0392B" data-tip="Crimson"      style="width:36px;height:36px;border-radius:50%;border:2px solid transparent;background:#C0392B;cursor:pointer;"></button>
      <button type="button" data-tone="8B0000" data-tip="Crimson deep" style="width:36px;height:36px;border-radius:50%;border:2px solid transparent;background:#8B0000;cursor:pointer;"></button>
      <button type="button" data-tone="5C0000" data-tip="Maroon"       style="width:36px;height:36px;border-radius:50%;border:2px solid transparent;background:#5C0000;cursor:pointer;"></button>
    </div>

    <div class="eyebrow" style="margin-top:18px;">// BACKGROUND</div>
    <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;" data-qr-bg>
      <button type="button" data-bg="FFFFFF" class="is-selected" data-tip="White"   style="width:36px;height:36px;border-radius:50%;border:2px solid var(--hairline);background:#FFFFFF;cursor:pointer;"></button>
      <button type="button" data-bg="FAFAFA" data-tip="Bone"               style="width:36px;height:36px;border-radius:50%;border:2px solid var(--hairline);background:#FAFAFA;cursor:pointer;"></button>
      <button type="button" data-bg="FCE7DD" data-tip="Peach"              style="width:36px;height:36px;border-radius:50%;border:2px solid var(--hairline);background:#FCE7DD;cursor:pointer;"></button>
      <button type="button" data-bg="0A0A0A" data-tip="Ink (dark mode)"    style="width:36px;height:36px;border-radius:50%;border:2px solid var(--hairline);background:#0A0A0A;cursor:pointer;"></button>
    </div>

    <div class="eyebrow" style="margin-top:18px;">// SIZE</div>
    <div style="display:flex;gap:8px;margin-top:10px;" data-qr-sizes>
      <button type="button" class="skill-pill" data-size="300">Small</button>
      <button type="button" class="skill-pill is-selected" data-size="500">Medium</button>
      <button type="button" class="skill-pill" data-size="800">Large</button>
      <button type="button" class="skill-pill" data-size="1200">Print</button>
    </div>

    <div class="eyebrow" style="margin-top:18px;">// LOGO OVERLAY</div>
    <label style="display:flex;align-items:center;gap:12px;margin-top:10px;cursor:pointer;font-size:14px;">
      <input type="checkbox" id="q-logo" checked>
      <span>Drop the Afrostrength brand mark in the centre <span class="caption">(uses high error correction so the code still scans)</span></span>
    </label>

    <div class="helper" data-qr-warn style="display:none;color:var(--warning);margin-top:14px;"></div>

    <button class="btn btn-primary" type="button" data-qr-generate style="margin-top:22px;">Generate QR <?= icon_chev() ?></button>
  </div>

  <div class="tool-stage__panel tool-stage__result">
    <div data-qr-empty>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// READY WHEN YOU ARE</div>
      <p class="ff-display" style="font-weight:600;font-size:24px;line-height:1.2;letter-spacing:-0.015em;margin:14px 0 0;max-width:24ch;">
        Your QR will appear here.
      </p>
    </div>
    <div data-qr-result style="display:none;text-align:center;width:100%;">
      <div class="tool-result__qr" data-qr-host style="position:relative;display:inline-block;">
        <img alt="QR code" data-qr-image>
        <span data-qr-logo style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);display:none;">
          <span class="grad-crimson" style="width:18%;height:18%;min-width:48px;min-height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 0 0 6px var(--bg-soft, #fff);">
            <?= icon_logo(28, 'white') ?>
          </span>
        </span>
      </div>
      <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:18px;">
        <a class="btn btn-primary" download="afrostrength-qr.png" data-qr-download>Download PNG</a>
        <button type="button" class="btn btn-ghost btn-sm" data-qr-copy-img>Copy image</button>
      </div>
      <div class="caption" style="margin-top:18px;" data-qr-status>// Print-safe at 300dpi</div>
    </div>
  </div>
</section>

<?php partial('promo-academy'); ?>

<script>
  (function(){
    var urlIn  = document.getElementById('q-url');
    var btn    = document.querySelector('[data-qr-generate]');
    var empty  = document.querySelector('[data-qr-empty]');
    var result = document.querySelector('[data-qr-result]');
    var image  = document.querySelector('[data-qr-image]');
    var dl     = document.querySelector('[data-qr-download]');
    var copyBtn = document.querySelector('[data-qr-copy-img]');
    var status = document.querySelector('[data-qr-status]');
    var warn   = document.querySelector('[data-qr-warn]');
    var tones  = document.querySelectorAll('[data-qr-tones] button');
    var bgs    = document.querySelectorAll('[data-qr-bg] button');
    var sizes  = document.querySelectorAll('[data-qr-sizes] button');
    var logoBox= document.querySelector('[data-qr-logo]');
    var logoChk= document.getElementById('q-logo');

    var tone = '0A0A0A', bg = 'FFFFFF', size = 500;

    function pickGroup(list, key, set) {
      list.forEach(function(b){
        b.addEventListener('click', function(){
          list.forEach(function(x){
            x.classList.remove('is-selected');
            if (x.style.borderColor && (x.style.borderColor !== 'rgb(178, 178, 178)')) {
              x.style.borderColor = x.style.borderColor.indexOf('transparent') !== -1 ? 'transparent' : 'var(--hairline)';
            }
          });
          b.classList.add('is-selected');
          if (b.style.background) b.style.borderColor = '#fff';
          set(b.dataset[key]);
          checkContrast();
        });
      });
    }
    pickGroup(tones, 'tone', function(v){ tone = v; });
    pickGroup(bgs,   'bg',   function(v){ bg = v; });
    pickGroup(sizes, 'size', function(v){ size = parseInt(v, 10); });

    function hexLum(hex){
      var r=parseInt(hex.slice(0,2),16),g=parseInt(hex.slice(2,4),16),b=parseInt(hex.slice(4,6),16);
      var sr=[r,g,b].map(function(v){v/=255;return v<=.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4);});
      return 0.2126*sr[0] + 0.7152*sr[1] + 0.0722*sr[2];
    }
    function contrastRatio(a, b){
      var l1 = hexLum(a), l2 = hexLum(b);
      var [lo, hi] = l1 > l2 ? [l2, l1] : [l1, l2];
      return (hi + 0.05) / (lo + 0.05);
    }
    function checkContrast(){
      var c = contrastRatio(tone, bg);
      if (c < 4) {
        warn.style.display = 'block';
        warn.textContent = '// Low contrast (' + c.toFixed(1) + ':1) — scanners may struggle. Pick a darker fg or lighter bg.';
      } else {
        warn.style.display = 'none';
      }
    }
    checkContrast();

    function generate() {
      var raw = (urlIn.value || '').trim();
      if (!raw) {
        urlIn.closest('.field').classList.add('shake');
        setTimeout(function(){ urlIn.closest('.field').classList.remove('shake'); }, 360);
        return;
      }
      var ec = logoChk.checked ? 'H' : 'M'; // high error correction when overlay'd
      var src = 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size +
        '&color=' + tone + '&bgcolor=' + bg + '&qzone=2&ecc=' + ec +
        '&data=' + encodeURIComponent(raw);
      image.onload = function(){
        empty.style.display = 'none';
        result.style.display = 'block';
        logoBox.style.display = logoChk.checked ? 'inline-flex' : 'none';
        status.textContent = '// ' + size + '×' + size + ' · error correction ' + ec + (logoChk.checked ? ' · with brand mark' : '');
      };
      image.onerror = function(){
        status.style.color = 'var(--crimson)';
        status.textContent = '// QR service unreachable — try again or pick smaller size.';
      };
      image.src = src;
      dl.href = src;
    }
    btn.addEventListener('click', generate);
    urlIn.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); generate(); } });

    copyBtn.addEventListener('click', async function(){
      try {
        var resp = await fetch(image.src);
        var blob = await resp.blob();
        await navigator.clipboard.write([new ClipboardItem({ [blob.type]: blob })]);
        status.textContent = '// image copied to clipboard';
      } catch(e) {
        status.textContent = '// download instead — clipboard image copy not supported in this browser';
      }
    });

    // Generate once on load
    generate();
  })();
</script>
