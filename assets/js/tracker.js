/* tracker.js — offline-first learning tracker for the student dashboard.
 *
 * Storage shape (per student id):
 *   localStorage["afs_tracker_v1_<sid>"] = [
 *     { id, ts, topic, minutes, completion, notes, synced }
 *   ]
 *
 * Pure client-side. The server endpoint (/api/student/progress) is
 * called only when the user hits "Sync now" — and it's deliberately
 * optional: a student can log months of offline study, export it as
 * JSON, share it with their mentor, and never need server sync at
 * all. Online detection (navigator.onLine + window events) flips the
 * status pill.
 *
 * Why no service worker? Shared cPanel PHP hosting + a static manifest
 * isn't a great fit, and the value here is _local persistence_, not
 * offline routing. We keep it boring on purpose.
 */
(function () {
  'use strict';

  const root = document.querySelector('[data-tracker-student]');
  if (!root) return;

  const sid     = root.dataset.trackerStudent || 'anon';
  const KEY     = 'afs_tracker_v1_' + sid;
  const PENDING = 'afs_tracker_pending_v1_' + sid;
  const form    = root.querySelector('[data-tracker-form]');
  const listEl  = root.querySelector('[data-tracker-list]');
  const status  = root.querySelector('[data-tracker-status]');
  const streakEl = root.querySelector('[data-tracker-streak]');
  const weekEl   = root.querySelector('[data-tracker-week]');
  const totalEl  = root.querySelector('[data-tracker-total]');

  function load() {
    try { return JSON.parse(localStorage.getItem(KEY) || '[]'); }
    catch (_) { return []; }
  }
  function save(rows) {
    try { localStorage.setItem(KEY, JSON.stringify(rows)); }
    catch (_) { /* quota — best-effort */ }
  }

  function dayStamp(ts) {
    const d = new Date(ts);
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  function ymd(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }
  function startOfWeek(d) {
    const t = new Date(d);
    const day = (t.getDay() + 6) % 7; // Monday = 0
    t.setHours(0, 0, 0, 0);
    t.setDate(t.getDate() - day);
    return t;
  }
  function streak(rows) {
    if (!rows.length) return 0;
    const days = new Set(rows.map(r => dayStamp(r.ts)));
    let n = 0;
    const today = new Date(); today.setHours(0, 0, 0, 0);
    for (;;) {
      if (days.has(ymd(today))) {
        n++;
        today.setDate(today.getDate() - 1);
      } else break;
    }
    return n;
  }
  function thisWeek(rows) {
    const start = startOfWeek(new Date()).getTime();
    return rows.filter(r => r.ts >= start).reduce((s, r) => s + (r.minutes || 0), 0);
  }
  function setStatus(text, state) {
    if (!status) return;
    status.textContent = text;
    status.dataset.state = state || 'idle';
  }
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
  }

  function render() {
    const rows = load().sort((a, b) => b.ts - a.ts);
    streakEl && (streakEl.textContent = streak(rows));
    weekEl   && (weekEl.textContent   = (thisWeek(rows) / 60).toFixed(1) + 'h');
    totalEl  && (totalEl.textContent  = rows.length);
    if (!listEl) return;
    if (!rows.length) {
      listEl.innerHTML =
        '<p class="tracker__empty">No sessions logged yet. Your first one feels good.</p>';
      return;
    }
    // Group by day
    const byDay = new Map();
    for (const r of rows) {
      const k = dayStamp(r.ts);
      if (!byDay.has(k)) byDay.set(k, []);
      byDay.get(k).push(r);
    }
    listEl.innerHTML = [...byDay.entries()].slice(0, 14).map(([day, items]) => {
      return (
        '<div class="tracker__day">' +
          '<div class="tracker__day-h">' + escapeHtml(prettyDay(day)) + '</div>' +
          items.map(r => (
            '<div class="tracker__row" data-id="' + r.id + '">' +
              '<div class="tracker__row-h">' +
                '<strong>' + escapeHtml(r.topic) + '</strong>' +
                '<span class="tracker__chip tracker__chip--' + escapeHtml(r.completion) + '">' + escapeHtml(labelFor(r.completion)) + '</span>' +
              '</div>' +
              '<div class="tracker__row-meta">' +
                '<span>' + r.minutes + ' min</span>' +
                (r.synced ? '<span class="tracker__chip tracker__chip--synced">synced</span>'
                          : '<span class="tracker__chip tracker__chip--local">on this device</span>') +
              '</div>' +
              (r.notes ? '<div class="tracker__notes" data-tracker-md>' + escapeHtml(r.notes) + '</div>' : '') +
              '<button type="button" class="tracker__del" data-tracker-del="' + r.id + '" aria-label="Delete entry">×</button>' +
            '</div>'
          )).join('') +
        '</div>'
      );
    }).join('');
    // Once the list is in the DOM we can decorate notes with markdown
    // and (re-)render the weekly chart. Both feature-detect, so a missing
    // library just leaves the plain text + hides the chart.
    renderMarkdownNotes();
    renderWeekChart();
  }

  // -------- Markdown rendering (Marked + DOMPurify) ----------------
  // We re-render every time the list is rebuilt. The data-tracker-md
  // marker is set once; we strip it after rendering so we don't double-
  // process if the same node survives a partial DOM update.
  function renderMarkdownNotes() {
    if (!window.marked || !window.DOMPurify) return;
    const nodes = (listEl || document).querySelectorAll('[data-tracker-md]');
    nodes.forEach(function (el) {
      try {
        const raw  = el.textContent || '';
        const html = window.marked.parse(raw, { breaks: true, gfm: true });
        el.innerHTML = window.DOMPurify.sanitize(html, {
          ALLOWED_TAGS: ['p','a','ul','ol','li','strong','em','code','pre','br','blockquote'],
          ALLOWED_ATTR: ['href','title'],
          ALLOW_DATA_ATTR: false,
        });
        el.removeAttribute('data-tracker-md');
      } catch (_) { /* leave as plain text */ }
    });
  }

  // -------- Weekly minutes chart (Chart.js) ------------------------
  // Builds 7 buckets (Mon..Sun of the current ISO week). Sums minutes
  // per day. Skips rendering entirely if Chart.js isn't loaded.
  let _chart = null;
  function renderWeekChart() {
    const wrap = document.querySelector('[data-tracker-chart-wrap]');
    const cvs  = document.querySelector('[data-tracker-chart]');
    if (!wrap || !cvs) return;
    if (typeof window.Chart === 'undefined') return;

    const start = startOfWeek(new Date()).getTime();
    const rows  = load().filter(r => r.ts >= start);
    if (!rows.length) { wrap.hidden = true; return; }
    wrap.hidden = false;

    const labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    const totals = [0,0,0,0,0,0,0];
    for (const r of rows) {
      const d = new Date(r.ts);
      const idx = (d.getDay() + 6) % 7; // Monday = 0
      totals[idx] += r.minutes || 0;
    }

    // Strip the previous chart instance if we re-render (e.g. after a
    // new log entry). Chart.js keeps a handle on the canvas otherwise
    // and the next .data assignment never paints.
    if (_chart) { try { _chart.destroy(); } catch (_) {} _chart = null; }

    const ink     = getComputedStyle(document.documentElement).getPropertyValue('--ink').trim()     || '#0A0A0A';
    const crimson = getComputedStyle(document.documentElement).getPropertyValue('--crimson').trim() || '#C0392B';
    const muted   = 'rgba(10,10,10,0.55)';

    _chart = new window.Chart(cvs, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Minutes',
          data:  totals,
          backgroundColor: totals.map(function (m, i) {
            const today = (new Date().getDay() + 6) % 7;
            return i === today ? crimson : 'rgba(10,10,10,0.18)';
          }),
          borderRadius: 6,
          maxBarThickness: 28,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 250 },
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: muted, font: { family: 'JetBrains Mono', size: 10 } } },
          y: { beginAtZero: true, grid: { color: 'rgba(10,10,10,0.06)' }, ticks: { color: muted, font: { family: 'JetBrains Mono', size: 10 }, stepSize: 30 } },
        },
      },
    });
  }

  function labelFor(c) {
    return ({ 'started': 'Started', 'in-progress': 'In progress',
              'done': 'Completed', 'stuck': 'Stuck' })[c] || c;
  }

  // Use Day.js for human-friendly day headers when it's loaded; otherwise
  // fall back to the raw YYYY-MM-DD. "Today" / "Yesterday" / "Last
  // Tuesday" reads better than "2026-05-12" in a personal log.
  function prettyDay(yyyymmdd) {
    if (!window.dayjs) return yyyymmdd;
    try {
      const d = window.dayjs(yyyymmdd);
      const today = window.dayjs().startOf('day');
      const diffDays = today.diff(d, 'day');
      if (diffDays === 0) return 'Today';
      if (diffDays === 1) return 'Yesterday';
      if (diffDays > 1 && diffDays < 7) return d.format('dddd');
      return d.format('ddd, D MMM');
    } catch (_) {
      return yyyymmdd;
    }
  }

  // ----- Submit ---------------------------------------------------
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const row = {
      id:         crypto.randomUUID ? crypto.randomUUID() : 'r_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
      ts:         Date.now(),
      topic:      String(fd.get('topic') || '').trim().slice(0, 120),
      minutes:    Math.min(600, Math.max(1, parseInt(fd.get('minutes'), 10) || 25)),
      completion: ['started','in-progress','done','stuck'].includes(String(fd.get('completion'))) ? String(fd.get('completion')) : 'in-progress',
      notes:      String(fd.get('notes') || '').trim().slice(0, 600),
      synced:     false,
    };
    if (!row.topic) return;
    const rows = load();
    rows.push(row);
    save(rows);
    form.reset();
    form.querySelector('[name="minutes"]').value = 25;
    form.querySelector('[name="completion"]').value = 'in-progress';
    render();
    setStatus('Saved locally', 'idle');
  });

  // ----- Delete + bulk actions -----------------------------------
  listEl.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-tracker-del]');
    if (!btn) return;
    const id = btn.dataset.trackerDel;
    save(load().filter(r => r.id !== id));
    render();
  });

  root.querySelector('[data-tracker-export]')?.addEventListener('click', () => {
    const blob = new Blob([JSON.stringify(load(), null, 2)], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'afs-learning-' + dayStamp(Date.now()) + '.json';
    a.click();
    URL.revokeObjectURL(a.href);
  });

  root.querySelector('[data-tracker-clear]')?.addEventListener('click', () => {
    if (!confirm('Clear every local session for this device? This cannot be undone.')) return;
    save([]);
    render();
    setStatus('Cleared', 'idle');
  });

  // ----- Sync (optional; soft-fail when endpoint absent) ---------
  async function syncNow() {
    const rows = load().filter(r => !r.synced);
    if (!rows.length) { setStatus('Nothing to sync', 'idle'); return; }
    if (!navigator.onLine) { setStatus('Offline — will sync later', 'warn'); return; }
    setStatus('Syncing…', 'busy');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    try {
      const r = await fetch('/api/student/progress', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrf,
        },
        body: JSON.stringify({ rows, _csrf: csrf }),
      });
      if (!r.ok) throw new Error('Sync rejected (HTTP ' + r.status + ')');
      const data = await r.json().catch(() => ({}));
      // Mark synced if the server accepted them.
      const acceptedIds = new Set((data.accepted || rows.map(x => x.id)));
      const merged = load().map(row => acceptedIds.has(row.id) ? { ...row, synced: true } : row);
      save(merged);
      render();
      setStatus('Synced ' + acceptedIds.size + ' · ' + new Date().toLocaleTimeString(), 'ok');
    } catch (err) {
      // Endpoint not built yet — be honest about it.
      setStatus('Local only (server endpoint not yet wired)', 'warn');
    }
  }
  root.querySelector('[data-tracker-sync]')?.addEventListener('click', syncNow);

  window.addEventListener('online',  () => setStatus('Back online', 'idle'));
  window.addEventListener('offline', () => setStatus('Offline — saving locally', 'warn'));

  // ----- AI weekly review ----------------------------------------
  // Posts an ANONYMISED snapshot of the last 7 days of sessions to
  // /api/ai/suggest with intent=learning_review. We never send the
  // student id, name, or email — only what they themselves typed.
  const reviewBox  = root.querySelector('[data-tracker-review]');
  const reviewBtn  = root.querySelector('[data-tracker-review-run]');
  const reviewBody = root.querySelector('[data-tracker-review-body]');
  reviewBtn?.addEventListener('click', async () => {
    const cutoff = Date.now() - 7 * 24 * 60 * 60 * 1000;
    const rows = load().filter(r => r.ts >= cutoff)
      .slice(-30)                          // cap volume
      .map(r => ({
        // Date-only so day-of-week comes through without leaking time-of-day.
        day: dayStamp(r.ts),
        topic: String(r.topic || '').slice(0, 80),
        minutes: r.minutes,
        completion: r.completion,
        notes: String(r.notes || '').slice(0, 240),
      }));
    if (!rows.length) {
      reviewBody.textContent = 'Nothing from the past 7 days yet — log a session, then come back.';
      return;
    }
    reviewBox.dataset.state = 'busy';
    reviewBtn.disabled = true;
    const label = reviewBtn.textContent;
    reviewBtn.textContent = 'Thinking…';
    reviewBody.textContent = '';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    try {
      const r = await fetch('/api/ai/suggest', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({
          intent: 'learning_review',
          context: JSON.stringify({ sessions: rows.slice(0, 30) }),
          _csrf: csrf,
        }),
      });
      const data = await r.json().catch(() => ({}));
      if (!r.ok || !data.text) throw new Error(data.message || 'AI unavailable');
      reviewBox.dataset.state = 'ready';
      // Render lightly. We treat the AI text as plain prose; line breaks
      // become paragraphs, bullets stay bullets.
      reviewBody.innerHTML = formatReview(String(data.text));
    } catch (err) {
      reviewBox.dataset.state = 'idle';
      reviewBody.textContent = 'Couldn\'t reach the mentor right now. Try again in a moment.';
    } finally {
      reviewBtn.disabled = false;
      reviewBtn.textContent = label;
    }
  });

  function formatReview(text) {
    // Escape, then split on blank lines for paragraphs, then bullet-lines.
    const safe = text
      .replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const parts = safe.split(/\n{2,}/).map(p => p.trim()).filter(Boolean);
    return parts.map(p => {
      if (/^[-•]\s/.test(p) || p.split('\n').every(ln => /^[-•]\s/.test(ln.trim()))) {
        const items = p.split('\n').map(ln => ln.replace(/^[-•]\s+/, '').trim()).filter(Boolean);
        return '<ul style="margin:8px 0 0;padding-left:18px;display:flex;flex-direction:column;gap:6px;">' +
          items.map(i => '<li>' + i + '</li>').join('') + '</ul>';
      }
      return '<p style="margin:0 0 10px;">' + p.replace(/\n/g, '<br>') + '</p>';
    }).join('');
  }

  render();

  // Charting + markdown libraries are defer-loaded; if they aren't on
  // window when we first render, poll for ~3s and re-render once they
  // arrive. We don't re-render past that — the page is responsive in
  // its plain-text form regardless.
  (function waitForLibs() {
    let waited = 0;
    const t = setInterval(function () {
      if (window.Chart || window.marked) { clearInterval(t); render(); return; }
      waited += 150;
      if (waited >= 3000) clearInterval(t);
    }, 150);
  })();
})();
