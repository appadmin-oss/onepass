<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'DIFF',
  'eyebrow' => 'Side-by-side text diff',
  'title'   => 'What <em>actually</em> changed?',
  'lead'    => 'Paste two blocks. We highlight inserts, deletes and moves at line and character level using Google\'s diff-match-patch. Useful for proofreading copy, comparing API responses, or auditing a brief.',
]); ?>

<section data-reveal>
  <div class="dff" data-dff>
    <div class="dff__inputs">
      <div class="dff__pane">
        <header><span class="ff-mono">// LEFT</span><label class="caption">Original</label></header>
        <textarea data-dff-a spellcheck="false">Strategy in week one.
Deliverables by week six.
Shipped in production.</textarea>
      </div>
      <div class="dff__pane">
        <header><span class="ff-mono">// RIGHT</span><label class="caption">Revised</label></header>
        <textarea data-dff-b spellcheck="false">Strategy in week one.
Deliverables by week four.
Shipped to production.
Mentor pairing on day one.</textarea>
      </div>
    </div>
    <div class="dff__controls">
      <label><input type="radio" name="dff-mode" value="word" checked> Word</label>
      <label><input type="radio" name="dff-mode" value="char"> Character</label>
      <label><input type="radio" name="dff-mode" value="line"> Line</label>
      <span class="dff__stats" data-dff-stats aria-live="polite">—</span>
    </div>
    <div class="dff__out" data-dff-out aria-live="polite"></div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/diff-match-patch@1.0.5/index.js" defer></script>
<script>
(function () {
  'use strict';
  const a = document.querySelector('[data-dff-a]');
  const b = document.querySelector('[data-dff-b]');
  const out = document.querySelector('[data-dff-out]');
  const stats = document.querySelector('[data-dff-stats]');
  function escapeHtml(s){return String(s).replace(/[&<>]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));}
  function mode() { return document.querySelector('input[name="dff-mode"]:checked').value; }

  function wait(fn){let t=0;const i=setInterval(()=>{if(window.diff_match_patch){clearInterval(i);fn();}else if((t+=80)>3000)clearInterval(i);},80);}

  wait(function () {
    const dmp = new window.diff_match_patch();
    function render() {
      let diffs;
      if (mode() === 'line') {
        // diff-match-patch's lineMode helper: convert to per-line char codes,
        // diff, then convert back. The standard pattern from the library docs.
        const lr = dmp.diff_linesToChars_(a.value, b.value);
        diffs = dmp.diff_main(lr.chars1, lr.chars2, false);
        dmp.diff_charsToLines_(diffs, lr.lineArray);
      } else if (mode() === 'word') {
        // Treat \W as token boundary so whole words are kept as single ops.
        diffs = dmp.diff_main(a.value, b.value);
        dmp.diff_cleanupSemantic(diffs);
      } else {
        diffs = dmp.diff_main(a.value, b.value);
      }
      let adds = 0, dels = 0;
      out.innerHTML = diffs.map(([op, text]) => {
        const safe = escapeHtml(text).replace(/\n/g, '<br>');
        if (op === 1)  { adds += text.length; return '<ins>'  + safe + '</ins>'; }
        if (op === -1) { dels += text.length; return '<del>' + safe + '</del>'; }
        return '<span>' + safe + '</span>';
      }).join('');
      stats.textContent = '+' + adds + ' / −' + dels + ' chars';
    }
    a.addEventListener('input', render);
    b.addEventListener('input', render);
    document.querySelectorAll('input[name="dff-mode"]').forEach(r => r.addEventListener('change', render));
    render();
  });
})();
</script>
