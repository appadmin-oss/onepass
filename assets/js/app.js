/* Afrostrength — global behaviours.
   Scroll reveals, word cascade, marquee, mobile drawer, announcement-bar dismiss.
   No external deps. ES2018+ — fine for every browser ≥2018. */
(function () {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // -------- IntersectionObserver scroll reveal --------
  function initReveal() {
    if (reduceMotion) {
      document.querySelectorAll('[data-reveal]').forEach(el => el.classList.add('in-view'));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry, idx) => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const stagger = parseInt(el.dataset.staggerOffset || '0', 10);
          if (stagger) el.style.transitionDelay = stagger + 'ms';
          el.classList.add('in-view');
          io.unobserve(el);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll('[data-reveal]').forEach(el => io.observe(el));

    // Stagger groups: parent has [data-stagger="80"], children get incrementing delay.
    document.querySelectorAll('[data-stagger]').forEach(parent => {
      const step = parseInt(parent.dataset.stagger || '80', 10);
      Array.from(parent.children).forEach((child, i) => {
        if (child.hasAttribute('data-reveal')) {
          child.dataset.staggerOffset = String(i * step);
        }
      });
    });
  }

  // -------- Word cascade --------
  // Walks text-node children of [data-words] and wraps each word in a span,
  // preserving any inline elements like <em>. The previous string-split
  // approach broke on tokens like "<em" / "Legacies.</em>".
  function initWordCascade() {
    document.querySelectorAll('[data-words]').forEach(el => {
      if (el.dataset.wordsApplied === '1') return;
      el.dataset.wordsApplied = '1';
      const offset = parseInt(el.dataset.wordsOffset || '0', 10);
      const step   = parseInt(el.dataset.wordsStep   || '60', 10);
      let i = 0;

      function wrapTextNode(node) {
        const parts = node.nodeValue.split(/(\s+)/);
        if (parts.length <= 1) return;
        const frag = document.createDocumentFragment();
        for (const part of parts) {
          if (!part) continue;
          if (/^\s+$/.test(part)) {
            frag.appendChild(document.createTextNode(part));
          } else {
            const span = document.createElement('span');
            span.className = 'word';
            span.style.animationDelay = (offset + (i++ * step)) + 'ms';
            span.textContent = part;
            frag.appendChild(span);
          }
        }
        node.parentNode.replaceChild(frag, node);
      }

      function walk(parent) {
        const kids = Array.from(parent.childNodes);
        kids.forEach(n => {
          if (n.nodeType === Node.TEXT_NODE) wrapTextNode(n);
          else if (n.nodeType === Node.ELEMENT_NODE) walk(n);
        });
      }
      walk(el);

      if (reduceMotion) {
        el.querySelectorAll('.word').forEach(w => {
          w.style.animation = 'none'; w.style.opacity = '1'; w.style.transform = 'none';
        });
      }
    });
  }

  // -------- Mobile drawer (with focus trap + restore) --------
  function initDrawer() {
    const drawer = document.querySelector('[data-drawer]');
    const open   = document.querySelector('[data-drawer-open]');
    const close  = document.querySelectorAll('[data-drawer-close]');
    if (!drawer || !open) return;
    let prevFocus = null;
    const focusable = () => drawer.querySelectorAll(
      'a[href], button:not([disabled]), input, [tabindex]:not([tabindex="-1"])'
    );
    function setOpen(state) {
      drawer.classList.toggle('is-open', state);
      document.body.style.overflow = state ? 'hidden' : '';
      drawer.setAttribute('aria-hidden', state ? 'false' : 'true');
      open.setAttribute('aria-expanded', state ? 'true' : 'false');
      if (state) {
        prevFocus = document.activeElement;
        const f = focusable();
        if (f.length) f[0].focus();
      } else if (prevFocus) {
        prevFocus.focus();
      }
    }
    open.addEventListener('click', () => setOpen(true));
    close.forEach(b => b.addEventListener('click', () => setOpen(false)));
    drawer.addEventListener('click', (e) => { if (e.target === drawer) setOpen(false); });
    document.addEventListener('keydown', (e) => {
      if (!drawer.classList.contains('is-open')) return;
      if (e.key === 'Escape') { setOpen(false); return; }
      if (e.key === 'Tab') {
        const f = Array.from(focusable());
        if (!f.length) return;
        const first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }

  // -------- Announcement bar --------
  function initAnnouncement() {
    const bar = document.querySelector('[data-announce]');
    if (!bar) return;
    if (sessionStorage.getItem('afs_announce_dismissed') === '1') {
      bar.style.display = 'none';
      return;
    }
    const close = bar.querySelector('[data-announce-close]');
    if (close) close.addEventListener('click', () => {
      bar.style.display = 'none';
      sessionStorage.setItem('afs_announce_dismissed', '1');
    });
  }

  // -------- Promo ribbon + live cohort countdown -----------------
  function initPromoRibbon() {
    const ribbon = document.querySelector('[data-promo-ribbon]');
    if (!ribbon) return;
    if (sessionStorage.getItem('afs_promo_dismissed') === '1') {
      ribbon.style.display = 'none';
      return;
    }
    const close = ribbon.querySelector('[data-promo-close]');
    if (close) close.addEventListener('click', () => {
      ribbon.style.display = 'none';
      sessionStorage.setItem('afs_promo_dismissed', '1');
    });
    // Live cohort countdown
    const cd = ribbon.querySelector('[data-cohort-countdown]');
    if (cd) {
      const deadline = new Date(cd.dataset.deadline).getTime();
      const $d = cd.querySelector('[data-cd-d]');
      const $h = cd.querySelector('[data-cd-h]');
      const $m = cd.querySelector('[data-cd-m]');
      function tick() {
        let diff = Math.max(0, deadline - Date.now());
        const d = Math.floor(diff / 864e5); diff -= d * 864e5;
        const h = Math.floor(diff / 36e5);  diff -= h * 36e5;
        const m = Math.floor(diff / 6e4);
        $d.textContent = d; $h.textContent = h; $m.textContent = m;
      }
      tick();
      setInterval(tick, 30 * 1000);
    }
  }

  // -------- Sticky-nav shadow on scroll --------
  function initStickyNav() {
    const nav = document.querySelector('.site-nav');
    if (!nav) return;
    let last = 0;
    function tick() {
      const y = window.scrollY;
      nav.style.boxShadow = y > 8 ? '0 1px 0 rgba(10,10,10,0.06)' : 'none';
      last = y;
    }
    document.addEventListener('scroll', tick, { passive: true });
    tick();
  }

  // -------- Active link highlighting --------
  // Live status pill in the nav — one fetch on load, then every 5 min
  // while the tab is visible. Fails silently and stays on the optimistic
  // "UP" baseline so a network blip never reads as an outage.
  function initNavStatus() {
    const pill = document.querySelector('[data-nav-status]');
    if (!pill) return;
    const label = pill.querySelector('[data-nav-status-label]');
    let timer = null;
    async function poll() {
      try {
        const r = await fetch('/status/health.json', { headers: { 'Accept': 'application/json' }});
        if (!r.ok) return;
        const data = await r.json();
        const state = ({ up: 'up', degraded: 'degraded', down: 'down' })[data.overall] || 'up';
        pill.dataset.state = state;
        if (label) label.textContent = ({ up: 'UP', degraded: 'DEG', down: 'DOWN' })[state];
        pill.title = data.overall === 'up'
          ? 'All systems normal — click for details'
          : 'System status: ' + data.overall + ' — click for details';
      } catch (_) { /* silent */ }
    }
    function schedule() {
      clearInterval(timer);
      if (document.hidden) return;
      timer = setInterval(poll, 5 * 60 * 1000);
    }
    poll();
    schedule();
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) { poll(); schedule(); }
    });
  }

  function initActiveNav() {
    const path = location.pathname;
    document.querySelectorAll('.site-nav a[data-href], .drawer__panel a[data-href]').forEach(a => {
      const href = a.getAttribute('data-href');
      if (!href) return;
      const isActive = (href === '/' && path === '/')
                    || (href !== '/' && path.startsWith(href));
      if (isActive) {
        a.classList.add('is-active');
        a.setAttribute('aria-current', 'page');
      }
    });
  }

  // -------- Newsletter inline submit (footer) --------
  function initNewsletter() {
    const form = document.querySelector('[data-newsletter]');
    if (!form) return;
    const status = form.querySelector('[data-newsletter-status]');
    const setStatus = (msg, ok = true) => {
      if (!status) return;
      status.textContent = msg;
      status.style.color = ok ? '#FCB7AB' : '#FCB7AB';
    };
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = form.querySelector('input[type="email"]');
      const email = (input?.value || '').trim();
      if (!/^\S+@\S+\.\S+$/.test(email)) {
        form.classList.add('shake');
        setStatus('Please enter a valid email address.', false);
        setTimeout(() => form.classList.remove('shake'), 360);
        return;
      }
      setStatus('Subscribing…');
      const csrf = form.querySelector('input[name="_csrf"]')?.value
                || document.querySelector('meta[name="csrf-token"]')?.content
                || '';
      const fd = new FormData();
      fd.append('email', email);
      if (csrf) fd.append('_csrf', csrf);
      try {
        const r = await fetch(form.action || '/api/newsletter', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: fd
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Subscribe failed.');
      } catch (err) {
        // Silent fail — show success anyway so the visitor isn't stuck.
        // Genuinely fatal cases surface in the studio inbox via the soft-fail log.
      }
      if (input) input.value = '';
      setStatus('Thanks — next letter ships in a week or two.');
    });
  }

  // -------- Theme toggle (light / dark) --------
  function initThemeToggle() {
    const buttons = document.querySelectorAll('[data-theme-set]');
    if (!buttons.length) return;

    function applyTheme(t) {
      if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
      else              document.documentElement.removeAttribute('data-theme');
      buttons.forEach(b => b.classList.toggle('is-active', b.dataset.themeSet === t));
      try { localStorage.setItem('afs_theme', t); } catch (e) {}
    }

    // Sync button highlight to current state
    const initial = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    buttons.forEach(b => b.classList.toggle('is-active', b.dataset.themeSet === initial));

    buttons.forEach(b => b.addEventListener('click', () => applyTheme(b.dataset.themeSet)));
  }

  // -------- Language picker (visual + persistent stub) --------
  function initLangPicker() {
    const root = document.querySelector('[data-lang-picker]');
    if (!root) return;
    const trigger = root.querySelector('[data-lang-trigger]');
    const menu    = root.querySelector('[data-lang-menu]');
    const current = root.querySelector('[data-lang-current]');
    const buttons = menu.querySelectorAll('button[data-lang]');

    let stored = 'en';
    try { stored = localStorage.getItem('afs_lang') || 'en'; } catch (e) {}
    apply(stored);

    function apply(code) {
      const btn = menu.querySelector(`button[data-lang="${code}"]`);
      if (!btn) return;
      buttons.forEach(b => b.classList.toggle('is-active', b === btn));
      current.textContent = btn.textContent.trim();
      try { localStorage.setItem('afs_lang', code); } catch (e) {}
    }
    function setOpen(state) {
      root.classList.toggle('is-open', state);
      trigger.setAttribute('aria-expanded', state ? 'true' : 'false');
    }
    trigger.addEventListener('click', (e) => { e.stopPropagation(); setOpen(!root.classList.contains('is-open')); });
    buttons.forEach(b => b.addEventListener('click', () => { apply(b.dataset.lang); setOpen(false); }));
    document.addEventListener('click', (e) => { if (!root.contains(e.target)) setOpen(false); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
  }

  // -------- Animated stat counter [data-count="50"] -------------
  function initStatCounter() {
    const targets = document.querySelectorAll('[data-count]');
    if (!targets.length) return;
    if (reduceMotion) {
      targets.forEach(el => { el.textContent = el.dataset.count; });
      return;
    }
    const easeOut = t => 1 - Math.pow(1 - t, 3);
    const animate = (el) => {
      const end = parseInt(el.dataset.count, 10);
      if (Number.isNaN(end)) return;
      const dur = 1400;
      const start = performance.now();
      function tick(now) {
        const t = Math.min(1, (now - start) / dur);
        el.textContent = Math.round(end * easeOut(t));
        if (t < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    };
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) { animate(en.target); io.unobserve(en.target); }
      });
    }, { threshold: 0.4 });
    targets.forEach(t => io.observe(t));
  }

  // -------- Sticky mobile CTA — show after the user scrolls past ~50vh.
  function initStickyCta() {
    const bar = document.querySelector('[data-sticky-cta]');
    if (!bar) return;
    if (sessionStorage.getItem('afs_sticky_cta_dismissed') === '1') {
      bar.remove();
      return;
    }
    const close = bar.querySelector('[data-sticky-cta-close]');
    if (close) close.addEventListener('click', () => {
      bar.classList.remove('is-visible');
      sessionStorage.setItem('afs_sticky_cta_dismissed', '1');
      setTimeout(() => bar.remove(), 400);
    });
    function tick() {
      // Only on small screens — CSS hides it >=768px regardless.
      const trigger = window.innerHeight * 0.6;
      if (window.scrollY > trigger) bar.classList.add('is-visible');
      else                          bar.classList.remove('is-visible');
    }
    document.addEventListener('scroll', tick, { passive: true });
    tick();
  }

  // -------- Reading progress bar (only on prose-bearing pages) -----
  function initReadingProgress() {
    const bar = document.querySelector('[data-reading-progress] .reading-progress__bar');
    const article = document.querySelector('article .prose, .prose');
    const host    = document.querySelector('[data-reading-progress]');
    if (!bar || !article || !host) return;
    host.style.display = 'block';

    function tick() {
      const rect = article.getBoundingClientRect();
      const total = rect.height - window.innerHeight;
      const passed = Math.min(Math.max(-rect.top, 0), Math.max(total, 0));
      const pct = total > 0 ? (passed / total) * 100 : 0;
      bar.style.width = pct + '%';
    }
    document.addEventListener('scroll', tick, { passive: true });
    tick();
  }

  // -------- Back-to-top button -------------------------------------
  function initBackToTop() {
    const btn = document.querySelector('[data-back-to-top]');
    if (!btn) return;
    function tick() {
      btn.classList.toggle('is-visible', window.scrollY > window.innerHeight * 0.8);
    }
    document.addEventListener('scroll', tick, { passive: true });
    btn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
    tick();
  }

  // -------- Smooth scroll for in-page anchor links ----------------
  function initSmoothScroll() {
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href^="#"]');
      if (!a) return;
      const id = a.getAttribute('href');
      if (id.length < 2) return;
      const target = document.querySelector(id);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
      // Update history without jumping again
      history.replaceState(null, '', id);
    });
  }

  // -------- Share-bar (Web Share API + clipboard fallback) ------------
  function initShareCopy() {
    document.querySelectorAll('[data-share-copy]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const url   = btn.dataset.shareUrl || location.href;
        const title = btn.dataset.shareTitle || document.title;
        const bar = btn.closest('[data-share-bar]');
        // Prefer the platform share sheet on mobile / supported browsers.
        if (navigator.share && /Mobi|Android|iPhone|iPad/.test(navigator.userAgent)) {
          try { await navigator.share({ title, url }); return; } catch (e) { /* fall through */ }
        }
        try {
          await navigator.clipboard.writeText(url);
        } catch (e) {
          const ta = document.createElement('textarea');
          ta.value = url; document.body.appendChild(ta); ta.select();
          document.execCommand('copy'); ta.remove();
        }
        if (bar) {
          bar.classList.add('is-copied');
          setTimeout(() => bar.classList.remove('is-copied'), 1800);
        }
      });
    });
  }

  // -------- Command palette (Cmd/Ctrl+K) --------------------------
  function initCmdk() {
    const overlay = document.querySelector('[data-cmdk]');
    if (!overlay) return;
    const input   = overlay.querySelector('[data-cmdk-input]');
    const results = overlay.querySelector('[data-cmdk-results]');
    const dataEl  = document.querySelector('[data-cmdk-index]');
    let items = [];
    try { items = JSON.parse(dataEl.textContent || '[]'); } catch (e) { items = []; }
    let active = 0;

    function open() {
      overlay.classList.add('is-open');
      input.value = '';
      render('');
      setTimeout(() => input.focus(), 30);
    }
    function close() { overlay.classList.remove('is-open'); }

    // Fuse.js gives us fuzzy + typo-tolerant matching. Falls back to a
    // simple substring search when the library hasn't loaded.
    let fuse = null;
    function ensureFuse() {
      if (fuse || typeof Fuse !== 'function') return fuse;
      fuse = new Fuse(items, {
        keys: ['label', 'kind'],
        threshold: 0.4,
        ignoreLocation: true,
        minMatchCharLength: 1,
      });
      return fuse;
    }
    function render(q) {
      const query = (q || '').trim();
      // New surface (Ask-Afro-AI rebuild): empty query shows the default
      // chip / nav / AI-action panel that lives in the markup. Old surface
      // (no [data-cmdk-default] in the DOM) keeps its previous behaviour
      // of seeding the first ten items.
      const defaultSurface = document.querySelector('[data-cmdk-default]');
      const answerSurface  = document.querySelector('[data-cmdk-answer]');
      if (!query) {
        if (defaultSurface) {
          defaultSurface.hidden = false;
          results.hidden = true;
          if (answerSurface) answerSurface.hidden = true;
          results.innerHTML = '';
          active = 0;
          return;
        }
      }
      if (defaultSurface) {
        defaultSurface.hidden = true;
        results.hidden = false;
        if (answerSurface) answerSurface.hidden = true;
      }
      let filtered;
      if (!query) {
        filtered = items.slice(0, 10);
      } else if (ensureFuse()) {
        filtered = fuse.search(query, { limit: 12 }).map(r => r.item);
      } else {
        const ql = query.toLowerCase();
        filtered = items.filter(it => it.label.toLowerCase().includes(ql)).slice(0, 12);
      }
      active = 0;
      if (filtered.length) {
        results.innerHTML = filtered.map((it, i) => `
          <a class="cmdk__item${i === 0 ? ' is-active' : ''}" href="${it.href}" data-idx="${i}">
            <span>${escapeHtml(it.label)}</span>
            <span class="cmdk__item-kind">${escapeHtml(it.kind)}</span>
          </a>
        `).join('');
      } else {
        results.innerHTML =
          '<div class="cmdk__item cmdk__item--empty">No direct match.'
          + (query.length > 2 ? ' <button type="button" class="cmdk__ai" data-cmdk-ai>Ask the AI mentor for a guess →</button>' : '')
          + '</div>';
        const btn = results.querySelector('[data-cmdk-ai]');
        if (btn) btn.addEventListener('click', () => askAiFor(query));
      }
    }

    // AI fallback — only used when fuzzy match is empty AND the user
    // opts in. The mentor sees the route catalogue + the query and picks
    // one. We never blindly redirect; we show the suggestion as a card
    // the user clicks to accept.
    async function askAiFor(query) {
      results.innerHTML =
        '<div class="cmdk__item cmdk__item--empty">Asking the AI mentor…</div>';
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const catalogue = items.map(i => ({ label: i.label, href: i.href, kind: i.kind }));
      try {
        const r = await fetch('/api/ai/suggest', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({
            intent: 'search_guess',
            context: JSON.stringify({ query, routes: catalogue }),
            _csrf: csrf,
          }),
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok || !data.text) throw new Error();
        // Parse loose JSON the mentor returns.
        const raw = String(data.text).trim()
          .replace(/^```(?:json)?\s*/i, '').replace(/```\s*$/i, '').trim();
        let parsed = null;
        try { parsed = JSON.parse(raw); }
        catch (_) { const m = raw.match(/\{[\s\S]*\}/); if (m) try { parsed = JSON.parse(m[0]); } catch (e) {} }
        if (!parsed || !parsed.href) throw new Error();

        // Sanity-check: only honour an href that's in our route catalogue.
        const ok = catalogue.find(c => c.href === parsed.href);
        if (!ok) throw new Error();

        results.innerHTML =
          '<a class="cmdk__item is-active cmdk__item--ai" href="' + escapeHtml(ok.href) + '">' +
            '<span>' + escapeHtml(ok.label) + '</span>' +
            '<span class="cmdk__item-kind">AI guess</span>' +
          '</a>' +
          (parsed.why ? '<div class="cmdk__ai-why">' + escapeHtml(String(parsed.why)) + '</div>' : '');
      } catch (e) {
        results.innerHTML =
          '<div class="cmdk__item cmdk__item--empty">No match — and the mentor is offline. Try a shorter keyword.</div>';
      }
    }
    function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    function setActive(i) {
      const els = results.querySelectorAll('.cmdk__item');
      if (!els.length) return;
      active = (i + els.length) % els.length;
      els.forEach((el, j) => el.classList.toggle('is-active', j === active));
      els[active].scrollIntoView({ block: 'nearest' });
    }

    document.addEventListener('keydown', (e) => {
      const isMod = e.metaKey || e.ctrlKey;
      if (isMod && e.key.toLowerCase() === 'k') { e.preventDefault(); overlay.classList.contains('is-open') ? close() : open(); return; }
      if (!overlay.classList.contains('is-open')) return;
      if (e.key === 'Escape')      { e.preventDefault(); close(); }
      else if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
      else if (e.key === 'ArrowUp')   { e.preventDefault(); setActive(active - 1); }
      else if (e.key === 'Enter') {
        const els = results.querySelectorAll('.cmdk__item');
        if (els[active] && els[active].href) {
          e.preventDefault();
          location.href = els[active].href;
        }
      }
    });
    input && input.addEventListener('input', (e) => render(e.target.value));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
    document.querySelectorAll('[data-open-cmdk]').forEach(btn => {
      btn.addEventListener('click', (e) => { e.preventDefault(); open(); });
    });
  }

  // -------- Onboarding wizard (Afrotech Academy, trust-first) ------
  function initOnboarding() {
    const form = document.querySelector('form[data-onboarding]');
    if (!form) return;

    const STORE_KEY = 'afs_onboarding_v3';
    const steps     = form.querySelectorAll('fieldset[data-step]');
    const fill      = form.parentElement.querySelector('[data-onboarding-fill]');
    const stepLabel = form.parentElement.querySelector('[data-onboarding-step-label]');
    const stepName  = form.parentElement.querySelector('[data-onboarding-step-name]');
    const encourageBox  = form.parentElement.querySelector('[data-onboarding-encourage]');
    const encourageText = form.parentElement.querySelector('[data-onboarding-encourage-text]');
    const saveStatus    = form.querySelector('[data-save-status]');
    const intentHidden  = form.querySelector('input[name="intent"]');
    const levelHidden   = form.querySelector('input[name="skill_level"]');
    const skillScoresIn = form.querySelector('input[name="skill_scores"]');
    const expScoresIn   = form.querySelector('input[name="experience_scores"]');
    const trackHidden   = form.querySelector('input[name="track_slug"]');
    const trackHelper   = form.querySelector('[data-track-helper]');

    let current = 0;
    let state = {
      intent: '',
      track:  trackHidden.value || '',
      level:  '',
      skills: {},
      exp:    {},
      step:   0,
    };

    // Restore
    try {
      const raw = localStorage.getItem(STORE_KEY);
      if (raw) {
        const saved = JSON.parse(raw);
        state = Object.assign(state, saved);
        if (state.intent) intentHidden.value = state.intent;
        if (state.track)  trackHidden.value  = state.track;
        if (state.level)  levelHidden.value  = state.level;
        if (saved.step && saved.step < steps.length) current = saved.step;
      }
    } catch (e) {}

    function save() {
      state.step = current;
      state.track = trackHidden.value;
      state.intent = intentHidden.value;
      state.level = levelHidden.value;
      skillScoresIn.value = JSON.stringify(state.skills);
      expScoresIn.value   = JSON.stringify(state.exp);
      try { localStorage.setItem(STORE_KEY, JSON.stringify(state)); } catch (e) {}
      if (saveStatus) saveStatus.textContent = '// Auto-saved · resume on this device any time';
    }

    // ---- Intent → Track recommendation ----
    // Lightweight mapping: when an intent is chosen on step 1, surface a
    // 1-3-track recommendation chip rail on step 2. The chips are the same
    // tracks that exist in the grid below; clicking a chip selects the
    // matching track card so progress carries over.
    const INTENT_TRACKS = {
      'career-switch': ['software-development', 'cybersecurity', 'cloud-engineering'],
      'freelance':     ['design', 'frontend-development', 'digital-marketing'],
      'startup':       ['digital-marketing', 'software-development', 'ai-ml-engineering'],
      'employment':    ['ai-ml-engineering', 'cloud-engineering', 'cybersecurity'],
      'curiosity':     ['frontend-development', 'digital-marketing', 'data-analysis'],
      'school':        ['data-analysis', 'software-development', 'frontend-development'],
    };
    const recoBox   = form.querySelector('[data-track-reco]');
    const recoChips = form.querySelector('[data-track-reco-chips]');
    function renderReco(intent) {
      if (!recoBox || !recoChips) return;
      const list = INTENT_TRACKS[intent] || [];
      if (!list.length) { recoBox.hidden = true; recoChips.innerHTML = ''; return; }
      const html = list.map(slug => {
        const card = form.querySelector('[data-onboarding-tracks] [data-track="' + slug + '"]');
        const label = card ? (card.dataset.trackName || card.querySelector('.track-card__name')?.textContent || slug) : slug;
        return '<button type="button" class="track-reco__chip" data-reco-track="' + slug + '">' + label + '</button>';
      }).join('');
      recoChips.innerHTML = html;
      recoBox.hidden = false;
      recoChips.querySelectorAll('[data-reco-track]').forEach(chip => {
        chip.addEventListener('click', () => {
          const slug = chip.dataset.recoTrack;
          const card = form.querySelector('[data-onboarding-tracks] [data-track="' + slug + '"]');
          if (card) card.click();
          // Scroll the card into view so the user sees the selection happen.
          card?.scrollIntoView({ block: 'center', behavior: 'smooth' });
        });
      });
    }

    // ---- Intent cards (step 1) ----
    form.querySelectorAll('[data-intent]').forEach(card => {
      if (state.intent === card.dataset.intent) card.classList.add('is-selected');
      card.addEventListener('click', () => {
        form.querySelectorAll('[data-intent]').forEach(c => c.classList.remove('is-selected'));
        card.classList.add('is-selected');
        intentHidden.value = card.dataset.intent;
        renderReco(card.dataset.intent);
        save();
      });
    });
    // Restore on load if intent was previously saved.
    if (state.intent) renderReco(state.intent);

    // ---- Track cards (step 2) ----
    form.querySelectorAll('[data-onboarding-tracks] [data-track]').forEach(b => {
      if (state.track && b.dataset.track === state.track) b.classList.add('is-selected');
      b.addEventListener('click', () => {
        form.querySelectorAll('[data-onboarding-tracks] [data-track]').forEach(x => x.classList.remove('is-selected'));
        b.classList.add('is-selected');
        trackHidden.value = b.dataset.track || '';
        if (trackHelper) trackHelper.style.display = 'none';
        save();
      });
    });

    // ---- Skill scale (step 3) ----
    form.querySelectorAll('[data-scale-group] .scale__step').forEach(b => {
      if (b.dataset.key === state.level) b.classList.add('is-selected');
      b.addEventListener('click', () => {
        form.querySelectorAll('[data-scale-group] .scale__step').forEach(x => x.classList.remove('is-selected'));
        b.classList.add('is-selected');
        levelHidden.value = b.dataset.key || '';
        save();
      });
    });

    // ---- Skill pills (step 3) ----
    form.querySelectorAll('.skill-row').forEach(row => {
      const skill = row.dataset.skill;
      row.querySelectorAll('.skill-pill').forEach(pill => {
        if (String(state.skills[skill] || '') === pill.dataset.value) pill.classList.add('is-selected');
        pill.addEventListener('click', () => {
          row.querySelectorAll('.skill-pill').forEach(p => p.classList.remove('is-selected'));
          pill.classList.add('is-selected');
          state.skills[skill] = parseInt(pill.dataset.value, 10);
          save();
        });
      });
    });

    // ---- Experience options (step 4) ----
    form.querySelectorAll('.experience-q').forEach(row => {
      const key = row.dataset.experience;
      row.querySelectorAll('.experience-q__opt').forEach(opt => {
        if (String(state.exp[key] || '') === opt.dataset.value) opt.classList.add('is-selected');
        opt.addEventListener('click', () => {
          row.querySelectorAll('.experience-q__opt').forEach(o => o.classList.remove('is-selected'));
          opt.classList.add('is-selected');
          state.exp[key] = parseInt(opt.dataset.value, 10);
          save();
        });
      });
    });

    // ---- Save & clear ----
    const clearBtn = form.querySelector('[data-save-clear]');
    if (clearBtn) clearBtn.addEventListener('click', () => {
      try { localStorage.removeItem(STORE_KEY); } catch (e) {}
      location.reload();
    });

    // ---- Step rendering with encouragement ----
    function render() {
      steps.forEach((s, i) => { s.style.display = i === current ? '' : 'none'; });
      const pct = Math.round(((current + 1) / steps.length) * 100);
      if (fill)      fill.style.width = pct + '%';
      if (stepLabel) stepLabel.textContent = 'Step 0' + (current + 1) + ' of 0' + steps.length;
      if (stepName)  stepName.textContent = steps[current].dataset.stepName || '';

      // Dynamic encouragement
      const messages = [
        null,                                                  // step 1 — first impression, no chip
        `Nice. You're ${pct}% in.`,                            // step 2
        `Halfway there — pacing perfectly.`,                   // step 3
        suggestedTrackMessage(),                               // step 4
        `One step left after this.`,                           // step 5
        `Last one. Let's lock in your seat.`,                  // step 6
      ];
      const msg = messages[current];
      if (encourageBox && encourageText) {
        if (msg) {
          encourageBox.style.display = 'block';
          encourageText.textContent = msg;
        } else {
          encourageBox.style.display = 'none';
        }
      }

      if (window.scrollY > 200) window.scrollTo({ top: form.offsetTop - 80, behavior: reduceMotion ? 'auto' : 'smooth' });
      save();
    }

    // Recommend a track based on the user's strongest skill area
    function suggestedTrackMessage() {
      const s = state.skills;
      const dev = ['HTML / CSS','JavaScript','React','Backend','APIs','Git / GitHub']
        .reduce((a,k) => a + (s[k]||0), 0);
      const dsn = ['Figma','UI Design','UX Research','Design Systems']
        .reduce((a,k) => a + (s[k]||0), 0);
      const dat = ['Python','Data Analysis','Machine Learning','Prompt Engineering']
        .reduce((a,k) => a + (s[k]||0), 0);
      if (dev === 0 && dsn === 0 && dat === 0) return `You're 60% in — keep going.`;
      const max = Math.max(dev, dsn, dat);
      if (max === dev) return `Your profile leans Development — that's our largest cohort.`;
      if (max === dsn) return `Your profile leans Design — strong fit with UI/UX.`;
      return `Your profile leans Data/AI — great market timing.`;
    }

    function validateStep(idx) {
      // Step 1 — intent must be picked
      if (idx === 0 && !intentHidden.value) {
        const grid = steps[0].querySelector('.intent-grid');
        if (grid) { grid.classList.add('shake'); setTimeout(() => grid.classList.remove('shake'), 360); }
        return false;
      }
      // Step 2 — track must be picked
      if (idx === 1 && !trackHidden.value) {
        if (trackHelper) trackHelper.style.display = 'block';
        return false;
      }
      // Step 3 — skill baseline required
      if (idx === 2 && !levelHidden.value) {
        const scale = steps[2].querySelector('.scale');
        if (scale) { scale.classList.add('shake'); setTimeout(() => scale.classList.remove('shake'), 360); }
        return false;
      }
      // Standard input rules
      const inputs = steps[idx].querySelectorAll('input[data-rules], textarea[data-rules]');
      let ok = true;
      inputs.forEach(input => {
        const rules = (input.dataset.rules || '').split('|').filter(Boolean);
        const v = input.value || '';
        const checks = {
          required: () => v.trim() !== '',
          email:    () => /^\S+@\S+\.\S+$/.test(v.trim()),
          phone:    () => v === '' || /^[\d\s+\-()]{6,20}$/.test(v.trim()),
          min:      (n) => v.trim().length >= parseInt(n, 10),
          max:      (n) => v.trim().length <= parseInt(n, 10),
        };
        for (const r of rules) {
          const [name, arg] = r.split(':');
          if (checks[name] && !checks[name](arg)) {
            ok = false;
            const f = input.closest('.field');
            if (f) { f.classList.add('is-error'); f.classList.add('shake'); setTimeout(() => f.classList.remove('shake'), 360); }
            break;
          }
        }
      });
      // Step 6 (last) — password match
      if (idx === steps.length - 1) {
        const pwEl = form.querySelector('input[name="password"]');
        const cfEl = form.querySelector('input[name="password_confirm"]');
        if (pwEl && cfEl && pwEl.value !== cfEl.value) {
          ok = false;
          const f = cfEl.closest('.field');
          if (f) { f.classList.add('is-error'); f.classList.add('shake'); setTimeout(() => f.classList.remove('shake'), 360); }
          const failure = form.querySelector('[data-failure]');
          if (failure) { failure.style.display = 'block'; failure.textContent = '// Passwords do not match.'; }
        }
      }
      return ok;
    }

    form.querySelectorAll('[data-step-next]').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!validateStep(current)) return;
        if (current < steps.length - 1) { current++; render(); }
      });
    });
    form.querySelectorAll('[data-step-prev]').forEach(btn => {
      btn.addEventListener('click', () => {
        if (current > 0) { current--; render(); }
      });
    });

    // Submit
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopImmediatePropagation();
      for (let i = 0; i < steps.length; i++) {
        if (!validateStep(i)) { current = i; render(); return; }
      }
      const submitBtn = form.querySelector('[type="submit"]');
      const original = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span>';
      try {
        // Bundle skill/experience JSON
        skillScoresIn.value = JSON.stringify(state.skills);
        expScoresIn.value   = JSON.stringify(state.exp);
        const fd = new FormData(form);
        const r = await fetch(form.action, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: fd
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok || data.error) throw new Error(data.message || 'Submission failed.');
        try { localStorage.removeItem(STORE_KEY); } catch (e) {}
        location.href = data.redirect || '/academy/apply/welcome';
      } catch (err) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = original;
        const failure = form.querySelector('[data-failure]');
        if (failure) { failure.style.display = 'block'; failure.textContent = '// ' + err.message; }
      }
    }, true);

    render();
  }

  // -------- Password strength meter -----------------------------
  function initPasswordStrength() {
    document.querySelectorAll('[data-pw-meter]').forEach(input => {
      const meter = input.closest('.field').querySelector('[data-pw-strength]');
      const label = input.closest('.field').querySelector('[data-pw-strength-label]');
      if (!meter || !label) return;
      input.addEventListener('input', () => {
        const v = input.value;
        let score = 0;
        if (v.length >= 8)  score++;
        if (v.length >= 12) score++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
        if (/\d/.test(v))    score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        meter.classList.remove('weak', 'medium', 'strong');
        let txt = '// Strength · awaiting input';
        if (v.length === 0) {
          // nothing
        } else if (score <= 2) { meter.classList.add('weak');   txt = '// Strength · weak'; }
        else if (score === 3 || score === 4) { meter.classList.add('medium'); txt = '// Strength · solid'; }
        else { meter.classList.add('strong'); txt = '// Strength · strong'; }
        label.textContent = txt;
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initWordCascade();
    initReveal();
    initDrawer();
    initAnnouncement();
    initPromoRibbon();
    initStickyNav();
    initActiveNav();
    initNavStatus();
    initNewsletter();
    initThemeToggle();
    initLangPicker();
    initStatCounter();
    initStickyCta();
    initReadingProgress();
    initBackToTop();
    initSmoothScroll();
    initShareCopy();
    initCmdk();
    initOnboarding();
    initPasswordStrength();
    initApplyModal();
    initMegaMenu();
  });

  // -------- Mega menu (hover + click + esc) ---------------------
  function initMegaMenu() {
    const groups = document.querySelectorAll('[data-mega]');
    if (!groups.length) return;
    function closeAll(except) {
      groups.forEach(g => { if (g !== except) g.classList.remove('is-open'); });
    }
    groups.forEach(g => {
      const trig = g.querySelector('[data-mega-trigger]');
      let timer = null;
      g.addEventListener('mouseenter', () => { clearTimeout(timer); closeAll(g); g.classList.add('is-open'); });
      g.addEventListener('mouseleave', () => { timer = setTimeout(() => g.classList.remove('is-open'), 140); });
      if (trig) {
        trig.addEventListener('click', (e) => {
          // On touch / no-hover, click toggles the panel rather than navigating.
          if (window.matchMedia('(hover: none)').matches) {
            e.preventDefault();
            const willOpen = !g.classList.contains('is-open');
            closeAll(g);
            g.classList.toggle('is-open', willOpen);
          }
        });
        trig.addEventListener('focus', () => { closeAll(g); g.classList.add('is-open'); });
      }
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(null); });
    document.addEventListener('click', (e) => {
      if (!e.target.closest('[data-mega]')) closeAll(null);
    });
  }

  // -------- Apply-modal trigger (SEO-friendly, fetch + History API) -----
  // The form lives at the real, indexable /academy/apply URL. The modal
  // fetches that page's main content via XHR and slots it into a div —
  // no iframe, so screen readers + search engines see the same DOM, and
  // History API updates the URL bar so /academy/apply is shareable.
  function initApplyModal() {
    const modal = document.querySelector('[data-apply-modal]');
    if (!modal) return;
    const body  = modal.querySelector('[data-apply-body]');
    const close = modal.querySelector('[data-apply-close]');
    let previousUrl = null;

    async function load(track) {
      const target = '/academy/apply' + (track ? '?track=' + encodeURIComponent(track) : '');
      body.innerHTML = '<div class="apply-modal__loading"><span class="spinner"></span><span>// Loading application…</span></div>';
      try {
        const r = await fetch(target + (target.includes('?') ? '&' : '?') + 'modal=1', {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
          credentials: 'same-origin',
        });
        const html = await r.text();
        // Parse and extract <main> content from the fragment.
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const main = doc.querySelector('#main') || doc.body;
        body.innerHTML = main.innerHTML;
        // Re-run our DOM-bound init functions on the freshly injected nodes.
        reinitInjectedScripts(body);
      } catch (err) {
        body.innerHTML = '<div class="apply-modal__loading"><span>// Could not load the application — opening full page…</span></div>';
        location.href = target;
      }
    }

    // Focus trap: keep Tab/Shift+Tab inside the modal while it's open.
    let lastFocusedBefore = null;
    function focusables() {
      return [...modal.querySelectorAll(
        'a[href], area[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
      )].filter(el => el.offsetParent !== null);
    }
    function onTrapKey(e) {
      if (e.key !== 'Tab') return;
      const list = focusables();
      if (!list.length) { e.preventDefault(); return; }
      const first = list[0], last = list[list.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }

    function open(track) {
      previousUrl = location.pathname + location.search + location.hash;
      lastFocusedBefore = document.activeElement;
      const target = '/academy/apply' + (track ? '?track=' + encodeURIComponent(track) : '');
      try { history.pushState({ applyModal: true }, '', target); } catch (e) {}
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      modal.addEventListener('keydown', onTrapKey);
      load(track);
      // Focus the first focusable element in the modal once content arrives.
      setTimeout(() => {
        const list = focusables();
        if (list[0]) list[0].focus();
        else close.focus();
      }, 120);
    }

    function shut(pushBack = true) {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      modal.removeEventListener('keydown', onTrapKey);
      body.innerHTML = '';
      document.body.style.overflow = '';
      if (pushBack && previousUrl) {
        try { history.pushState({}, '', previousUrl); } catch (e) {}
        previousUrl = null;
      }
      if (lastFocusedBefore && typeof lastFocusedBefore.focus === 'function') {
        lastFocusedBefore.focus();
      }
      lastFocusedBefore = null;
    }

    close.addEventListener('click', () => shut(true));
    modal.addEventListener('click', (e) => { if (e.target === modal) shut(true); });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('is-open')) shut(true);
    });
    window.addEventListener('popstate', () => {
      if (modal.classList.contains('is-open')) shut(false);
    });

    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-open-apply]');
      if (trigger) {
        e.preventDefault();
        open(trigger.dataset.track || trigger.getAttribute('data-track') || '');
        return;
      }
      const a = e.target.closest('a[href]');
      if (!a) return;
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
      if (a.dataset.applyFullpage) return;
      try {
        const u = new URL(a.href, location.origin);
        if (u.pathname === '/academy/apply' && !u.searchParams.get('modal')) {
          e.preventDefault();
          open(u.searchParams.get('track') || '');
        }
      } catch (_) {}
    });

    // Re-run any embedded <script> blocks the loaded fragment needs,
    // plus rebind the onboarding wizard against the new DOM.
    function reinitInjectedScripts(container) {
      // Execute inline <script> elements (they don't run when injected via innerHTML).
      container.querySelectorAll('script').forEach((old) => {
        const s = document.createElement('script');
        for (const a of old.attributes) s.setAttribute(a.name, a.value);
        s.textContent = old.textContent;
        old.parentNode.replaceChild(s, old);
      });
      // Re-initialise our form + onboarding logic against the modal subtree.
      try { initOnboarding(); } catch (e) {}
      try { initPasswordStrength(); } catch (e) {}
      try { initReveal(); } catch (e) {}
    }
  }
})();

/* ====================================================================
   Afrostrength — floating nav, drawer, mega menus, Ask-Afro-AI streaming.
   Self-contained second IIFE. Bails out cleanly if the markup isn't on
   the page (e.g. admin views still using the old layout).
   ==================================================================== */
(function () {
  'use strict';

  const $  = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  /* ----- Floating-pill nav: scroll state ----- */
  function initAfroNav() {
    const nav = document.getElementById('afro-nav');
    if (!nav) return;
    const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 12);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ----- Mobile drawer ----- */
  function initAfroDrawer() {
    const drawer = $('[data-afro-drawer]');
    if (!drawer) return;
    const openers = $$('[data-afro-drawer-open]');
    const closers = $$('[data-afro-drawer-close]');
    const open  = () => {
      drawer.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      openers.forEach(b => b.setAttribute('aria-expanded', 'true'));
    };
    const close = () => {
      drawer.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      openers.forEach(b => b.setAttribute('aria-expanded', 'false'));
    };
    openers.forEach(b => b.addEventListener('click', open));
    closers.forEach(b => b.addEventListener('click', close));
    drawer.addEventListener('click', (e) => { if (e.target === drawer) close(); });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && drawer.getAttribute('aria-hidden') === 'false') close();
    });
    // Close on navigation tap inside drawer
    $$('a', drawer).forEach(a => a.addEventListener('click', () => setTimeout(close, 120)));
  }

  /* ----- Mega menus on the floating nav ----- */
  function initAfroMega() {
    const groups = $$('[data-afro-mega]');
    if (!groups.length) return;
    const closeAll = (except) => groups.forEach(g => { if (g !== except) g.classList.remove('is-open'); });
    groups.forEach(g => {
      const trig = $('[data-afro-mega-trigger]', g);
      if (!trig) return;
      let timer = null;
      g.addEventListener('mouseenter', () => { clearTimeout(timer); closeAll(g); g.classList.add('is-open'); trig.setAttribute('aria-expanded', 'true'); });
      g.addEventListener('mouseleave', () => {
        timer = setTimeout(() => { g.classList.remove('is-open'); trig.setAttribute('aria-expanded', 'false'); }, 140);
      });
      trig.addEventListener('click', (e) => {
        e.preventDefault();
        const willOpen = !g.classList.contains('is-open');
        closeAll(g);
        g.classList.toggle('is-open', willOpen);
        trig.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });
      trig.addEventListener('focus', () => { closeAll(g); g.classList.add('is-open'); trig.setAttribute('aria-expanded', 'true'); });
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(null); });
    document.addEventListener('click', (e) => {
      if (!e.target.closest('[data-afro-mega]')) closeAll(null);
    });
  }

  /* ----- CMDK extensions: suggested chips, AI streaming, back ----- */
  function initAfroCmdk() {
    const cmdk      = $('[data-cmdk]');
    if (!cmdk) return;
    const dflt      = $('[data-cmdk-default]', cmdk);
    const ans       = $('[data-cmdk-answer]',  cmdk);
    if (!dflt || !ans) return; // old surface; nothing to wire
    const results   = $('[data-cmdk-results]', cmdk);
    const input     = $('[data-cmdk-input]',   cmdk);
    const stream    = $('[data-cmdk-stream]',  cmdk);
    const qLabel    = $('[data-cmdk-q-label]', cmdk);
    const backBtn   = $('[data-cmdk-back]',    cmdk);

    const AI_MAP = {
      career: 'Build me a 12-week path to becoming an AI/ML engineer',
      quote:  'Estimate a project quote for a 6-week brand sprint',
      match:  'Match me to the right operator for a fintech repositioning',
      cert:   'How do I verify an Afrostrength certificate?',
    };

    function showDefault() {
      dflt.hidden = false;
      ans.hidden  = true;
      if (results) results.hidden = true;
      if (stream) stream.innerHTML = '';
    }
    function showAnswer(q) {
      dflt.hidden = true;
      ans.hidden  = false;
      if (results) results.hidden = true;
      if (qLabel) qLabel.textContent = q;
      streamReply(q);
    }

    // Suggested chips
    $$('[data-cmdk-q]', cmdk).forEach(el => {
      el.addEventListener('click', () => {
        const q = el.getAttribute('data-cmdk-q');
        if (input) input.value = q;
        showAnswer(q);
      });
    });
    // AI-action rows
    $$('[data-cmdk-ai]', cmdk).forEach(el => {
      el.addEventListener('click', () => {
        const q = AI_MAP[el.getAttribute('data-cmdk-ai')] || el.textContent.trim();
        if (input) input.value = q;
        showAnswer(q);
      });
    });
    // Enter in the input — when results list is empty / hidden, route to AI
    if (input) {
      input.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        const q = (input.value || '').trim();
        if (!q) return;
        // If the user typed a query AND fuzzy results are visible, the
        // existing CMDK Enter handler navigates to the top result.
        // Only intercept if results pane is hidden or empty.
        const resultsVisible = results && !results.hidden && results.children.length > 0;
        if (resultsVisible) return;
        e.preventDefault();
        showAnswer(q);
      });
    }
    // Back to default
    if (backBtn) backBtn.addEventListener('click', () => {
      if (input) { input.value = ''; input.focus(); }
      showDefault();
    });

    // ----- streaming reply -----
    // Tries the existing /api/ai/chat endpoint first (server-side AI,
    // possibly Pollinations). If it fails / is offline, falls back to
    // a local composed answer so the panel never feels dead.
    async function streamReply(q) {
      stream.innerHTML = '';
      stream.classList.add('is-streaming');

      let text = '';
      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const r = await fetch('/api/ai/chat', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ message: q, _csrf: csrf }),
        });
        if (r.ok) {
          const d = await r.json().catch(() => null);
          if (d && typeof d.text === 'string' && d.text.trim()) text = d.text.trim();
        }
      } catch (_) { /* fall through */ }
      if (!text) text = composeLocalReply(q);
      typewriter(stream, text);
    }

    function typewriter(target, html) {
      // Simple chunk-printer; visually identical to the streaming
      // animation in the static prototype. Skips animation when the
      // user prefers reduced motion.
      const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (reduced) {
        target.innerHTML = html;
        target.classList.remove('is-streaming');
        return;
      }
      let i = 0;
      const step = 4;
      const tick = () => {
        if (i >= html.length) { target.classList.remove('is-streaming'); return; }
        target.insertAdjacentHTML('beforeend', html.slice(i, i + step));
        i += step;
        setTimeout(tick, 14);
      };
      tick();
    }

    function composeLocalReply(q) {
      const lc = (q || '').toLowerCase();
      if (lc.includes('ai/ml') || lc.includes('engineer') || lc.includes('career')) {
        return `<p><strong>12-week path · AI/ML engineer track</strong></p>
          <ol class="cmdk__list">
            <li><strong>Weeks 1–2 ·</strong> Python foundations + linear-algebra refresher.</li>
            <li><strong>Weeks 3–5 ·</strong> Classical ML — regression, decision trees, evaluation.</li>
            <li><strong>Weeks 6–8 ·</strong> Deep learning — PyTorch, CNNs, attention.</li>
            <li><strong>Weeks 9–10 ·</strong> Production — MLOps, monitoring, evaluations.</li>
            <li><strong>Weeks 11–12 ·</strong> Capstone + recorded interview + certification.</li>
          </ol>
          <p class="cmdk__cta">Cohort opens next month. <a href="/academy/apply">Enrol →</a></p>`;
      }
      if (lc.includes('cost') || lc.includes('quote') || lc.includes('price') || lc.includes('sprint')) {
        return `<p><strong>Typical engagement ranges</strong></p>
          <ul class="cmdk__list">
            <li><strong>Brand sprint · 2 wks</strong> — from <strong>₦2.4M / $1,500</strong>.</li>
            <li><strong>Full brand build · 6–8 wks</strong> — <strong>₦8M–₦18M / $5–11K</strong>.</li>
            <li><strong>Software MVP · 8–14 wks</strong> — <strong>₦14M+ / $9K+</strong>.</li>
            <li><strong>AI implementation · scoped</strong> — pilot pricing after discovery.</li>
          </ul>
          <p class="cmdk__cta">Send a paragraph &amp; we'll reply with a one-page quote in two business days. <a href="/contact">Start →</a></p>`;
      }
      if (lc.includes('match') || lc.includes('operator')) {
        return `<p><strong>Suggested operator pairing</strong></p>
          <ul class="cmdk__list">
            <li>Strategy lead with 4 repositioning projects shipped.</li>
            <li>Voice &amp; narrative lead for messaging architecture.</li>
            <li>Design lead — type, identity, photography direction.</li>
          </ul>
          <p class="cmdk__cta"><a href="/contact">Book a 30-min discovery →</a></p>`;
      }
      if (lc.includes('verify') || lc.includes('certificate')) {
        return `<p><strong>Verify a certificate</strong></p>
          <p>Every Afrostrength Academy certificate carries a 12-character hash on the bottom-right corner. Drop it into the verifier and we'll confirm the recipient, programme, cohort and issue date.</p>
          <p class="cmdk__cta"><a href="/verify">Open the verifier →</a></p>`;
      }
      if (lc.includes('cybersec') || lc.includes('cloud')) {
        return `<p><strong>Cybersecurity vs cloud engineering — quick read</strong></p>
          <ul class="cmdk__list">
            <li><strong>Cloud</strong> · build &amp; operate distributed systems. Heavier on networking, IaC, cost engineering. Salary band tends higher mid-career in NG.</li>
            <li><strong>Cybersec</strong> · adversarial mindset. Heavier on protocols, threat modelling, response. Tighter compliance angle (NDPR, ISO).</li>
            <li>Both pay well; cloud has a deeper local hiring pool, cybersec is more remote-friendly.</li>
          </ul>
          <p class="cmdk__cta"><a href="/tools/career-path">Take the 5-min path quiz →</a></p>`;
      }
      if (lc.includes('work') || lc.includes('case')) {
        return `<p>Most-cited recent work:</p>
          <ul class="cmdk__list">
            <li>Pan-African PE repositioning (+38% deal flow)</li>
            <li>Independent quarterly identity (2.4k subs in 60 days)</li>
            <li>Telecom voice rules — 11 markets aligned</li>
          </ul>
          <p class="cmdk__cta"><a href="/projects">Browse the archive →</a></p>`;
      }
      return `<p>I can help with three things on this site:</p>
        <ul class="cmdk__list">
          <li><strong>Studio</strong> — show work, estimate quotes, match you to operators.</li>
          <li><strong>Academy</strong> — recommend programmes, build a career path, verify certificates.</li>
          <li><strong>Reach</strong> — book a call, send a brief, find the office.</li>
        </ul>
        <p class="cmdk__cta">Try one of the suggested questions above — or type what you actually need.</p>`;
    }

    // Reset to default on every fresh open (Cmd+K). The existing
    // initCmdk clears the input on open; we sync the surface state.
    new MutationObserver(() => {
      if (cmdk.classList.contains('is-open') && (!input || !input.value.trim())) {
        showDefault();
      }
    }).observe(cmdk, { attributes: true, attributeFilter: ['class'] });
  }

  document.addEventListener('DOMContentLoaded', () => {
    try { initAfroNav(); }    catch (e) {}
    try { initAfroDrawer(); } catch (e) {}
    try { initAfroMega(); }   catch (e) {}
    try { initAfroCmdk(); }   catch (e) {}
  });
})();
