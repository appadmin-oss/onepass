<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'REGEX',
  'eyebrow' => 'Regular expression tester',
  'title'   => 'Match what you <em>mean</em> to match.',
  'lead'    => 'Real-time JavaScript regex tester. Highlights matches in your sample text, shows capture groups (named and numbered), and explains every flag. Everything runs in your browser — paste real data without worry.',
]); ?>

<section data-reveal>
  <div class="rx" data-rx>
    <div class="rx__pattern">
      <span class="rx__delim">/</span>
      <input type="text" data-rx-pattern class="rx__pattern-input"
             value="(?<scheme>https?):\/\/(?<host>[^\/\s]+)"
             autocomplete="off" spellcheck="false" autocapitalize="off"
             aria-label="Pattern">
      <span class="rx__delim">/</span>
      <input type="text" data-rx-flags class="rx__flags-input"
             value="gi" maxlength="8" autocomplete="off" spellcheck="false"
             aria-label="Flags">
    </div>

    <div class="rx__status" role="status" aria-live="polite" data-rx-status>—</div>

    <details class="rx__presets" open>
      <summary>Quick presets</summary>
      <div class="rx__presets-grid">
        <?php
          $presets = [
            ['Email',           '\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b', 'g'],
            ['URL (http/https)','https?:\\/\\/[^\\s\\)\\]]+',                      'g'],
            ['IPv4',            '\\b(?:(?:25[0-5]|2[0-4]\\d|1?\\d?\\d)\\.){3}(?:25[0-5]|2[0-4]\\d|1?\\d?\\d)\\b', 'g'],
            ['NG phone',        '(?:\\+234|0)\\s?[789]\\d{2}\\s?\\d{3}\\s?\\d{4}',     'g'],
            ['UUID v4',         '\\b[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\\b', 'gi'],
            ['ISO date',        '\\b\\d{4}-\\d{2}-\\d{2}\\b',                          'g'],
            ['Hex colour',      '#(?:[0-9a-f]{3}){1,2}\\b',                            'gi'],
            ['Hashtag',         '#[A-Za-z0-9_]+',                                      'g'],
            ['Markdown link',   '\\[([^\\]]+)\\]\\(([^\\)]+)\\)',                      'g'],
            ['Slug',            '[a-z0-9]+(?:-[a-z0-9]+)*',                            'g'],
            ['Words 4–12',      '\\b[A-Za-z]{4,12}\\b',                                'g'],
            ['Numbers',         '-?\\d+(?:\\.\\d+)?',                                   'g'],
          ];
          foreach ($presets as $p):
        ?>
          <button type="button" class="rx__preset"
                  data-rx-preset="<?= e($p[1]) ?>"
                  data-rx-preset-flags="<?= e($p[2]) ?>"
                  title="<?= e($p[1]) ?>"><?= e($p[0]) ?></button>
        <?php endforeach; ?>
      </div>
    </details>

    <label class="field field--auth">
      <span class="field__label">Sample text</span>
      <textarea data-rx-sample rows="7"
                placeholder="Paste anything…">Visit https://afrostrength.com and academy.afrostrength.com, or email afrostrength@gmail.com.
You can also try http://example.org/path?x=1 and tools.afrostrength.com/qr.</textarea>
    </label>

    <div class="rx__panes">
      <article class="rx__pane">
        <header><span class="ff-mono">// HIGHLIGHTED MATCHES</span></header>
        <div class="rx__highlight" data-rx-highlight></div>
      </article>
      <article class="rx__pane">
        <header>
          <span class="ff-mono">// CAPTURES</span>
          <span class="rx__count" data-rx-count>0</span>
        </header>
        <ol class="rx__captures" data-rx-captures></ol>
      </article>
    </div>

    <details class="rx__flags-explain">
      <summary>What each flag does</summary>
      <dl>
        <dt><code>g</code></dt><dd>Global — keep matching past the first hit.</dd>
        <dt><code>i</code></dt><dd>Case-insensitive.</dd>
        <dt><code>m</code></dt><dd>Multiline — <code>^</code> / <code>$</code> match at every line break.</dd>
        <dt><code>s</code></dt><dd>Dotall — <code>.</code> matches newlines too.</dd>
        <dt><code>u</code></dt><dd>Unicode — treat the pattern as a UTF-16 code-point sequence.</dd>
        <dt><code>y</code></dt><dd>Sticky — anchor every match to <code>lastIndex</code>.</dd>
      </dl>
    </details>
  </div>
</section>

<script>
(function () {
  'use strict';
  const $ = (s) => document.querySelector(s);
  const els = {
    pattern: $('[data-rx-pattern]'),
    flags:   $('[data-rx-flags]'),
    sample:  $('[data-rx-sample]'),
    status:  $('[data-rx-status]'),
    hl:      $('[data-rx-highlight]'),
    cap:     $('[data-rx-captures]'),
    count:   $('[data-rx-count]'),
  };
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }
  function setStatus(text, state) {
    els.status.textContent = text;
    els.status.dataset.state = state || 'idle';
  }

  function compile() {
    const pat = els.pattern.value;
    let flags = els.flags.value.replace(/[^gimsuy]/g, '');
    // Always include 'g' for our highlighter — if the user omits it we
    // still scan globally, but we tell them.
    let injected = false;
    if (!flags.includes('g')) { flags += 'g'; injected = true; }
    try {
      return { re: new RegExp(pat, flags), injected, error: null };
    } catch (err) {
      return { re: null, injected: false, error: err.message };
    }
  }

  function render() {
    const { re, injected, error } = compile();
    if (error) {
      setStatus('Invalid: ' + error, 'bad');
      els.hl.innerHTML = '';
      els.cap.innerHTML = '';
      els.count.textContent = '0';
      return;
    }

    const text = els.sample.value;
    if (!text) {
      setStatus('Sample is empty.', 'idle');
      els.hl.innerHTML = '';
      els.cap.innerHTML = '';
      els.count.textContent = '0';
      return;
    }

    // Walk all matches. Guard against zero-width regexes that would loop.
    const matches = [];
    let m, last = -1;
    re.lastIndex = 0;
    let guard = 0;
    while ((m = re.exec(text)) !== null) {
      if (m.index === last && m[0].length === 0) {
        // zero-width match at same index — bump lastIndex to avoid infinite loop.
        re.lastIndex++;
      } else {
        matches.push(m);
        last = m.index;
      }
      if (++guard > 5000) break; // hard ceiling
    }

    // Build highlighted text: stitch around the match ranges so we don't
    // accidentally re-process already-emitted HTML.
    let out = '', cursor = 0;
    for (const mm of matches) {
      out += escapeHtml(text.slice(cursor, mm.index));
      out += '<mark>' + escapeHtml(mm[0]) + '</mark>';
      cursor = mm.index + mm[0].length;
    }
    out += escapeHtml(text.slice(cursor));
    els.hl.innerHTML = out;

    els.count.textContent = matches.length;
    setStatus(
      (matches.length ? matches.length + ' match' + (matches.length === 1 ? '' : 'es') : 'No matches')
      + (injected ? ' · g-flag auto-added' : ''),
      matches.length ? 'ok' : 'idle'
    );

    // Captures view
    els.cap.innerHTML = matches.slice(0, 50).map((mm, i) => {
      const groups = mm.length > 1 ? mm.slice(1) : [];
      const named = mm.groups ? Object.entries(mm.groups) : [];
      return (
        '<li class="rx__cap-row">' +
          '<div class="rx__cap-head"><span>#' + (i+1) + '</span>'
          + '<span class="rx__cap-pos">@ ' + mm.index + '</span></div>' +
          '<div class="rx__cap-full"><strong>full:</strong> ' + escapeHtml(mm[0]) + '</div>' +
          groups.map((g, gi) => (
            '<div class="rx__cap-group"><strong>$' + (gi+1) + ':</strong> ' + escapeHtml(String(g ?? '∅')) + '</div>'
          )).join('') +
          named.map(([k, v]) => (
            '<div class="rx__cap-group rx__cap-group--named"><strong>?&lt;' + escapeHtml(k) + '&gt;:</strong> ' + escapeHtml(String(v ?? '∅')) + '</div>'
          )).join('') +
        '</li>'
      );
    }).join('') || '<p class="caption">No captures.</p>';
  }

  ['input','keyup','change'].forEach(ev => {
    els.pattern.addEventListener(ev, render);
    els.flags.addEventListener(ev, render);
    els.sample.addEventListener(ev, render);
  });
  // Preset quick-loads: clicking a preset sets the pattern + flags and
  // re-renders. We never auto-apply on hover — clicks only.
  document.querySelectorAll('[data-rx-preset]').forEach(b => {
    b.addEventListener('click', () => {
      els.pattern.value = b.dataset.rxPreset;
      els.flags.value   = b.dataset.rxPresetFlags || 'g';
      render();
      els.pattern.focus();
      els.pattern.setSelectionRange(els.pattern.value.length, els.pattern.value.length);
    });
  });

  render();
})();
</script>
