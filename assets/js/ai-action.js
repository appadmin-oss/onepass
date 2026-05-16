/* ai-action.js — single controller for every [data-ai-block] on the site.
 *
 * Owns the 12 UX patterns codified in the plan. Every AI affordance
 * (status summary, services brief-match, course "is this for me",
 * tracker weekly review, contact polish, etc.) renders through the
 * ai-block.php partial and is animated by this file.
 *
 * No build step. No framework. Auto-runs on DOMContentLoaded; idempotent
 * — re-runs are safe (we set data-ai-init=1 on attach).
 *
 * Public surface:
 *   - data-ai-block  : container; everything else hangs off this
 *   - data-ai-action : the button (clicking it triggers the call)
 *   - data-ai-body   : aria-live region for the output
 *   - data-ai-attr   : footer line for attribution
 *   - data-ai-target : optional form field name to rewrite
 *   - data-ai-context: pre-baked JSON context string
 *   - data-ai-auto   : "1" to run on load without a button click
 *
 * The body of the block can contain any HTML; we never blow it away on
 * idle. We swap aria attributes + a state class to communicate.
 */
(function () {
  'use strict';

  // Network budgets per the UX pattern doc.
  const SOFT_TIMEOUT_MS = 8000;
  const HARD_TIMEOUT_MS = 20000;

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',
    }[c]));
  }

  function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function getContext(block) {
    // Priority: explicit data-ai-context (pre-baked) → form-field by name
    // → the closest [data-ai-source] element's value. We never read PII
    // from arbitrary inputs without an explicit binding.
    const baked = block.dataset.aiContext;
    if (baked && baked.length) return baked;

    const target = block.dataset.aiTarget;
    if (target) {
      // Search the nearest form first, then the page.
      const form = block.closest('form');
      const el = (form && form.elements.namedItem(target))
        || document.querySelector(`[name="${target}"]`);
      if (el && typeof el.value === 'string') return el.value;
    }

    const src = block.querySelector('[data-ai-source]');
    if (src) return src.value ?? src.textContent ?? '';

    return '';
  }

  function setState(block, state) {
    block.dataset.aiState = state;
    const btn = block.querySelector('[data-ai-action]');
    if (!btn) return;
    btn.disabled = (state === 'busy');
    if (state === 'busy') {
      if (!btn.dataset.label) btn.dataset.label = btn.textContent.trim();
      btn.textContent = 'Thinking…';
      btn.setAttribute('aria-busy', 'true');
    } else {
      btn.removeAttribute('aria-busy');
      if (btn.dataset.label) btn.textContent = btn.dataset.label;
    }
  }

  function attributeRow(block, { provider, ms, cached, error }) {
    const attr = block.querySelector('[data-ai-attr]');
    if (!attr) return;
    if (cached) {
      attr.innerHTML = '<span class="ai-block__chip">cached</span>';
    } else if (error) {
      attr.innerHTML =
        '<span class="ai-block__chip ai-block__chip--err">' + escapeHtml(error) + '</span>';
    } else {
      attr.innerHTML = 'Drafted by AI · '
        + '<span data-ai-provider>' + escapeHtml(provider || '—') + '</span> · '
        + '<span data-ai-ms>' + (ms || 0) + '</span>ms';
    }
    attr.hidden = false;
  }

  function renderFallback(block, fallback) {
    const body = block.querySelector('[data-ai-body]');
    if (!body) return;
    body.innerHTML = '<p class="ai-block__fallback">'
      + escapeHtml(fallback || 'AI mentor is unavailable. Carry on without it.')
      + '</p>';
  }

  function renderText(block, text) {
    const body = block.querySelector('[data-ai-body]');
    if (!body) return;
    // The block is text-led by default. Pages that want structured
    // rendering (JSON, bullets, etc) listen for the custom event below
    // and replace innerHTML themselves.
    body.innerHTML = '<p>' + escapeHtml(text).replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
  }

  async function run(block, opts) {
    const intent   = block.dataset.aiIntent;
    const endpoint = block.dataset.aiEndpoint || '/api/ai/suggest';
    const tone     = block.dataset.aiTone || 'auto';
    if (!intent) return;

    const target = block.dataset.aiTarget;
    const targetEl = target ? (function () {
      const form = block.closest('form');
      return (form && form.elements.namedItem(target))
        || document.querySelector(`[name="${target}"]`);
    })() : null;

    const context = (opts?.contextOverride ?? getContext(block)).trim();
    if (!context) {
      setState(block, 'idle');
      renderFallback(block, 'Type something first.');
      return;
    }

    setState(block, 'busy');

    // Soft + hard timeouts so we tell the user what's happening rather
    // than spinning forever. The soft warning is in-band (no abort).
    const controller = new AbortController();
    const softTimer = setTimeout(() => {
      const body = block.querySelector('[data-ai-body]');
      if (body && body.textContent.trim() === '') {
        body.innerHTML = '<p class="ai-block__hint">Still thinking — usually this means the network is slow.</p>';
      }
    }, SOFT_TIMEOUT_MS);
    const hardTimer = setTimeout(() => controller.abort(), HARD_TIMEOUT_MS);

    const fd = new FormData();
    fd.append('intent', intent);
    fd.append('context', context);
    fd.append('tone', tone);
    fd.append('_csrf', csrf());

    const t0 = performance.now();
    let data = null, status = 0;
    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd,
        signal: controller.signal,
      });
      status = res.status;
      data = await res.json().catch(() => ({}));
    } catch (e) {
      data = { ok: false, error: e?.name === 'AbortError' ? 'timeout' : 'network' };
    } finally {
      clearTimeout(softTimer);
      clearTimeout(hardTimer);
    }
    const ms = Math.round(performance.now() - t0);

    if (!data || !data.ok || !data.text) {
      const reason = data?.error || ('http_' + status);
      attributeRow(block, { error: reason });
      // Always show the canned fallback when we have one; otherwise a
      // generic offline line.
      renderFallback(block, data?.fallback || block.dataset.aiFallback);
      setState(block, 'error');
      return;
    }

    // If the affordance was a "rewrite this field" (e.g. polish a brief),
    // commit the new text + remember the previous so we can undo.
    if (targetEl) {
      block._previous = targetEl.value;
      targetEl.value = data.text;
      targetEl.dispatchEvent(new Event('input', { bubbles: true }));
      // Convert the button into Undo for this round.
      const btn = block.querySelector('[data-ai-action]');
      if (btn) {
        btn.dataset.label = btn.dataset.label || 'AI assist';
        btn.textContent = 'Undo';
        btn.dataset.aiMode = 'undo';
      }
    } else {
      renderText(block, data.text);
    }
    attributeRow(block, {
      provider: data.provider, ms, cached: !!data.cached,
    });
    setState(block, 'ready');

    // Custom event for pages that need to parse JSON (career-path,
    // search_guess, faq_search, etc).
    block.dispatchEvent(new CustomEvent('ai:result', {
      detail: { intent, text: data.text, provider: data.provider, cached: !!data.cached, ms },
      bubbles: true,
    }));
  }

  function undo(block) {
    const target = block.dataset.aiTarget;
    if (!target) return;
    const form = block.closest('form');
    const el = (form && form.elements.namedItem(target))
      || document.querySelector(`[name="${target}"]`);
    if (!el) return;
    if (block._previous != null) {
      el.value = block._previous;
      el.dispatchEvent(new Event('input', { bubbles: true }));
      delete block._previous;
    }
    const btn = block.querySelector('[data-ai-action]');
    if (btn) {
      btn.textContent = btn.dataset.label || 'AI assist';
      btn.dataset.aiMode = '';
    }
    const attr = block.querySelector('[data-ai-attr]');
    if (attr) attr.hidden = true;
    setState(block, 'idle');
  }

  function attach(block) {
    if (block.dataset.aiInit) return;
    block.dataset.aiInit = '1';
    setState(block, block.dataset.aiState || 'idle');

    const btn = block.querySelector('[data-ai-action]');
    if (btn) {
      btn.addEventListener('click', () => {
        if (btn.dataset.aiMode === 'undo') return undo(block);
        run(block);
      });
    }
    // Auto-run on mount if requested (e.g. status_impact_plain).
    if (block.dataset.aiAuto === '1') {
      // Defer slightly so DOMContentLoaded handlers settle.
      setTimeout(() => run(block), 100);
    }
  }

  function boot() {
    document.querySelectorAll('[data-ai-block]').forEach(attach);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  // Expose for AJAX-injected forms (modal apply, admin editors).
  window.AfsAi = { run, undo, attach, boot };
})();
