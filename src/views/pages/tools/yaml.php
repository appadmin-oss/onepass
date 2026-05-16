<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'YAML',
  'eyebrow' => 'JSON ↔ YAML converter',
  'title'   => 'Same shape. <em>Different syntax.</em>',
  'lead'    => 'Paste into either side. The other side updates as you type. Powered by js-yaml — the same parser GitHub Actions and Kubernetes use. Everything stays in your browser.',
]); ?>

<section data-reveal>
  <div class="yam" data-yam>
    <div class="yam__pane">
      <header><span class="ff-mono">// JSON</span><span class="yam__err" data-yam-err-json></span></header>
      <textarea data-yam-json spellcheck="false" autocomplete="off" autocapitalize="off">{
  "site": "Afrostrength",
  "tracks": [
    {"slug": "cybersecurity", "weeks": 12},
    {"slug": "software-development", "weeks": 14}
  ]
}</textarea>
    </div>
    <div class="yam__pane">
      <header><span class="ff-mono">// YAML</span><span class="yam__err" data-yam-err-yaml></span></header>
      <textarea data-yam-yaml spellcheck="false" autocomplete="off" autocapitalize="off"></textarea>
    </div>
  </div>
  <div class="yam__bar">
    <button type="button" class="auth-form__link" data-yam-copy="json">Copy JSON</button>
    <button type="button" class="auth-form__link" data-yam-copy="yaml">Copy YAML</button>
    <span class="caption">Parsed locally via <code>js-yaml</code> · we never receive the content</span>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/js-yaml@4.1.0/dist/js-yaml.min.js" defer></script>
<script>
(function () {
  'use strict';
  const $ = (s) => document.querySelector(s);
  const jsonEl = $('[data-yam-json]'), yamlEl = $('[data-yam-yaml]');
  const errJ = $('[data-yam-err-json]'), errY = $('[data-yam-err-yaml]');
  let lock = null;            // 'json' | 'yaml' — which side we're echoing FROM
  function whenReady(fn){ let t=0;const i=setInterval(()=>{ if(window.jsYaml){clearInterval(i);fn();}else if((t+=80)>3000){clearInterval(i);} },80); }
  whenReady(function () {
    function fromJson() {
      if (lock === 'yaml') return;
      lock = 'json';
      errJ.textContent = ''; errY.textContent = '';
      try {
        const data = JSON.parse(jsonEl.value || 'null');
        yamlEl.value = window.jsYaml.dump(data, { indent: 2, lineWidth: 100 });
      } catch (e) { errJ.textContent = e.message; }
      setTimeout(() => { lock = null; }, 0);
    }
    function fromYaml() {
      if (lock === 'json') return;
      lock = 'yaml';
      errJ.textContent = ''; errY.textContent = '';
      try {
        const data = window.jsYaml.load(yamlEl.value || '');
        jsonEl.value = JSON.stringify(data ?? null, null, 2);
      } catch (e) { errY.textContent = e.message; }
      setTimeout(() => { lock = null; }, 0);
    }
    jsonEl.addEventListener('input', fromJson);
    yamlEl.addEventListener('input', fromYaml);
    fromJson();
    document.querySelectorAll('[data-yam-copy]').forEach(b => {
      b.addEventListener('click', async () => {
        const side = b.dataset.yamCopy;
        try {
          await navigator.clipboard.writeText(side === 'json' ? jsonEl.value : yamlEl.value);
          const t = b.textContent; b.textContent = 'Copied ✓'; setTimeout(()=>b.textContent=t, 1200);
        } catch (_) {}
      });
    });
  });
})();
</script>
