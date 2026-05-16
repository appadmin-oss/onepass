<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'ICON',
  'eyebrow' => 'Favicon set generator',
  'title'   => 'Ship a complete <em>favicon set</em> in 30 seconds.',
  'lead'    => 'Drop a square PNG, JPG, or WebP. We resample it to the four sizes browsers, iOS and Android actually use — 32, 180, 192, 512 — preview each one in context, and let you download them individually. Nothing leaves your browser.',
]); ?>

<section data-reveal>
  <div class="favicon" data-favicon>

    <div class="favicon__drop" data-favicon-drop tabindex="0" role="button" aria-label="Upload an image">
      <input type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" data-favicon-input hidden>
      <div class="favicon__drop-inner" data-favicon-empty>
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
          <path d="M12 16V4M7 9l5-5 5 5"/>
          <path d="M5 20h14"/>
        </svg>
        <p style="margin:14px 0 6px;font-family:'Garet',sans-serif;font-weight:600;font-size:17px;">Drop an image</p>
        <p style="margin:0;font-size:13px;color:var(--text-mute);">PNG, JPG, WebP, or SVG · square works best · ≥ 512 × 512 ideal</p>
        <button type="button" class="btn btn-ghost btn-sm" style="margin-top:18px;" data-favicon-pick>Or browse files</button>
      </div>
      <div class="favicon__drop-loaded" data-favicon-loaded hidden>
        <canvas data-favicon-source width="512" height="512" aria-hidden="true"></canvas>
        <div class="favicon__meta">
          <div data-favicon-meta>—</div>
          <button type="button" class="btn btn-ghost btn-sm" data-favicon-pick>Replace image</button>
        </div>
      </div>
    </div>

    <div class="favicon__sizes" data-favicon-sizes hidden>
      <?php
        $sizes = [
          ['px' => 32,  'label' => 'Browser tab',    'sub' => 'favicon-32.png · &lt;link rel="icon"&gt;'],
          ['px' => 180, 'label' => 'iOS home screen', 'sub' => 'apple-touch-icon.png · 180 × 180'],
          ['px' => 192, 'label' => 'Android',         'sub' => 'icon-192.png · manifest src'],
          ['px' => 512, 'label' => 'PWA · install',   'sub' => 'icon-512.png · manifest src'],
        ];
        foreach ($sizes as $s):
      ?>
        <article class="favicon__card" data-favicon-card="<?= (int)$s['px'] ?>">
          <header class="favicon__card-h">
            <div>
              <span class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);"><?= (int)$s['px'] ?> px</span>
              <p style="font-family:'Garet',sans-serif;font-weight:600;font-size:15px;margin:4px 0 2px;"><?= e($s['label']) ?></p>
              <p style="font-size:11px;line-height:1.5;color:var(--text-mute);margin:0;"><?= $s['sub'] ?></p>
            </div>
            <a class="btn btn-ghost btn-sm" href="#" data-favicon-download aria-disabled="true">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 4v12M7 11l5 5 5-5M5 20h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>PNG</span>
            </a>
          </header>
          <div class="favicon__previews">
            <div class="favicon__preview favicon__preview--tab" aria-label="Browser tab preview">
              <span class="favicon__tab-bar">
                <span class="favicon__tab-icon"><canvas data-favicon-render="<?= (int)$s['px'] ?>" width="<?= (int)$s['px'] ?>" height="<?= (int)$s['px'] ?>"></canvas></span>
                <span class="favicon__tab-title">Afrostrength</span>
              </span>
            </div>
            <div class="favicon__preview favicon__preview--device" aria-label="Device preview">
              <span class="favicon__device-icon"><canvas data-favicon-render="<?= (int)$s['px'] ?>" width="<?= (int)$s['px'] ?>" height="<?= (int)$s['px'] ?>"></canvas></span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <details class="favicon__snippet" data-favicon-snippet hidden>
      <summary>HTML to drop into your &lt;head&gt;</summary>
<pre><code class="ff-mono">&lt;link rel="icon" type="image/png" sizes="32x32"  href="/favicon-32.png"&gt;
&lt;link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"&gt;
&lt;link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png"&gt;
&lt;link rel="icon" type="image/png" sizes="512x512" href="/icon-512.png"&gt;</code></pre>
    </details>
  </div>
</section>

<script>
(function () {
  'use strict';
  const root    = document.querySelector('[data-favicon]');
  if (!root) return;
  const drop    = root.querySelector('[data-favicon-drop]');
  const input   = root.querySelector('[data-favicon-input]');
  const empty   = root.querySelector('[data-favicon-empty]');
  const loaded  = root.querySelector('[data-favicon-loaded]');
  const source  = root.querySelector('[data-favicon-source]');
  const sizes   = root.querySelector('[data-favicon-sizes]');
  const snippet = root.querySelector('[data-favicon-snippet]');
  const metaEl  = root.querySelector('[data-favicon-meta]');
  const ctx     = source.getContext('2d');

  function showLoaded() {
    empty.hidden = true; loaded.hidden = false;
    sizes.hidden = false; snippet.hidden = false;
  }

  async function handleFile(file) {
    if (!file) return;
    if (file.size > 12 * 1024 * 1024) {
      alert('That file is over 12 MB. Use a smaller image.');
      return;
    }
    const url = URL.createObjectURL(file);
    const img = new Image();
    img.decoding = 'async';
    img.onload = () => {
      // Square-fit the source onto a 512×512 canvas so all renders share a base.
      const side = Math.min(img.width, img.height);
      const sx = (img.width - side) / 2;
      const sy = (img.height - side) / 2;
      ctx.clearRect(0, 0, 512, 512);
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(img, sx, sy, side, side, 0, 0, 512, 512);
      metaEl.textContent = img.width + ' × ' + img.height + ' source · ' + (file.size/1024).toFixed(0) + ' KB · ' + (file.type || 'image');
      URL.revokeObjectURL(url);
      renderAll();
      showLoaded();
    };
    img.onerror = () => { alert("Couldn't read that image. Try PNG, JPG, or WebP."); URL.revokeObjectURL(url); };
    img.src = url;
  }

  function renderAll() {
    root.querySelectorAll('[data-favicon-render]').forEach(c => {
      const px = parseInt(c.dataset.faviconRender, 10);
      const cx = c.getContext('2d');
      cx.imageSmoothingQuality = 'high';
      cx.clearRect(0, 0, px, px);
      cx.drawImage(source, 0, 0, px, px);
    });
    root.querySelectorAll('[data-favicon-card]').forEach(card => {
      const px = parseInt(card.dataset.faviconCard, 10);
      const dl = card.querySelector('[data-favicon-download]');
      // Build a download blob from a fresh hidden canvas (we don't toBlob the
      // preview because Safari can lose imageSmoothingQuality on rapid swap).
      const exp = document.createElement('canvas');
      exp.width = exp.height = px;
      const ex = exp.getContext('2d');
      ex.imageSmoothingQuality = 'high';
      ex.drawImage(source, 0, 0, px, px);
      exp.toBlob(blob => {
        if (!blob) return;
        const url = URL.createObjectURL(blob);
        dl.href = url;
        dl.removeAttribute('aria-disabled');
        const fname = px === 180 ? 'apple-touch-icon.png'
                    : px === 32  ? 'favicon-32.png'
                    : 'icon-' + px + '.png';
        dl.download = fname;
      }, 'image/png');
    });
  }

  drop.addEventListener('click', e => {
    if (e.target.closest('[data-favicon-pick]') || e.target === drop || e.target === empty) input.click();
  });
  drop.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
  });
  drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('is-drag'); });
  drop.addEventListener('dragleave', () => drop.classList.remove('is-drag'));
  drop.addEventListener('drop', e => {
    e.preventDefault();
    drop.classList.remove('is-drag');
    if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
  });
  input.addEventListener('change', () => handleFile(input.files[0]));
})();
</script>
