/* smart-form.js — progressive intelligence layer for our public forms.
 *
 * A form opts in by adding `data-smart` to the <form> element. Then any
 * field can declare any of these behaviours via data-* attributes; the
 * engine wires them on DOM-ready:
 *
 *   data-smart-email         → email-typo suggester ("did you mean gmail.com?")
 *   data-smart-counter       → live character counter with quality hint
 *                              optional data-smart-min / data-smart-max
 *   data-smart-password      → password strength meter (length + classes + HIBP hint)
 *   data-show-when           → "field:value[,value2]" — show only when a
 *                              sibling/ancestor input matches one of those values
 *   data-smart-suggest       → AI polish via POST /api/ai/suggest?intent=…
 *                              optional data-smart-intent (default "rewrite")
 *
 * The form gets autosave/restore via localStorage keyed by the form's
 * id (or `data-smart-key`). Submission clears the saved snapshot.
 *
 * Everything degrades cleanly: forms without `data-smart` are untouched;
 * fields without smart attributes behave normally.
 */
(function () {
  'use strict';

  // ---------- helpers -----------------------------------------------

  const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));
  const debounce = (fn, ms = 200) => {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(null, args), ms); };
  };
  const isFocusable = el => el && !el.disabled && el.offsetParent !== null;
  const csrfFrom = form =>
    form.querySelector('input[name="_csrf"]')?.value
    || document.querySelector('meta[name="csrf-token"]')?.content
    || '';

  // Tiny helper to attach a hint element under a field. Returns the node.
  function ensureHint(field, cls) {
    let hint = field.parentElement.querySelector('.' + cls);
    if (!hint) {
      hint = document.createElement('div');
      hint.className = cls;
      hint.setAttribute('aria-live', 'polite');
      field.parentElement.appendChild(hint);
    }
    return hint;
  }

  // ---------- 1. Email-typo suggester -------------------------------
  //
  // Common typos for the big providers. If the user types "gmaol.com",
  // we suggest "gmail.com" — they can accept with a tap or keep typing.
  const COMMON_DOMAINS = [
    'gmail.com','googlemail.com','yahoo.com','yahoo.co.uk','outlook.com',
    'hotmail.com','live.com','icloud.com','me.com','protonmail.com',
    'aol.com','msn.com','zoho.com',
    'afrostrength.com',
  ];
  function levenshtein(a, b) {
    if (a === b) return 0;
    if (!a.length) return b.length;
    if (!b.length) return a.length;
    const v0 = new Array(b.length + 1).fill(0).map((_, i) => i);
    const v1 = new Array(b.length + 1).fill(0);
    for (let i = 0; i < a.length; i++) {
      v1[0] = i + 1;
      for (let j = 0; j < b.length; j++) {
        const cost = a[i] === b[j] ? 0 : 1;
        v1[j + 1] = Math.min(v1[j] + 1, v0[j + 1] + 1, v0[j] + cost);
      }
      for (let j = 0; j <= b.length; j++) v0[j] = v1[j];
    }
    return v1[b.length];
  }
  function suggestEmail(value) {
    const at = value.lastIndexOf('@');
    if (at < 1 || at === value.length - 1) return null;
    const local  = value.slice(0, at);
    const domain = value.slice(at + 1).toLowerCase().trim();
    if (COMMON_DOMAINS.includes(domain)) return null;
    let best = null, bestDist = Infinity;
    for (const d of COMMON_DOMAINS) {
      // Skip if a totally different TLD.
      if (Math.abs(d.length - domain.length) > 3) continue;
      const dist = levenshtein(domain, d);
      if (dist > 0 && dist < bestDist) { bestDist = dist; best = d; }
    }
    // Tighter threshold for short domains.
    const maxOk = domain.length <= 6 ? 1 : 2;
    if (best && bestDist <= maxOk) return local + '@' + best;
    return null;
  }
  function initEmailSuggest(input) {
    const hint = ensureHint(input, 'sf-hint sf-hint--email');
    const update = debounce(() => {
      const v = input.value.trim();
      hint.innerHTML = '';
      hint.style.display = 'none';
      if (!v || !/^\S+@\S+\.\S+$/.test(v)) return;
      const suggestion = suggestEmail(v);
      if (!suggestion) return;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'sf-suggest';
      btn.innerHTML = 'Did you mean <strong>' + suggestion + '</strong>? <span aria-hidden="true">↩</span>';
      btn.addEventListener('click', () => {
        input.value = suggestion;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        hint.style.display = 'none';
        input.focus();
      });
      hint.appendChild(btn);
      hint.style.display = 'block';
    }, 200);
    input.addEventListener('input', update);
    input.addEventListener('blur', update);
  }

  // ---------- 2. Character counter with quality hint -----------------
  //
  // For long-form text fields: shows char count, an aim band, and a
  // qualitative read ("Reads as concise / clear / detailed").
  function initCounter(field) {
    const min = parseInt(field.dataset.smartMin || '0', 10);
    const max = parseInt(field.dataset.smartMax || '1200', 10);
    const hint = ensureHint(field, 'sf-counter');
    function render() {
      const len = field.value.length;
      let band, state;
      if (len === 0)            { band = 'Empty';     state = 'empty'; }
      else if (len < min)       { band = 'Too short'; state = 'low';   }
      else if (len < max * 0.5) { band = 'Concise';   state = 'ok';    }
      else if (len < max * 0.9) { band = 'Clear';     state = 'ok';    }
      else if (len <= max)      { band = 'Detailed';  state = 'warn';  }
      else                      { band = 'Too long';  state = 'bad';   }
      hint.dataset.state = state;
      hint.innerHTML =
        '<span class="sf-counter__band">' + band + '</span>' +
        '<span class="sf-counter__num">' + len + (max ? ' / ' + max : '') + '</span>';
    }
    field.addEventListener('input', render);
    render();
  }

  // ---------- 3. Password strength meter -----------------------------
  //
  // We don't fetch HIBP from JS (rate limits + cors); we score locally
  // and tell the user we'll deep-check the password on submit.
  function initPasswordMeter(field) {
    const hint = ensureHint(field, 'sf-strength');
    function score(pw) {
      if (!pw) return { tier: 0, label: 'Enter a password' };
      let bits = 0;
      const len = pw.length;
      const classes = [
        /[a-z]/.test(pw),
        /[A-Z]/.test(pw),
        /\d/.test(pw),
        /[^A-Za-z0-9]/.test(pw),
      ].filter(Boolean).length;
      bits += Math.min(len, 32) * 2;
      bits += classes * 6;
      if (/(.)\1{2,}/.test(pw)) bits -= 8;          // repeats
      if (/^(?:[a-z]+|\d+)$/i.test(pw)) bits -= 8;  // single-class
      const tier =
        len < 8 ? 1 :
        bits < 24 ? 1 :
        bits < 40 ? 2 :
        bits < 56 ? 3 : 4;
      const labels = ['Enter a password', 'Weak', 'Fair', 'Strong', 'Very strong'];
      return { tier, label: labels[tier] };
    }
    function render() {
      const { tier, label } = score(field.value);
      hint.dataset.tier = String(tier);
      hint.innerHTML =
        '<span class="sf-strength__bars" aria-hidden="true">' +
          [1,2,3,4].map(n => '<i' + (n <= tier ? ' class="is-on"' : '') + '></i>').join('') +
        '</span>' +
        '<span class="sf-strength__label">' + label + '</span>';
    }
    field.addEventListener('input', render);
    render();
  }

  // ---------- 4. Conditional fields (data-show-when) -----------------
  //
  // data-show-when="service:brand-development,creative-design"
  // → show the wrapper when [name=service] equals one of those values.
  function initConditional(form) {
    const wrappers = $$('[data-show-when]', form);
    if (!wrappers.length) return;
    function evaluate() {
      wrappers.forEach(w => {
        const expr = w.dataset.showWhen;
        const [name, valuesCsv] = expr.split(':');
        const values = (valuesCsv || '').split(',').map(s => s.trim()).filter(Boolean);
        const src = form.querySelector('[name="' + name + '"]');
        const v = src ? (src.type === 'checkbox' ? (src.checked ? '1' : '') : src.value) : '';
        const match = values.length === 0 ? !!v : values.includes(v);
        w.hidden = !match;
        // Disable inputs inside hidden wrappers so they don't fail validation.
        $$('input, select, textarea', w).forEach(inp => {
          inp.disabled = !match;
        });
      });
    }
    form.addEventListener('input', evaluate);
    form.addEventListener('change', evaluate);
    evaluate();
  }

  // ---------- 5. Autosave / restore ----------------------------------
  function autosaveKey(form) {
    return 'afs_sf_' + (form.dataset.smartKey || form.id || form.action || 'form');
  }
  function initAutosave(form) {
    const key = autosaveKey(form);
    // Restore on load (skip if a sensitive field would be repopulated).
    try {
      const raw = localStorage.getItem(key);
      if (raw) {
        const data = JSON.parse(raw);
        Object.entries(data).forEach(([name, value]) => {
          const el = form.elements.namedItem(name);
          if (!el || el.type === 'password' || el.name === '_csrf') return;
          if (el.type === 'checkbox' || el.type === 'radio') { el.checked = !!value; }
          else if (typeof value === 'string') { el.value = value; }
        });
        form.dispatchEvent(new Event('change'));
      }
    } catch (_) { /* ignore */ }

    const save = debounce(() => {
      const data = {};
      $$('input, select, textarea', form).forEach(el => {
        if (!el.name || el.type === 'password' || el.name === '_csrf' || el.type === 'file') return;
        if (el.type === 'checkbox' || el.type === 'radio') data[el.name] = el.checked;
        else data[el.name] = el.value;
      });
      try { localStorage.setItem(key, JSON.stringify(data)); } catch (_) {}
    }, 400);
    form.addEventListener('input', save);
    form.addEventListener('change', save);
    form.addEventListener('submit', () => { try { localStorage.removeItem(key); } catch (_) {} });
  }

  // ---------- 6. AI suggest (Polish) ---------------------------------
  //
  // <button data-smart-suggest="message" data-smart-intent="brief_polish">…
  // posts {context: <target field value>, intent} to /api/ai/suggest
  // and writes the returned text back into the target. Honest UX: tells
  // the user we sent only the prompt text.
  function initAiSuggest(form) {
    const buttons = $$('[data-smart-suggest]', form);
    if (!buttons.length) return;
    buttons.forEach(btn => {
      const targetName = btn.dataset.smartSuggest;
      const intent     = btn.dataset.smartIntent || 'rewrite';
      const target     = form.elements.namedItem(targetName);
      if (!target) return;
      btn.addEventListener('click', async () => {
        const context = (target.value || '').trim();
        if (context.length < 12) {
          btn.dataset.state = 'short';
          btn.textContent = 'Write a bit more first';
          setTimeout(() => { btn.textContent = btn.dataset.label || 'AI polish'; delete btn.dataset.state; }, 1800);
          return;
        }
        if (!btn.dataset.label) btn.dataset.label = btn.textContent.trim();
        btn.disabled = true;
        btn.dataset.state = 'busy';
        btn.textContent = 'Polishing…';
        try {
          const fd = new FormData();
          fd.append('intent', intent);
          fd.append('context', context);
          fd.append('_csrf', csrfFrom(form));
          const r = await fetch('/api/ai/suggest', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
          });
          const data = await r.json().catch(() => ({}));
          if (r.ok && data.text) {
            // Remember the original so the user can undo.
            btn.dataset.previous = context;
            target.value = data.text;
            target.dispatchEvent(new Event('input', { bubbles: true }));
            btn.textContent = 'Undo';
            btn.dataset.state = 'done';
            btn.disabled = false;
            const undo = () => {
              if (btn.dataset.previous !== undefined) {
                target.value = btn.dataset.previous;
                target.dispatchEvent(new Event('input', { bubbles: true }));
                delete btn.dataset.previous;
              }
              btn.textContent = btn.dataset.label;
              delete btn.dataset.state;
              btn.removeEventListener('click', undo);
              setTimeout(() => initAiSuggest(form), 0); // re-attach the polish handler
            };
            btn.addEventListener('click', undo, { once: true });
          } else {
            btn.textContent = data.message || 'AI unavailable';
            btn.dataset.state = 'error';
            btn.disabled = false;
            setTimeout(() => { btn.textContent = btn.dataset.label; delete btn.dataset.state; }, 2200);
          }
        } catch (_) {
          btn.textContent = 'AI unavailable';
          btn.dataset.state = 'error';
          btn.disabled = false;
          setTimeout(() => { btn.textContent = btn.dataset.label; delete btn.dataset.state; }, 2200);
        }
      });
    });
  }

  // ---------- 7. Logic-based prefills + derivations -----------------
  //
  // Three behaviours, each opt-in per field, all idempotent:
  //
  //   data-derive-from="email-local"
  //     → If THIS field is blank when the named source field is left,
  //       fill it with a humanised value derived from the source.
  //       Currently supports "email-local" only (john.doe@x.com → John Doe).
  //
  //   data-prefix-when="country:Nigeria=>+234 "
  //     → On blur, if THIS field is non-empty and doesn't already begin
  //       with a plus or the prefix, and the source field equals the
  //       given value, prepend the prefix. Useful for phone numbers.
  //
  //   data-auto-https
  //     → On blur, if THIS field looks like a URL (has a dot) and
  //       doesn't begin with http(s)://, prepend https://.
  //
  // All three never overwrite what a user typed and never trigger more
  // than once per blur. The autosave snapshot picks up the result so a
  // restored form keeps the prefilled value.
  function initPrefill(form) {
    const fields = $$('[data-derive-from], [data-prefix-when], [data-auto-https]', form);
    if (!fields.length) return;

    function humaniseLocal(email) {
      const at = email.indexOf('@');
      if (at < 1) return '';
      const local = email.slice(0, at);
      return local
        .split(/[._\-+]+/)
        .filter(Boolean)
        .map(w => w[0].toUpperCase() + w.slice(1).toLowerCase())
        .join(' ');
    }

    // Derive (e.g. name from email-local)
    $$('[data-derive-from]', form).forEach(target => {
      const kind = target.dataset.deriveFrom;
      if (kind !== 'email-local') return;
      const email = form.querySelector('input[type="email"]') || form.elements.namedItem('email');
      if (!email) return;
      const tryFill = () => {
        if (target.value.trim()) return; // never overwrite
        const v = email.value.trim();
        if (!/^\S+@\S+\.\S+$/.test(v)) return;
        const derived = humaniseLocal(v);
        if (derived) {
          target.value = derived;
          target.dispatchEvent(new Event('input', { bubbles: true }));
        }
      };
      email.addEventListener('blur', tryFill);
    });

    // Prefix when (e.g. +234 for Nigerian numbers)
    $$('[data-prefix-when]', form).forEach(field => {
      const expr = field.dataset.prefixWhen;
      const arrow = expr.indexOf('=>');
      if (arrow < 0) return;
      const pair    = expr.slice(0, arrow);
      const prefix  = expr.slice(arrow + 2);
      const [srcName, srcVal] = pair.split(':');
      const src = form.elements.namedItem(srcName);
      if (!src) return;
      field.addEventListener('blur', () => {
        const v = field.value.trim();
        if (!v) return;
        // Only act when source matches and the field doesn't already have
        // an international prefix or the requested prefix.
        const srcCurrent = (src.type === 'checkbox' || src.type === 'radio')
          ? (src.checked ? src.value : '')
          : src.value;
        if (srcCurrent.trim().toLowerCase() !== srcVal.toLowerCase()) return;
        if (v.startsWith('+') || v.startsWith(prefix.trim())) return;
        // Strip a leading 0 if present (typical local format).
        const cleaned = v.replace(/^0+/, '');
        field.value = prefix + cleaned;
        field.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });

    // Auto-https
    $$('[data-auto-https]', form).forEach(field => {
      field.addEventListener('blur', () => {
        let v = field.value.trim();
        if (!v) return;
        if (/^https?:\/\//i.test(v)) return;
        if (!/\./.test(v)) return; // doesn't look like a domain yet
        // Strip any accidental leading slashes / "www " typos.
        v = v.replace(/^\/+/, '').replace(/^www\s+/i, 'www.');
        field.value = 'https://' + v;
        field.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });
  }

  // ---------- 8. Inline live validation -----------------------------
  //
  // We honour data-rules="required|email|min:N|max:N|phone|url|match:field"
  // and render a helpful, plain-language hint under the field.
  const FRIENDLY = {
    required: () => 'This field is required.',
    email: () => 'Use a valid email address.',
    phone: () => 'Use digits, spaces and an optional +. E.g. +234 810 019 1456.',
    url: () => 'Use a full URL, starting with http or https.',
    min: n => 'At least ' + n + ' characters.',
    max: n => 'At most ' + n + ' characters.',
    match: () => 'Doesn\'t match the other field.',
  };
  const RULES = {
    required: v => v.trim().length > 0,
    email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()),
    phone: v => /^\+?[\d\s().-]{7,}$/.test(v.trim()),
    url:   v => /^https?:\/\/\S+$/i.test(v.trim()),
    min:   (v, n) => v.length >= parseInt(n, 10),
    max:   (v, n) => v.length <= parseInt(n, 10),
  };
  function validateField(field, form) {
    if (!field.dataset.rules) return true;
    const rules = field.dataset.rules.split('|');
    const value = field.value || '';
    for (const r of rules) {
      const [name, arg] = r.split(':');
      if (name === 'match') {
        const other = form.elements.namedItem(arg);
        if (other && other.value !== value) return FRIENDLY.match();
        continue;
      }
      const fn = RULES[name];
      if (!fn) continue;
      if (!fn(value, arg)) return FRIENDLY[name] ? FRIENDLY[name](arg) : 'Check this field.';
    }
    return true;
  }
  function initLiveValidate(form) {
    $$('[data-rules]', form).forEach(field => {
      const hint = ensureHint(field, 'sf-error');
      let touched = false;
      const run = () => {
        const result = validateField(field, form);
        if (result === true) {
          field.removeAttribute('aria-invalid');
          hint.textContent = '';
          hint.style.display = 'none';
        } else if (touched) {
          field.setAttribute('aria-invalid', 'true');
          hint.textContent = result;
          hint.style.display = 'block';
        }
      };
      field.addEventListener('blur', () => { touched = true; run(); });
      field.addEventListener('input', () => { if (touched) run(); });
    });
  }

  // ---------- bootstrap ---------------------------------------------

  function init(form) {
    if (form.dataset.smartInit) return;
    form.dataset.smartInit = '1';
    initConditional(form);
    initPrefill(form);
    initLiveValidate(form);
    initAutosave(form);
    initAiSuggest(form);
    $$('input[type="email"][data-smart-email]', form).forEach(initEmailSuggest);
    $$('textarea[data-smart-counter], input[data-smart-counter]', form).forEach(initCounter);
    $$('input[type="password"][data-smart-password]', form).forEach(initPasswordMeter);
  }

  function boot() {
    $$('form[data-smart]').forEach(init);
    // Email-typo suggester is small and self-contained — let it run on any
    // input that opts in, regardless of whether the parent form uses the
    // full smart-form engine.
    $$('input[type="email"][data-smart-email]').forEach(inp => {
      if (inp.dataset.smartEmailInit) return;
      inp.dataset.smartEmailInit = '1';
      initEmailSuggest(inp);
    });
    // Same for the character counter — it's a per-field affordance with
    // no cross-field dependencies, so let it attach standalone too.
    $$('[data-smart-counter]').forEach(el => {
      if (el.dataset.smartCounterInit) return;
      el.dataset.smartCounterInit = '1';
      initCounter(el);
    });
    // Prefills attach per-form (they look up sibling fields), so we walk
    // every form that contains a prefill-marked field, even if the form
    // itself doesn't opt into data-smart.
    const prefillForms = new Set();
    $$('[data-derive-from], [data-prefix-when], [data-auto-https]').forEach(el => {
      const f = el.closest('form');
      if (f && !f.dataset.smartPrefillInit) prefillForms.add(f);
    });
    prefillForms.forEach(f => { f.dataset.smartPrefillInit = '1'; initPrefill(f); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
  // Expose for forms injected by AJAX (e.g. the apply modal).
  window.AfsSmartForm = { init, boot };
})();
