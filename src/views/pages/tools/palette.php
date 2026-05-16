<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<header data-reveal style="margin:32px 0 24px;text-align:center;max-width:680px;margin-left:auto;margin-right:auto;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// TOOL · PALETTE EXTRACTOR</div>
  <h1 class="h-display-2" style="margin:14px 0 12px;">Pull a palette <em class="grad-text">from any image.</em></h1>
  <p class="body-l" style="color:var(--text-mute);max-width:60ch;margin:0 auto;">
    Drop an image, get six dominant colours via k-means clustering — plus auto-generated brand harmonies and WCAG contrast scores. Runs entirely in your browser.
  </p>
</header>

<section class="tool-stage" data-reveal>
  <div class="tool-stage__panel">
    <div class="eyebrow">SOURCE</div>
    <label data-palette-drop
      style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:32px;border:2px dashed var(--hairline);border-radius:14px;margin-top:14px;cursor:pointer;transition:border-color .2s ease, background .2s ease;">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <path d="M17 8l-5-5-5 5M12 3v12"/>
      </svg>
      <span style="font-weight:600;font-size:14px;">Drop or click to upload an image</span>
      <span class="caption">JPG, PNG, WEBP — up to 6 MB. Or paste an Unsplash URL below.</span>
      <input type="file" accept="image/*" data-palette-file style="display:none;">
    </label>
    <div class="field" style="margin-top:14px;">
      <input type="url" id="p-url" placeholder=" " inputmode="url">
      <label>Or paste an image URL (Unsplash, etc.)</label>
    </div>
    <button type="button" class="btn btn-ghost btn-sm" data-palette-fetch>Load URL</button>
    <button type="button" class="btn btn-ghost btn-sm" data-palette-sample>Use a sample</button>

    <div data-palette-preview style="margin-top:18px;display:none;">
      <img alt="Selected image" style="width:100%;border-radius:10px;border:1px solid var(--hairline);"/>
    </div>
  </div>

  <div class="tool-stage__panel tool-stage__result">
    <div data-palette-empty>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// READY WHEN YOU ARE</div>
      <p class="ff-display" style="font-weight:600;font-size:24px;line-height:1.2;letter-spacing:-0.015em;margin:14px 0 0;max-width:26ch;">Your palette appears here.</p>
    </div>
    <div data-palette-out style="display:none;width:100%;text-align:left;">
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;margin-bottom:10px;">// DOMINANT COLOURS</div>
      <div data-palette-swatches style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;"></div>

      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;margin:24px 0 10px;">// HARMONIES (FROM ANCHOR)</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;" data-palette-harmony-modes>
        <button type="button" class="skill-pill is-selected" data-harmony="complementary">Complementary</button>
        <button type="button" class="skill-pill" data-harmony="triadic">Triadic</button>
        <button type="button" class="skill-pill" data-harmony="analogous">Analogous</button>
        <button type="button" class="skill-pill" data-harmony="split">Split</button>
      </div>
      <div data-palette-harmony style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;"></div>

      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;margin:24px 0 10px;">// WCAG CONTRAST · against ink</div>
      <div data-palette-wcag style="display:flex;flex-direction:column;gap:6px;"></div>

      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:24px;">
        <select data-palette-format style="height:36px;padding:0 12px;border:1px solid var(--hairline);border-radius:999px;font:inherit;font-size:13px;background:#fff;">
          <option value="css" selected>CSS variables</option>
          <option value="scss">SCSS variables</option>
          <option value="tailwind">Tailwind config</option>
          <option value="json">JSON array</option>
          <option value="hexlist">Plain hex list</option>
        </select>
        <button type="button" class="btn btn-primary btn-sm" data-palette-copy>Copy</button>
        <button type="button" class="btn btn-ghost btn-sm" data-palette-export>Download .json</button>
      </div>
      <div class="caption" data-palette-status style="margin-top:12px;">// click any swatch to copy its hex</div>
    </div>
  </div>
</section>

<?php partial('promo-academy'); ?>

<script>
  (function(){
    var drop   = document.querySelector('[data-palette-drop]');
    var input  = document.querySelector('[data-palette-file]');
    var urlIn  = document.getElementById('p-url');
    var fetchBtn = document.querySelector('[data-palette-fetch]');
    var sampleBtn = document.querySelector('[data-palette-sample]');
    var prev   = document.querySelector('[data-palette-preview]');
    var empty  = document.querySelector('[data-palette-empty]');
    var out    = document.querySelector('[data-palette-out]');
    var grid   = document.querySelector('[data-palette-swatches]');
    var harmonyGrid  = document.querySelector('[data-palette-harmony]');
    var harmonyModes = document.querySelectorAll('[data-palette-harmony-modes] button');
    var wcagBox = document.querySelector('[data-palette-wcag]');
    var status = document.querySelector('[data-palette-status]');
    var copy   = document.querySelector('[data-palette-copy]');
    var exportBtn = document.querySelector('[data-palette-export]');

    var picks = []; // [[r,g,b], ...]
    var anchor = 0;
    var harmonyMode = 'complementary';

    ['dragover','dragenter'].forEach(function(ev){
      drop.addEventListener(ev, function(e){ e.preventDefault(); drop.style.borderColor='var(--crimson)'; drop.style.background='var(--bg-soft)'; });
    });
    ['dragleave','drop'].forEach(function(ev){
      drop.addEventListener(ev, function(e){ e.preventDefault(); drop.style.borderColor='var(--hairline)'; drop.style.background='transparent'; });
    });
    drop.addEventListener('drop', function(e){ if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]); });
    input.addEventListener('change', function(){ if (input.files[0]) handleFile(input.files[0]); });

    fetchBtn.addEventListener('click', function(){
      var u = (urlIn.value||'').trim();
      if (!/^https?:\/\//.test(u)) { urlIn.closest('.field').classList.add('shake'); setTimeout(function(){ urlIn.closest('.field').classList.remove('shake'); }, 360); return; }
      loadImage(u);
    });
    sampleBtn.addEventListener('click', function(){
      loadImage('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=900&q=80');
    });

    function handleFile(file) {
      if (!/^image\//.test(file.type)) { status.textContent = '// not an image'; return; }
      if (file.size > 6*1024*1024) { status.textContent = '// over 6 MB — try smaller'; return; }
      var reader = new FileReader();
      reader.onload = function(e){ loadImage(e.target.result); };
      reader.readAsDataURL(file);
    }
    function loadImage(src){
      var img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = function(){
        prev.querySelector('img').src = img.src;
        prev.style.display = 'block';
        extract(img);
      };
      img.onerror = function(){ status.textContent = '// could not load that URL — try a direct image URL'; };
      img.src = src;
    }

    // K-means quantisation. K=6, 6 iterations. Operates on downscaled
    // ~6400 pixels for speed.
    function extract(img){
      var W = 80, H = Math.round(W * img.height / img.width);
      var c = document.createElement('canvas'); c.width = W; c.height = H;
      var ctx = c.getContext('2d');
      try { ctx.drawImage(img, 0, 0, W, H); } catch(e){ status.textContent = '// blocked by CORS'; return; }
      var data;
      try { data = ctx.getImageData(0,0,W,H).data; } catch(e){ status.textContent = '// browser blocked pixel access (CORS)'; return; }

      var pixels = [];
      for (var i = 0; i < data.length; i += 4) {
        if (data[i+3] < 200) continue;
        pixels.push([data[i], data[i+1], data[i+2]]);
      }
      var K = 6;
      // Seed centroids by sampling
      var centroids = [];
      for (var k = 0; k < K; k++) centroids.push(pixels[Math.floor(Math.random() * pixels.length)].slice());

      for (var iter = 0; iter < 6; iter++) {
        var clusters = Array.from({length: K}, function(){ return [0,0,0,0]; });
        for (var p = 0; p < pixels.length; p++) {
          var pix = pixels[p], best = 0, bestDist = Infinity;
          for (var ck = 0; ck < K; ck++) {
            var dx = pix[0]-centroids[ck][0], dy = pix[1]-centroids[ck][1], dz = pix[2]-centroids[ck][2];
            var d = dx*dx + dy*dy + dz*dz;
            if (d < bestDist) { bestDist = d; best = ck; }
          }
          clusters[best][0] += pix[0]; clusters[best][1] += pix[1];
          clusters[best][2] += pix[2]; clusters[best][3] += 1;
        }
        for (var ci = 0; ci < K; ci++) {
          if (clusters[ci][3] > 0) {
            centroids[ci] = [
              Math.round(clusters[ci][0] / clusters[ci][3]),
              Math.round(clusters[ci][1] / clusters[ci][3]),
              Math.round(clusters[ci][2] / clusters[ci][3]),
            ];
          }
        }
      }
      // Sort by cluster size (frequency) desc
      var counts = Array.from({length:K}, function(){return 0;});
      for (var pp = 0; pp < pixels.length; pp++) {
        var px = pixels[pp], bIdx = 0, bDist = Infinity;
        for (var cck = 0; cck < K; cck++) {
          var ddx = px[0]-centroids[cck][0], ddy = px[1]-centroids[cck][1], ddz = px[2]-centroids[cck][2];
          var dd = ddx*ddx + ddy*ddy + ddz*ddz;
          if (dd < bDist) { bDist = dd; bIdx = cck; }
        }
        counts[bIdx]++;
      }
      var idx = centroids.map(function(_,n){ return n; });
      idx.sort(function(a,b){ return counts[b] - counts[a]; });
      picks = idx.map(function(n){ return centroids[n]; });
      anchor = 0;
      render();
    }

    // --- Colour space helpers
    function toHex(r,g,b){ return '#' + [r,g,b].map(function(x){ return ('0'+x.toString(16)).slice(-2);}).join('').toUpperCase(); }
    function rgb2hsl(r,g,b){
      r/=255; g/=255; b/=255;
      var mx = Math.max(r,g,b), mn = Math.min(r,g,b), h, s, l = (mx+mn)/2;
      if (mx===mn) { h=s=0; }
      else {
        var d = mx-mn;
        s = l > 0.5 ? d/(2-mx-mn) : d/(mx+mn);
        switch(mx){ case r: h=(g-b)/d+(g<b?6:0); break; case g: h=(b-r)/d+2; break; default: h=(r-g)/d+4; }
        h /= 6;
      }
      return [h, s, l];
    }
    function hsl2rgb(h,s,l){
      var r,g,b;
      if (s===0) { r=g=b=l; }
      else {
        function hue2rgb(p,q,t){ if(t<0)t+=1; if(t>1)t-=1; if(t<1/6)return p+(q-p)*6*t; if(t<1/2)return q; if(t<2/3)return p+(q-p)*(2/3-t)*6; return p; }
        var q = l<0.5?l*(1+s):l+s-l*s; var p = 2*l-q;
        r = hue2rgb(p,q,h+1/3); g = hue2rgb(p,q,h); b = hue2rgb(p,q,h-1/3);
      }
      return [Math.round(r*255), Math.round(g*255), Math.round(b*255)];
    }
    function rotateHue(rgb, deg){
      var hsl = rgb2hsl(rgb[0],rgb[1],rgb[2]);
      return hsl2rgb(((hsl[0]*360 + deg) % 360 + 360) % 360 / 360, hsl[1], hsl[2]);
    }
    function relLum(r,g,b){
      var c = [r,g,b].map(function(v){ v/=255; return v<=.03928?v/12.92:Math.pow((v+.055)/1.055,2.4); });
      return 0.2126*c[0] + 0.7152*c[1] + 0.0722*c[2];
    }
    function contrastRatio(a,b){
      var l1 = relLum(a[0],a[1],a[2]), l2 = relLum(b[0],b[1],b[2]);
      var [lo,hi] = l1>l2?[l2,l1]:[l1,l2];
      return (hi+0.05)/(lo+0.05);
    }
    function wcag(c){
      if (c >= 7) return ['AAA', '#1F8A5B'];
      if (c >= 4.5) return ['AA', '#1F8A5B'];
      if (c >= 3) return ['AA Large', '#C9851A'];
      return ['Fail', '#C0392B'];
    }

    function render(){
      empty.style.display = 'none';
      out.style.display = 'block';
      grid.innerHTML = '';
      picks.forEach(function(c, i){
        var hex = toHex(c[0],c[1],c[2]);
        var cell = document.createElement('button');
        cell.type = 'button';
        cell.style.cssText = 'border:0;border-radius:10px;height:96px;cursor:pointer;display:flex;flex-direction:column;justify-content:flex-end;padding:10px 12px;text-align:left;background:'+hex+';font:inherit;position:relative;';
        var lum = relLum(c[0],c[1],c[2]);
        cell.style.color = lum > 0.55 ? '#0A0A0A' : '#fff';
        if (i === anchor) cell.style.outline = '3px solid var(--crimson)';
        cell.innerHTML = '<span class="ff-mono" style="font-size:10px;letter-spacing:0.14em;text-transform:uppercase;opacity:0.78;">'+(i===anchor?'★ anchor':'0'+(i+1))+'</span><strong style="font-family:Garet,sans-serif;font-size:14px;letter-spacing:0.04em;">'+hex+'</strong>';
        cell.addEventListener('click', async function(){
          anchor = i;
          try { await navigator.clipboard.writeText(hex); status.textContent = '// '+hex+' copied · anchor moved'; }
          catch(e){ status.textContent = '// anchor moved'; }
          render();
        });
        grid.appendChild(cell);
      });
      renderHarmony();
      renderWcag();
    }

    function renderHarmony(){
      harmonyGrid.innerHTML = '';
      var base = picks[anchor];
      var rotations = harmonyMode === 'complementary' ? [180]
                    : harmonyMode === 'triadic'       ? [120, 240]
                    : harmonyMode === 'analogous'     ? [-30, 30]
                    : /* split */                       [150, 210];
      var cols = [base].concat(rotations.map(function(deg){ return rotateHue(base, deg); }));
      cols.forEach(function(c){
        var hex = toHex(c[0],c[1],c[2]);
        var cell = document.createElement('button');
        cell.type = 'button';
        cell.style.cssText = 'border:0;border-radius:10px;height:64px;cursor:pointer;background:'+hex+';font:inherit;color:'+(relLum(c[0],c[1],c[2])>0.55?'#0A0A0A':'#fff')+';text-align:left;padding:10px 12px;display:flex;align-items:flex-end;';
        cell.innerHTML = '<strong style="font-family:Garet,sans-serif;font-size:12px;letter-spacing:0.04em;">'+hex+'</strong>';
        cell.addEventListener('click', async function(){
          try { await navigator.clipboard.writeText(hex); status.textContent = '// '+hex+' copied'; }
          catch(e){}
        });
        harmonyGrid.appendChild(cell);
      });
    }

    function renderWcag(){
      wcagBox.innerHTML = '';
      var ink = [10,10,10], bone = [250,250,250];
      picks.forEach(function(c){
        var hex = toHex(c[0],c[1],c[2]);
        var cInk = contrastRatio(c, ink), cBone = contrastRatio(c, bone);
        var [lInk, gInk]  = wcag(cInk);
        var [lBon, gBon] = wcag(cBone);
        var row = document.createElement('div');
        row.style.cssText = 'display:grid;grid-template-columns:24px 1fr auto auto;gap:10px;align-items:center;font-size:12px;';
        row.innerHTML =
          '<span style="width:18px;height:18px;border-radius:4px;background:'+hex+';"></span>' +
          '<code style="font-family:JetBrains Mono,monospace;">'+hex+'</code>' +
          '<span style="font-family:JetBrains Mono,monospace;color:'+gInk+';">'+cInk.toFixed(1)+' · '+lInk+' on ink</span>' +
          '<span style="font-family:JetBrains Mono,monospace;color:'+gBon+';">'+cBone.toFixed(1)+' · '+lBon+' on bone</span>';
        wcagBox.appendChild(row);
      });
    }

    harmonyModes.forEach(function(b){
      b.addEventListener('click', function(){
        harmonyModes.forEach(function(x){ x.classList.remove('is-selected'); });
        b.classList.add('is-selected');
        harmonyMode = b.dataset.harmony;
        renderHarmony();
      });
    });

    var formatEl = document.querySelector('[data-palette-format]');
    function exportText(fmt) {
      var hexes = picks.map(function (c) { return toHex(c[0], c[1], c[2]); });
      switch (fmt) {
        case 'scss':
          return hexes.map(function (h, i) { return '$c-' + (i + 1) + ': ' + h + ';'; }).join('\n');
        case 'tailwind':
          return 'module.exports = {\n  theme: {\n    extend: {\n      colors: {\n        brand: {\n'
               + hexes.map(function (h, i) { return '          c' + (i + 1) + ': \'' + h + '\','; }).join('\n')
               + '\n        }\n      }\n    }\n  }\n};';
        case 'json':
          return JSON.stringify({ palette: hexes }, null, 2);
        case 'hexlist':
          return hexes.join('\n');
        case 'css':
        default:
          return ':root {\n' + hexes.map(function (h, i) { return '  --c-' + (i + 1) + ': ' + h + ';'; }).join('\n') + '\n}';
      }
    }
    copy.addEventListener('click', async function () {
      var fmt = formatEl ? formatEl.value : 'css';
      try {
        await navigator.clipboard.writeText(exportText(fmt));
        status.textContent = '// ' + fmt.toUpperCase() + ' copied · ' + picks.length + ' colours';
      } catch (e) { status.textContent = '// copy failed'; }
    });
    exportBtn.addEventListener('click', function(){
      var blob = new Blob([JSON.stringify({ palette: picks.map(function(c){ return toHex(c[0],c[1],c[2]); }) }, null, 2)], { type: 'application/json' });
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'afrostrength-palette.json';
      a.click();
      URL.revokeObjectURL(a.href);
    });
  })();
</script>
