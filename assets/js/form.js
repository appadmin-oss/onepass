/* Afrostrength — form behaviour.
   Floating-label state, inline validation, multi-step navigation, conditional fields,
   AJAX submit with spinner → check stroke morph. */
(function () {
  'use strict';

  // ---------- Validators ----------
  const Rules = {
    required: v => v != null && v.trim() !== '',
    email:    v => /^\S+@\S+\.\S+$/.test(v.trim()),
    phone:    v => /^[\d\s\+\-\(\)]{6,20}$/.test(v.trim()),
    min: (v, n) => v.trim().length >= parseInt(n, 10),
  };

  function fieldOf(input) { return input.closest('.field'); }

  function validate(input) {
    const rules = (input.dataset.rules || '').split('|').filter(Boolean);
    if (!rules.length) return true;
    const value = input.value || '';
    for (const r of rules) {
      const [name, arg] = r.split(':');
      const fn = Rules[name];
      if (fn && !fn(value, arg)) return r;
    }
    return true;
  }

  function setFieldState(input, ok, message) {
    const f = fieldOf(input);
    if (!f) return;
    f.classList.toggle('is-valid', ok === true);
    f.classList.toggle('is-error', ok === false);
    let helper = f.querySelector('.helper');
    if (ok === false) {
      if (!helper) {
        helper = document.createElement('div');
        helper.className = 'helper';
        f.appendChild(helper);
      }
      helper.textContent = '// ' + (message || 'Check this field.');
    } else if (helper && helper.dataset.persistent !== '1') {
      helper.remove();
    }
  }

  function bindLiveValidation(input) {
    if (input.dataset.bound === '1') return;
    input.dataset.bound = '1';
    const onCheck = () => {
      const result = validate(input);
      if (result === true) {
        setFieldState(input, input.value.trim() !== '');
      } else {
        const msg = ({
          required: 'Required.',
          email:    "Doesn't look like a valid email.",
          phone:    'Use digits, spaces, or +.',
          min:      'A bit longer, please.'
        })[result.split(':')[0]];
        setFieldState(input, false, msg);
      }
    };
    input.addEventListener('blur', onCheck);
    input.addEventListener('input', () => {
      if (fieldOf(input).classList.contains('is-error')) onCheck();
    });
  }

  // ---------- Multi-step ----------
  function initMultiStep(form) {
    const steps = form.querySelectorAll('[data-step]');
    if (steps.length < 2) return;
    const segs = form.querySelectorAll('.step-bar .seg');
    let current = 0;
    function show(index) {
      steps.forEach((s, i) => { s.style.display = i === index ? '' : 'none'; });
      segs.forEach((s, i) => {
        s.classList.toggle('done',   i < index);
        s.classList.toggle('active', i === index);
      });
      const labels = form.querySelectorAll('[data-step-label]');
      if (labels.length) {
        labels.forEach(l => l.textContent = `0${index + 1} of ${steps.length}`);
      }
      current = index;
    }
    show(0);

    form.querySelectorAll('[data-step-next]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const stepEl = steps[current];
        const inputs = stepEl.querySelectorAll('input[data-rules], textarea[data-rules], select[data-rules]');
        let ok = true;
        inputs.forEach(i => { if (validate(i) !== true) { ok = false; setFieldState(i, false, 'Required.'); fieldOf(i).classList.add('shake'); setTimeout(() => fieldOf(i).classList.remove('shake'), 360); } });
        if (!ok) return;
        if (current < steps.length - 1) show(current + 1);
      });
    });
    form.querySelectorAll('[data-step-prev]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (current > 0) show(current - 1);
      });
    });
  }

  // ---------- Conditional fields ----------
  function initConditionals(form) {
    function evaluate() {
      form.querySelectorAll('[data-cond]').forEach(el => {
        const cond = el.dataset.cond; // e.g. "service:brand-development,creative-design"
        const [name, raw] = cond.split(':');
        const want = (raw || '').split(',');
        const inputs = form.querySelectorAll(`[name="${name}"]`);
        let value = '';
        inputs.forEach(i => {
          if (i.type === 'radio' && i.checked) value = i.value;
          else if (i.type === 'checkbox' && i.checked) value = i.value;
          else if (i.type === 'hidden' || i.tagName === 'SELECT' || i.tagName === 'INPUT') value = i.value;
        });
        const visible = want.includes(value);
        el.classList.toggle('is-visible', visible);
      });
    }
    form.addEventListener('change', evaluate);
    form.addEventListener('input',  evaluate);
    evaluate();
  }

  // ---------- Choice cards (mimics radio) ----------
  function initChoiceGroups(form) {
    form.querySelectorAll('[data-choice-group]').forEach(group => {
      const name  = group.dataset.choiceGroup;
      let hidden  = form.querySelector(`input[type="hidden"][name="${name}"]`);
      if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = name;
        form.appendChild(hidden);
      }
      group.querySelectorAll('.choice-card').forEach(card => {
        card.addEventListener('click', () => {
          group.querySelectorAll('.choice-card').forEach(c => c.classList.remove('is-selected'));
          card.classList.add('is-selected');
          hidden.value = card.dataset.value;
          form.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });
    });
  }

  // ---------- Submit (AJAX morphing button) ----------
  function initSubmit(form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const inputs = form.querySelectorAll('input[data-rules], textarea[data-rules], select[data-rules]');
      let ok = true;
      inputs.forEach(i => { if (validate(i) !== true) { ok = false; setFieldState(i, false, 'Check this field.'); } });
      if (!ok) return;

      const submitBtn = form.querySelector('[type="submit"]');
      if (!submitBtn) return;
      const originalLabel = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span>';

      try {
        const fd = new FormData(form);
        // Read meta CSRF
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf) fd.append('_csrf', csrf.content);
        const resp = await fetch(form.action || '/api/inquiries', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: fd
        });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok || data.error) throw new Error(data.message || 'Server error.');

        submitBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path class="check-draw" d="M3 9.5L7 13.5 15 5"/></svg>';
        submitBtn.style.background = '#1F8A5B';
        submitBtn.style.color = '#fff';

        // Show success panel if present
        const success = form.querySelector('[data-success]');
        const failure = form.querySelector('[data-failure]');
        if (failure) failure.style.display = 'none';
        if (success) {
          success.style.display = 'block';
          form.querySelectorAll('[data-step]').forEach(s => s.style.display = 'none');
        }
      } catch (err) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalLabel;
        const failure = form.querySelector('[data-failure]');
        if (failure) {
          failure.style.display = 'block';
          failure.textContent = '// ' + (err.message || 'Could not submit. Try again.');
        }
      }
    });
  }

  // ---------- Init ----------
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-rules], textarea[data-rules]').forEach(bindLiveValidation);
    document.querySelectorAll('form[data-form]').forEach(form => {
      initChoiceGroups(form);
      initConditionals(form);
      initMultiStep(form);
      initSubmit(form);
    });
  });
})();
