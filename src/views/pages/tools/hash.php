<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'HASH',
  'eyebrow' => 'Cryptographic hash generator',
  'title'   => 'One input. <em>Five fingerprints.</em>',
  'lead'    => 'MD5, SHA-1, SHA-256, SHA-384 and SHA-512. Type, paste, or drop a file — everything is computed in your browser via Web Crypto. We never receive the content.',
]); ?>

<section data-reveal>
  <div class="hash" data-hash>
    <div class="hash__input">
      <label class="field field--auth">
        <span class="field__label">Text input</span>
        <textarea data-hash-text rows="4" spellcheck="false" placeholder="Type or paste anything…">Building brands, strengthening legacies.</textarea>
      </label>
      <label class="hash__file">
        <input type="file" data-hash-file>
        <span>or drop a file here · <em data-hash-file-meta>no file</em></span>
      </label>
    </div>

    <div class="hash__rows">
      <?php foreach (['md5','sha-1','sha-256','sha-384','sha-512'] as $alg): ?>
        <div class="hash__row" data-hash-row="<?= e($alg) ?>">
          <span class="hash__alg"><?= e(strtoupper($alg)) ?></span>
          <code class="hash__val" data-hash-val>—</code>
          <button type="button" class="auth-form__link" data-hash-copy aria-label="Copy <?= e(strtoupper($alg)) ?>">Copy</button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SparkMD5 only — SHA-* uses native Web Crypto, no CDN needed. -->
<script src="https://cdn.jsdelivr.net/npm/spark-md5@3.0.2/spark-md5.min.js" defer></script>
<script>
(function () {
  'use strict';
  const text = document.querySelector('[data-hash-text]');
  const file = document.querySelector('[data-hash-file]');
  const fileMeta = document.querySelector('[data-hash-file-meta]');
  const rows = document.querySelectorAll('[data-hash-row]');
  const subtle = window.crypto && window.crypto.subtle;

  function bufToHex(buf) {
    return Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2, '0')).join('');
  }
  async function sha(name, bytes) {
    if (!subtle) return null;
    const buf = await subtle.digest(name, bytes);
    return bufToHex(buf);
  }
  async function md5(bytes) {
    if (!window.SparkMD5) return null;
    // ArrayBuffer or Uint8Array → we want the binary string SparkMD5 expects
    return window.SparkMD5.ArrayBuffer.hash(bytes instanceof ArrayBuffer ? bytes : bytes.buffer);
  }

  function setVal(alg, v) {
    const row = document.querySelector('[data-hash-row="' + alg + '"]');
    row.querySelector('[data-hash-val]').textContent = v || '—';
  }
  async function computeAll(bytes) {
    try { setVal('md5',     await md5(bytes) || 'lib loading…'); } catch (e) { setVal('md5', 'error'); }
    try { setVal('sha-1',   await sha('SHA-1', bytes)); }    catch (e) { setVal('sha-1', 'error'); }
    try { setVal('sha-256', await sha('SHA-256', bytes)); }  catch (e) { setVal('sha-256', 'error'); }
    try { setVal('sha-384', await sha('SHA-384', bytes)); }  catch (e) { setVal('sha-384', 'error'); }
    try { setVal('sha-512', await sha('SHA-512', bytes)); }  catch (e) { setVal('sha-512', 'error'); }
  }

  function fromText() {
    const bytes = new TextEncoder().encode(text.value || '');
    computeAll(bytes);
  }
  text.addEventListener('input', fromText);
  file.addEventListener('change', async () => {
    const f = file.files[0];
    if (!f) return;
    fileMeta.textContent = f.name + ' · ' + (f.size/1024).toFixed(1) + ' KB';
    const bytes = await f.arrayBuffer();
    computeAll(bytes);
  });

  document.querySelectorAll('[data-hash-copy]').forEach(b => {
    b.addEventListener('click', async () => {
      const v = b.closest('[data-hash-row]').querySelector('[data-hash-val]').textContent;
      try { await navigator.clipboard.writeText(v); b.textContent = 'Copied'; setTimeout(()=>b.textContent='Copy', 900); } catch (_) {}
    });
  });

  // Initial render once SparkMD5 lands; SHA-* doesn't need to wait.
  fromText();
  (function poll() {
    let t = 0;
    const i = setInterval(() => {
      if (window.SparkMD5) { clearInterval(i); fromText(); }
      else if ((t += 100) >= 3000) clearInterval(i);
    }, 100);
  })();
})();
</script>
