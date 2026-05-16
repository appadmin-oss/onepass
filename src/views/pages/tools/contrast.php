<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'A11Y',
  'eyebrow' => 'Contrast checker',
  'title'   => 'Will your colours <em>read?</em>',
  'lead'    => 'Paste two colours — hex, rgb(), or any CSS value the browser understands — and we\'ll calculate the relative luminance ratio, then tell you which WCAG levels (AA, AAA, large, body) it passes. Runs entirely in your browser.',
]); ?>

<section data-reveal>
  <div class="contrast" data-contrast>
    <!-- LIVE PREVIEW PANEL -->
    <div class="contrast__preview" data-contrast-preview>
      <div class="contrast__sample contrast__sample--body">
        <span class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;opacity:.7;">Body 16 px</span>
        <p style="font-size:16px;line-height:1.55;margin:8px 0 0;">
          The legacy you're building can be felt in the silence between decisions.
        </p>
      </div>
      <div class="contrast__sample contrast__sample--large">
        <span class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;opacity:.7;">Large 24 px</span>
        <p style="font-family:'Garet',sans-serif;font-weight:600;font-size:24px;line-height:1.15;letter-spacing:-0.01em;margin:8px 0 0;">
          Strong type, calm presence.
        </p>
      </div>
    </div>

    <!-- CONTROLS -->
    <form class="contrast__controls" data-smart onsubmit="return false;">
      <div class="contrast__row">
        <label class="field field--auth" for="fg">
          <span class="field__label">Foreground</span>
          <div class="contrast__swatch-row">
            <input type="color" data-contrast-fg-picker value="#0A0A0A" aria-label="Foreground colour picker">
            <input id="fg" type="text" name="fg" data-contrast-fg value="#0A0A0A" autocomplete="off" spellcheck="false">
          </div>
        </label>
        <label class="field field--auth" for="bg">
          <span class="field__label">Background</span>
          <div class="contrast__swatch-row">
            <input type="color" data-contrast-bg-picker value="#FAFAFA" aria-label="Background colour picker">
            <input id="bg" type="text" name="bg" data-contrast-bg value="#FAFAFA" autocomplete="off" spellcheck="false">
          </div>
        </label>
      </div>

      <button type="button" class="contrast__swap" data-contrast-swap aria-label="Swap foreground and background">
        ⇅ Swap
      </button>

      <div class="contrast__ratio" data-contrast-ratio aria-live="polite">
        <div class="contrast__ratio-num">—</div>
        <div class="contrast__ratio-label">Contrast ratio</div>
      </div>

      <div class="contrast__grades" data-contrast-grades aria-live="polite">
        <div class="contrast__grade" data-grade="aa-body">
          <span class="contrast__grade-h">AA · Body</span>
          <span class="contrast__grade-need">≥ 4.5</span>
          <span class="contrast__grade-state">—</span>
        </div>
        <div class="contrast__grade" data-grade="aa-large">
          <span class="contrast__grade-h">AA · Large</span>
          <span class="contrast__grade-need">≥ 3.0</span>
          <span class="contrast__grade-state">—</span>
        </div>
        <div class="contrast__grade" data-grade="aaa-body">
          <span class="contrast__grade-h">AAA · Body</span>
          <span class="contrast__grade-need">≥ 7.0</span>
          <span class="contrast__grade-state">—</span>
        </div>
        <div class="contrast__grade" data-grade="aaa-large">
          <span class="contrast__grade-h">AAA · Large</span>
          <span class="contrast__grade-need">≥ 4.5</span>
          <span class="contrast__grade-state">—</span>
        </div>
      </div>

      <details class="contrast__preset">
        <summary>Brand presets</summary>
        <div class="contrast__presets-grid">
          <button type="button" data-preset="0A0A0A:FAFAFA">Ink on Bone</button>
          <button type="button" data-preset="FAFAFA:0A0A0A">Bone on Ink</button>
          <button type="button" data-preset="C0392B:FAFAFA">Crimson on Bone</button>
          <button type="button" data-preset="FFFFFF:C0392B">White on Crimson</button>
          <button type="button" data-preset="0A0A0A:FCE7DD">Ink on Peach</button>
          <button type="button" data-preset="5C0000:FAFAFA">Maroon on Bone</button>
        </div>
      </details>
    </form>
  </div>
</section>

<script>
(function () {
  'use strict';
  // Resolve any CSS colour string into [r,g,b] by drawing it to a 1×1 canvas.
  // This means we accept hex, rgb(), rgba(), hsl(), named colours, anything.
  const probe = document.createElement('canvas');
  probe.width = probe.height = 1;
  const ctx = probe.getContext('2d', { willReadFrequently: true });
  function parseColor(input) {
    if (!input) return null;
    let s = String(input).trim();
    if (/^[0-9a-f]{3}$|^[0-9a-f]{6}$/i.test(s)) s = '#' + s;
    ctx.clearRect(0, 0, 1, 1);
    ctx.fillStyle = '#000';   // reset to a known value
    ctx.fillStyle = s;
    if (ctx.fillStyle === '#000000' && s.toLowerCase() !== '#000000' && s.toLowerCase() !== 'black') {
      return null;
    }
    ctx.fillRect(0, 0, 1, 1);
    const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
    return [r, g, b];
  }
  function rgbToHex([r, g, b]) {
    return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('').toUpperCase();
  }
  // WCAG 2.2 relative-luminance formula
  function relLum([r, g, b]) {
    const a = [r, g, b].map(v => {
      v /= 255;
      return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
  }
  function ratio(fg, bg) {
    const L1 = relLum(fg), L2 = relLum(bg);
    const [hi, lo] = L1 > L2 ? [L1, L2] : [L2, L1];
    return (hi + 0.05) / (lo + 0.05);
  }

  const root  = document.querySelector('[data-contrast]');
  if (!root) return;
  const fg    = root.querySelector('[data-contrast-fg]');
  const bg    = root.querySelector('[data-contrast-bg]');
  const fgPick = root.querySelector('[data-contrast-fg-picker]');
  const bgPick = root.querySelector('[data-contrast-bg-picker]');
  const preview = root.querySelector('[data-contrast-preview]');
  const numEl   = root.querySelector('[data-contrast-ratio] .contrast__ratio-num');
  const grades  = root.querySelectorAll('[data-grade]');
  const swap    = root.querySelector('[data-contrast-swap]');

  function applyGrade(el, pass, ratioVal) {
    el.dataset.state = pass ? 'pass' : 'fail';
    el.querySelector('.contrast__grade-state').textContent = pass ? 'Pass' : 'Fail';
  }
  function update() {
    const fgRgb = parseColor(fg.value) || [10, 10, 10];
    const bgRgb = parseColor(bg.value) || [250, 250, 250];
    const fgHex = rgbToHex(fgRgb), bgHex = rgbToHex(bgRgb);
    // Sync the color pickers (they only accept #rrggbb).
    fgPick.value = fgHex; bgPick.value = bgHex;
    preview.style.color = fgHex;
    preview.style.background = bgHex;
    const r = ratio(fgRgb, bgRgb);
    numEl.textContent = r.toFixed(2) + ' : 1';
    grades.forEach(g => {
      const id = g.dataset.grade;
      const threshold = ({ 'aa-body': 4.5, 'aa-large': 3.0, 'aaa-body': 7.0, 'aaa-large': 4.5 })[id];
      applyGrade(g, r >= threshold, r);
    });
  }
  function setHex(which, hex) {
    if (which === 'fg') fg.value = hex; else bg.value = hex;
    update();
  }
  fg.addEventListener('input', update);
  bg.addEventListener('input', update);
  fgPick.addEventListener('input', () => setHex('fg', fgPick.value.toUpperCase()));
  bgPick.addEventListener('input', () => setHex('bg', bgPick.value.toUpperCase()));
  swap.addEventListener('click', () => {
    const a = fg.value, b = bg.value;
    fg.value = b; bg.value = a; update();
  });
  root.querySelectorAll('[data-preset]').forEach(btn => {
    btn.addEventListener('click', () => {
      const [f, b] = btn.dataset.preset.split(':');
      fg.value = '#' + f; bg.value = '#' + b; update();
    });
  });
  update();
})();
</script>
