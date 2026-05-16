/* AFROSTRENGTH — chrome behaviours (nav scroll, mobile drawer,
 * Ask-Afro-AI command palette with streaming, reveal-on-scroll).
 *
 * Self-contained module. Reads DOM hooks the partials emit:
 *   .afro-nav                 — fixed pill nav
 *   .afro-drawer              — mobile drawer
 *   [data-afro-burger]        — hamburger button
 *   [data-afro-cmdk-open]     — any element that opens the palette
 *   .afro-cmdk                — palette root
 *   .afro-reveal              — reveal-on-scroll target
 *
 * The streaming answer uses /api/ai/chat (the existing AI endpoint).
 * If that endpoint is unavailable the palette gracefully degrades
 * to a local canned reply, so the chrome works even without a key.
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function () {
    initNavScroll();
    initDrawer();
    initActiveLink();
    initCmdk();
    initReveal();
  });

  // -------------- NAV: shrink on scroll ----------------------------
  function initNavScroll() {
    var nav = document.querySelector('.afro-nav');
    if (!nav) return;
    var onScroll = function () {
      nav.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // -------------- MOBILE DRAWER ------------------------------------
  function initDrawer() {
    var drawer = document.querySelector('.afro-drawer');
    var burger = document.querySelector('[data-afro-burger]');
    if (!drawer || !burger) return;
    var open = function () {
      drawer.classList.add('is-open');
      drawer.setAttribute('aria-hidden', 'false');
      burger.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    };
    var close = function () {
      drawer.classList.remove('is-open');
      drawer.setAttribute('aria-hidden', 'true');
      burger.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    };
    var toggle = function () {
      drawer.classList.contains('is-open') ? close() : open();
    };
    burger.addEventListener('click', toggle);
    drawer.addEventListener('click', function (e) {
      // Close on link tap, or on a designated close button
      if (e.target.closest('a, [data-afro-drawer-close]')) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });
    // If we cross above the mobile breakpoint while open, close cleanly
    var mq = window.matchMedia('(min-width: 981px)');
    var onMq = function () { if (mq.matches) close(); };
    if (mq.addEventListener) mq.addEventListener('change', onMq);
    else if (mq.addListener) mq.addListener(onMq);
  }

  // -------------- ACTIVE LINK HIGHLIGHTING -------------------------
  function initActiveLink() {
    var path = (location.pathname || '/').toLowerCase().replace(/\/+$/, '') || '/';
    document.querySelectorAll('.afro-nav__links a, .afro-drawer__links a').forEach(function (a) {
      var href = (a.getAttribute('href') || '').toLowerCase();
      // Strip absolute origin if present so we compare path-to-path.
      try {
        var u = new URL(href, location.origin);
        href = u.pathname.toLowerCase().replace(/\/+$/, '') || '/';
      } catch (_) { /* ignore */ }
      if (!href || href === '#') return;
      var matched = false;
      if (href === '/') matched = (path === '/');
      else matched = (path === href || path.indexOf(href + '/') === 0);
      if (matched) a.classList.add('is-active');
    });
  }

  // -------------- COMMAND PALETTE ----------------------------------
  function initCmdk() {
    var cmdk = document.querySelector('.afro-cmdk');
    if (!cmdk) return;
    var input    = cmdk.querySelector('[data-afro-cmdk-input]');
    var results  = cmdk.querySelector('[data-afro-cmdk-results]');
    var suggest  = cmdk.querySelector('[data-afro-cmdk-suggested]');
    var answer   = cmdk.querySelector('[data-afro-cmdk-answer]');
    var stream   = cmdk.querySelector('[data-afro-cmdk-stream]');
    var qLabel   = cmdk.querySelector('[data-afro-cmdk-q]');
    var backBtn  = cmdk.querySelector('[data-afro-cmdk-back]');
    var navGroup = cmdk.querySelector('[data-afro-cmdk-nav]');

    // Load route index emitted by PHP for fuzzy matching
    var idx = [];
    try {
      var idxEl = document.querySelector('[data-afro-cmdk-index]');
      if (idxEl) idx = JSON.parse(idxEl.textContent || '[]');
    } catch (_) { idx = []; }
    var allRows = navGroup ? Array.prototype.slice.call(navGroup.querySelectorAll('.afro-cmdk__row')) : [];

    var active = 0;

    function open() {
      cmdk.hidden = false;
      requestAnimationFrame(function () { cmdk.classList.add('is-open'); });
      setTimeout(function () { input && input.focus(); }, 80);
    }
    function close() {
      cmdk.classList.remove('is-open');
      setTimeout(function () { cmdk.hidden = true; reset(); }, 220);
    }
    function reset() {
      if (input)   input.value = '';
      if (results) results.hidden = false;
      if (suggest) suggest.hidden = false;
      if (answer)  answer.hidden = true;
      if (stream)  stream.innerHTML = '';
      filter('');
      active = 0;
      updateActiveRow();
    }

    // Open triggers
    document.querySelectorAll('[data-afro-cmdk-open]').forEach(function (b) {
      b.addEventListener('click', function (e) { e.preventDefault(); open(); });
    });
    // Backdrop click closes
    var bd = cmdk.querySelector('[data-afro-cmdk-close]');
    if (bd) bd.addEventListener('click', close);
    // Cmd/Ctrl+K, Escape
    window.addEventListener('keydown', function (e) {
      var mod = e.metaKey || e.ctrlKey;
      if (mod && e.key && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        cmdk.hidden ? open() : close();
        return;
      }
      if (e.key === 'Escape' && !cmdk.hidden) { close(); return; }
      if (cmdk.hidden) return;
      if (e.key === 'ArrowDown')      { e.preventDefault(); active++; updateActiveRow(); }
      else if (e.key === 'ArrowUp')   { e.preventDefault(); active--; updateActiveRow(); }
      else if (e.key === 'Enter')     {
        var rows = visibleRows();
        if (input && input.value.trim() && (!rows.length || document.activeElement === input)) {
          e.preventDefault();
          ask(input.value.trim());
        } else if (rows[wrapIndex(active, rows.length)]) {
          e.preventDefault();
          var r = rows[wrapIndex(active, rows.length)];
          if (r.tagName === 'A') location.href = r.href;
          else r.click();
        }
      }
    });

    function visibleRows() {
      if (!navGroup || navGroup.hidden) return [];
      return allRows.filter(function (r) { return !r.hidden; });
    }
    function wrapIndex(i, n) {
      if (!n) return 0;
      return ((i % n) + n) % n;
    }
    function updateActiveRow() {
      var rows = visibleRows();
      if (!rows.length) return;
      active = wrapIndex(active, rows.length);
      rows.forEach(function (r, j) { r.classList.toggle('is-active', j === active); });
      var el = rows[active];
      if (el && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' });
    }

    // Suggested chips → ask
    cmdk.querySelectorAll('[data-q]').forEach(function (el) {
      el.addEventListener('click', function () { ask(el.getAttribute('data-q')); });
    });
    cmdk.querySelectorAll('[data-ai]').forEach(function (el) {
      el.addEventListener('click', function () {
        var map = {
          career: 'Build me a 12-week path to becoming an AI/ML engineer',
          quote:  'Estimate a project quote for a 6-week brand sprint',
          match:  'Match me to the right operator for a fintech repositioning',
          cert:   'How do I verify an Afrostrength certificate?'
        };
        ask(map[el.getAttribute('data-ai')] || el.textContent.trim());
      });
    });

    // Typing: filter local index. Enter sends to AI.
    if (input) {
      input.addEventListener('input', function () { filter(input.value); });
    }
    if (backBtn) backBtn.addEventListener('click', function () { reset(); if (input) input.focus(); });

    function filter(q) {
      var query = (q || '').trim().toLowerCase();
      if (!allRows.length) return;
      var anyVisible = false;
      allRows.forEach(function (row) {
        var label = (row.getAttribute('data-label') || row.textContent || '').toLowerCase();
        var kind  = (row.getAttribute('data-kind') || '').toLowerCase();
        var hit = !query || label.indexOf(query) !== -1 || kind.indexOf(query) !== -1;
        row.hidden = !hit;
        if (hit) anyVisible = true;
      });
      // If query is non-empty and we have an answer panel hidden, that's fine.
      if (query && suggest) suggest.hidden = false; // keep chips around as hints
      // Reset selection to first visible
      active = 0;
      updateActiveRow();
    }

    function ask(q) {
      if (!q) return;
      if (qLabel)  qLabel.textContent = q;
      if (input)   input.value = q;
      if (results) results.hidden = true;
      if (suggest) suggest.hidden = true;
      if (answer)  answer.hidden = false;
      if (!stream) return;
      stream.innerHTML = '';
      stream.classList.add('is-streaming');
      // Try the live AI endpoint first. Fall back to a local canned reply.
      attemptApiStream(q).catch(function () {
        renderTyped(composeReply(q));
      });
    }

    function attemptApiStream(q) {
      // Server endpoint accepts JSON and returns { text: "..." } for AI replies.
      var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
      var body = JSON.stringify({ message: q, surface: 'cmdk', _csrf: csrf });
      return fetch('/api/ai/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: body
      }).then(function (r) {
        if (!r.ok) throw new Error('http ' + r.status);
        return r.json();
      }).then(function (data) {
        var text = (data && (data.text || data.reply || data.answer)) || '';
        if (!text) throw new Error('empty');
        // Convert plain text (or markdown-ish) into the same shape as
        // composeReply() so the visual treatment matches.
        renderTyped('<p>' + escapeHtml(text).replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>');
      });
    }

    function renderTyped(html) {
      // Type-out animation. Reveals 4 chars per ~14ms tick. Works on
      // HTML by chunking on raw character positions — the browser
      // re-parses cleanly between chunks because we insert at the end.
      stream.innerHTML = '';
      var i = 0;
      var step = 4;
      (function tick() {
        if (i >= html.length) { stream.classList.remove('is-streaming'); return; }
        var chunk = html.slice(i, i + step);
        stream.insertAdjacentHTML('beforeend', chunk);
        i += step;
        setTimeout(tick, 14);
      })();
    }

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, function (c) {
        return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[c];
      });
    }

    // Local fallback replies — keep the surface alive when AI is offline.
    function composeReply(q) {
      var lc = q.toLowerCase();
      if (/ai\/?ml|engineer|career/.test(lc)) {
        return '<p><strong>12-week path · AI/ML engineer track</strong></p>'
          + '<ol class="afro-cmdk__list">'
          +   '<li><strong>Weeks 1–2 ·</strong> Python foundations + linear algebra refresher.</li>'
          +   '<li><strong>Weeks 3–5 ·</strong> Classical ML — regression, decision trees, evaluation.</li>'
          +   '<li><strong>Weeks 6–8 ·</strong> Deep learning — PyTorch, CNNs, attention.</li>'
          +   '<li><strong>Weeks 9–10 ·</strong> Production — MLOps, monitoring, drift.</li>'
          +   '<li><strong>Weeks 11–12 ·</strong> Capstone shipped + global certification sit.</li>'
          + '</ol>'
          + '<p class="afro-cmdk__cta">Next cohort opens soon. <a href="/academy">Enrol →</a></p>';
      }
      if (/cost|quote|price|pricing/.test(lc)) {
        return '<p><strong>Typical engagement ranges</strong></p>'
          + '<ul class="afro-cmdk__list">'
          +   '<li><strong>Brand sprint · 2 wks</strong> — includes positioning, voice, identity core.</li>'
          +   '<li><strong>Full brand build · 6–8 wks</strong> — adds web, deck, photography brief.</li>'
          +   '<li><strong>Software MVP · 8–14 wks</strong> — scoped against your roadmap.</li>'
          +   '<li><strong>AI implementation · scoped</strong> — pilot pricing after a 30-min discovery call.</li>'
          + '</ul>'
          + '<p class="afro-cmdk__cta">Send a paragraph &amp; we&rsquo;ll reply with a one-page quote. <a href="/contact">Start →</a></p>';
      }
      if (/match|operator|team/.test(lc)) {
        return '<p><strong>How matching works</strong></p>'
          + '<p>Tell us the industry, the outcome, and the timeline. We assemble a small operator pod from the studio and confirm availability within 48 hours.</p>'
          + '<p class="afro-cmdk__cta"><a href="/contact">Book a 30-min discovery →</a></p>';
      }
      if (/verify|certificate/.test(lc)) {
        return '<p><strong>Verify a certificate</strong></p>'
          + '<p>Every Afrostrength Academy certificate carries a 12-character hash on the bottom right. Drop it into the verifier and we&rsquo;ll confirm the recipient, programme, cohort and issue date.</p>'
          + '<p class="afro-cmdk__cta"><a href="/verify">Open the verifier →</a></p>';
      }
      if (/work|case|project/.test(lc)) {
        return '<p>Browse our most-cited recent work — strategy, identity, software and AI rollouts across African and diaspora teams.</p>'
          + '<p class="afro-cmdk__cta"><a href="/projects">Open the archive →</a></p>';
      }
      return '<p>I can help with three things on this site:</p>'
        + '<ul class="afro-cmdk__list">'
        +   '<li><strong>Studio</strong> — show work, estimate quotes, match you to operators.</li>'
        +   '<li><strong>Academy</strong> — recommend programmes, build a career path, verify certificates.</li>'
        +   '<li><strong>Reach</strong> — book a call, send a brief, find the office.</li>'
        + '</ul>'
        + '<p class="afro-cmdk__cta">Try one of the suggested questions above — or just type what you actually need.</p>';
    }
  }

  // -------------- REVEAL ON SCROLL ---------------------------------
  function initReveal() {
    var els = document.querySelectorAll('.afro-reveal');
    if (!els.length || !('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (el) { io.observe(el); });
  }
})();
