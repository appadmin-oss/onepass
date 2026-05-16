<?php
/** @var array $report  ['overall','checkedAt','rev','services'=>[]] */
/** @var array $timeline    24h per component (legacy) */
/** @var array $timeline_90d 90d per component (new) */
/** @var array $groups       STATUS_GROUPS map */
/** @var array $incidents    active incidents */
/** @var array $scheduled    upcoming maintenance */
/** @var array $recent       recently-resolved incidents */
/** @var array $deps         dependency ledger */
/** @var bool  $subscribed */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

$overall    = $report['overall'] ?? 'up';
$overallLbl = ['up' => 'All systems normal.', 'degraded' => 'Some services are running slow.', 'down' => 'We have an active outage.'][$overall];

// Service rows keyed by component for quick group rendering.
$svcByKey = [];
foreach (($report['services'] ?? []) as $svc) {
    $svcByKey[$svc['key']] = $svc;
}
?>

<!-- ── 1. HERO VERDICT BAND ─────────────────────────────────────────
     The first thing a screenshot captures. State decides the band tone. -->
<div class="status-hero" data-state="<?= e($overall) ?>">
  <div class="status-hero__inner">
    <h1 class="status-hero__verdict" data-status-verdict data-verdict-en="<?= e($overallLbl) ?>"><?= e($overallLbl) ?></h1>
    <p class="status-hero__meta">
      <span>Build <?= e($report['rev']) ?></span>
      <span>·</span>
      <span>Checked <time datetime="<?= e((string)$report['checkedAt']) ?>" data-status-checked><?= e(date('H:i:s T', strtotime((string)$report['checkedAt']))) ?></time></span>
      <span>·</span>
      <a href="#subscribe">Subscribe</a>
      <span>·</span>
      <a href="<?= e(url('/status/api')) ?>">API</a>
    </p>
    <?php if (class_exists('Ai') && Ai::enabled()): ?>
      <!-- Multilingual hero. EN is canonical; the mentor translates the
           verdict to Naija Pidgin and Yoruba on demand. Cacheable for
           30 days per verdict string, so repeat visits are instant. -->
      <div class="status-lang" role="group" aria-label="Verdict language" data-status-lang>
        <button type="button" class="status-lang__btn is-active" data-lang="en"      aria-pressed="true">EN</button>
        <button type="button" class="status-lang__btn"           data-lang="pidgin"  aria-pressed="false" title="Naija Pidgin">Pidgin</button>
        <button type="button" class="status-lang__btn"           data-lang="yoruba"  aria-pressed="false" title="Yorùbá">Yorùbá</button>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Verdict translation. The first click on each non-EN button POSTs the
// EN verdict + the target lang to /api/ai/suggest with intent=status_translate.
// The result is cached on the dataset so re-toggling is free.
(function () {
  const root = document.querySelector('[data-status-lang]');
  const verdict = document.querySelector('[data-status-verdict]');
  if (!root || !verdict) return;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  async function translate(lang) {
    if (lang === 'en') {
      verdict.textContent = verdict.dataset.verdictEn;
      return;
    }
    const cached = verdict.dataset['verdict_' + lang];
    if (cached) { verdict.textContent = cached; return; }
    verdict.dataset.busy = '1';
    const fd = new FormData();
    fd.append('intent', 'status_translate');
    fd.append('context', JSON.stringify({ text: verdict.dataset.verdictEn, target: lang }));
    fd.append('_csrf', csrf);
    try {
      const r = await fetch('/api/ai/suggest', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: fd, credentials: 'same-origin',
      });
      const data = await r.json();
      if (data?.ok && data.text) {
        const t = data.text.trim();
        verdict.dataset['verdict_' + lang] = t;
        verdict.textContent = t;
      } else {
        verdict.textContent = verdict.dataset.verdictEn;
      }
    } catch (_) {
      verdict.textContent = verdict.dataset.verdictEn;
    } finally {
      delete verdict.dataset.busy;
    }
  }

  root.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-lang]');
    if (!btn) return;
    root.querySelectorAll('.status-lang__btn').forEach((b) => {
      const on = b === btn;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    translate(btn.dataset.lang);
  });
})();
</script>

<?php if ($subscribed): ?>
  <div role="status" class="auth-form__alert" style="background:var(--state-up-bg);border-color:var(--state-up-border);color:var(--state-up);margin-bottom:24px;">
    Subscribed. We'll only email when something material changes.
  </div>
<?php endif; ?>

<!-- ── 2. AI MENTOR SUMMARY (only when not "up") ──────────────────── -->
<section data-status data-state="<?= e($overall) ?>"
         data-status-endpoint="<?= e(url('/status/health.json')) ?>"
         data-status-ai="<?= e(url('/status/ai-summary')) ?>">
  <?php if ($overall !== 'up'): ?>
    <?php partial('ai-block', [
      'intent'   => 'status_impact_plain',
      'kind'     => 'summary',
      'eyebrow'  => '// MENTOR SUMMARY',
      'auto'     => true,
      'endpoint' => url('/status/ai-summary'),
      'context'  => json_encode(['active_incidents' => array_map(fn($r) => [
          'title' => $r['title'], 'severity' => $r['severity'],
          'components' => $r['components_csv'], 'body' => mb_substr((string)($r['body'] ?? ''), 0, 240),
      ], $incidents)]),
    ]); ?>
  <?php endif; ?>

  <!-- ── 3. COMPONENTS, GROUPED ─────────────────────────────────────
       Replaces the flat status__list. Each row: dot · name · detail
       · 90-day grid · uptime% · pill. -->
  <?php foreach ($groups as $groupLabel => $keys): ?>
    <div class="status-group">
      <h2 class="status-group__h"><?= e($groupLabel) ?></h2>
      <ul class="status-group__rows" data-status-list>
        <?php foreach ($keys as $key):
          $svc  = $svcByKey[$key] ?? null;
          if (!$svc) continue;
          $bars = $timeline_90d[$key] ?? array_fill(0, 90, 'up');
          $uptime = Incident::uptimePercent($bars);
        ?>
          <li class="status__row" data-state="<?= e($svc['state']) ?>" data-status-row="<?= e($key) ?>">
            <span class="status__dot" aria-hidden="true"></span>
            <div class="status__row-text">
              <div class="status__row-name"><?= e($svc['name']) ?></div>
              <div class="status__row-detail" data-status-detail><?= e($svc['detail']) ?></div>
            </div>
            <div class="status__grid" aria-label="Last 90 days uptime for <?= e($svc['name']) ?>">
              <?php foreach ($bars as $b): ?>
                <span class="status__grid-cell" data-state="<?= e($b) ?>"></span>
              <?php endforeach; ?>
            </div>
            <div class="status__uptime"><strong><?= e(number_format($uptime, 1)) ?>%</strong> · 90d</div>
            <span class="status__pill">
              <?= ['up' => 'Up', 'degraded' => 'Degraded', 'down' => 'Down'][$svc['state']] ?? 'Unknown' ?>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</section>

<!-- ── 4. SCHEDULED MAINTENANCE ────────────────────────────────────── -->
<?php if (!empty($scheduled)): ?>
<section data-reveal style="margin-top:48px;">
  <h2 class="h3" style="margin:0 0 14px;">Scheduled maintenance</h2>
  <?php foreach ($scheduled as $m):
    $ts = strtotime((string)$m['started_at']);
  ?>
    <article class="maint-card">
      <div class="maint-card__bar"></div>
      <div class="maint-card__body">
        <p class="maint-card__h">// MAINTENANCE</p>
        <h3 class="maint-card__title"><?= e($m['title']) ?></h3>
        <p style="margin:0;font-size:13px;color:var(--text-mute);">
          <?php $parts = array_filter(array_map('trim', explode(',', (string)$m['components_csv']))); ?>
          <?php if ($parts): ?>Affects <?= e(strtoupper(implode(' · ', $parts))) ?>.<?php endif; ?>
        </p>
      </div>
      <div class="maint-card__when">
        <time datetime="<?= e(date('c', $ts)) ?>"><?= e(date('j M · H:i T', $ts)) ?></time>
      </div>
    </article>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- Active incidents -->
<?php if ($incidents): ?>
<section data-reveal style="margin-top:48px;">
  <h2 class="h2" style="margin:0 0 16px;">Active incidents</h2>
  <ul class="incident-list" aria-label="Active incidents">
    <?php foreach ($incidents as $i):
      $code = (string)($i['public_id'] ?? '');
    ?>
      <li class="incident incident--<?= e($i['severity']) ?>">
        <div class="incident__bar"></div>
        <div class="incident__body">
          <header>
            <span class="incident__sev"><?= e(ucfirst($i['severity'])) ?></span>
            <span class="incident__time" data-incident-time data-ts="<?= e($i['started_at']) ?>">
              <time datetime="<?= e(date('c', strtotime((string)$i['started_at']))) ?>">
                <?= e(date('j M · H:i T', strtotime((string)$i['started_at']))) ?>
              </time>
            </span>
          </header>
          <h3>
            <?php if ($code): ?>
              <a href="<?= e(url('/status/incidents/' . $code)) ?>"><?= e($i['title']) ?></a>
            <?php else: ?>
              <?= e($i['title']) ?>
            <?php endif; ?>
          </h3>
          <?php if (!empty($i['body'])): ?>
            <div class="incident__text" data-md><?= e((string)$i['body']) ?></div>
          <?php endif; ?>
          <?php $parts = array_filter(array_map('trim', explode(',', (string)$i['components_csv']))); ?>
          <?php if ($parts): ?>
            <div class="incident__components">
              <?php foreach ($parts as $p): ?>
                <span class="incident__tag"><?= e(strtoupper($p)) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<!-- ── 5. HISTORY + SUBSCRIBE + INTEGRATIONS ─────────────────────── -->
<section id="subscribe" data-reveal style="margin-top:48px;">
  <div class="status-sub">
    <div>
      <h2 class="h3" style="margin:0 0 6px;">Get a note when something breaks.</h2>
      <p class="body-m" style="color:var(--text-mute);margin:0;max-width:48ch;">
        We don't mass-email. The only mail from this list is operator-authored, sent when an incident opens.
      </p>
    </div>
    <form class="status-sub__form" action="<?= e(url('/status/subscribe')) ?>" method="post" data-status-sub>
      <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
      <?= Security::honeypotField('website') ?>
      <input type="email" name="email" required placeholder="you@studio.com"
             autocomplete="email" inputmode="email" data-smart-email>
      <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
    </form>
  </div>
</section>

<?php if (!empty($recent)): ?>
<section data-reveal style="margin-top:48px;">
  <h2 class="h3" style="margin:0 0 14px;">Recently resolved</h2>
  <ul class="incident-list incident-list--past">
    <?php foreach ($recent as $i):
      $code = (string)($i['public_id'] ?? '');
    ?>
      <li class="incident incident--resolved">
        <div class="incident__body">
          <header>
            <span class="incident__sev"><?= e(ucfirst($i['severity'])) ?> · resolved</span>
            <span class="incident__time">
              <?= e(date('j M · H:i T', strtotime((string)$i['started_at']))) ?> →
              <?= e(date('H:i T', strtotime((string)($i['resolved_at'] ?? '')))) ?>
            </span>
          </header>
          <h3>
            <?php if ($code): ?>
              <a href="<?= e(url('/status/incidents/' . $code)) ?>"><?= e($i['title']) ?></a>
            <?php else: ?>
              <?= e($i['title']) ?>
            <?php endif; ?>
          </h3>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<!-- Integrations / dependencies ledger — honest about what would break -->
<section class="deps-ledger" data-reveal>
  <h2>We depend on</h2>
  <p>Third-party services we rely on. Most failures degrade rather than break us; we list the impact honestly.</p>
  <ul>
    <?php foreach ($deps as $d): ?>
      <li><div><strong><?= e($d[0]) ?></strong><span><?= e($d[1]) ?></span></div></li>
    <?php endforeach; ?>
  </ul>
</section>

<!-- Marked + DOMPurify lazy-load only when there are incident bodies. -->
<?php if ($incidents): ?>
<script src="https://cdn.jsdelivr.net/npm/marked@13.0.0/marked.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js" defer></script>
<?php endif; ?>

<script>
(function () {
  'use strict';
  const root = document.querySelector('[data-status]');
  if (!root) return;
  const endpoint = root.dataset.statusEndpoint;
  const checked  = document.querySelector('[data-status-checked]');

  // Render markdown bodies (incidents). Bounded wait for the CDN libs.
  function renderMarkdown() {
    if (!window.marked || !window.DOMPurify) return;
    document.querySelectorAll('[data-md]').forEach(function (el) {
      if (el.dataset.mdRendered) return;
      el.dataset.mdRendered = '1';
      try {
        const html = window.marked.parse(el.textContent || '', { breaks: true, gfm: true });
        el.innerHTML = window.DOMPurify.sanitize(html, {
          ALLOWED_TAGS: ['p','a','ul','ol','li','strong','em','code','pre','br','blockquote','h2','h3','h4','img'],
          ALLOWED_ATTR: ['href','title','src','alt'],
          ALLOW_DATA_ATTR: false,
        });
      } catch (_) {}
    });
  }
  (function waitMd() {
    let waited = 0;
    const t = setInterval(function () {
      if ((window.marked && window.DOMPurify) || waited >= 3000) { clearInterval(t); renderMarkdown(); }
      waited += 100;
    }, 100);
  })();

  // Live polling — update pills + details + last-checked time without
  // a full reload. New shape carries a 90-day timeline but we don't
  // re-render the grid live (cells are cheap to render at first paint;
  // a state change today is communicated via the row pill).
  async function refresh() {
    try {
      const r = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
      const data = await r.json();
      if (!data || !Array.isArray(data.services)) return;
      // Update hero band overall state
      const hero = document.querySelector('.status-hero');
      if (hero) hero.dataset.state = data.overall;
      // Update each row pill + detail
      data.services.forEach(function (s) {
        const row = document.querySelector('[data-status-row="' + s.key + '"]');
        if (!row) return;
        row.dataset.state = s.state;
        const pill = row.querySelector('.status__pill');
        if (pill) pill.textContent = ({ up: 'Up', degraded: 'Degraded', down: 'Down' })[s.state] || 'Unknown';
        const detail = row.querySelector('[data-status-detail]');
        if (detail) detail.textContent = s.detail;
      });
      if (checked) checked.textContent = new Date().toLocaleTimeString();
    } catch (_) {}
  }
  setInterval(refresh, 30000);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) refresh();
  });

  // Subscribe form — AJAX, no reload.
  const sub = document.querySelector('[data-status-sub]');
  if (sub) {
    sub.addEventListener('submit', async function (e) {
      e.preventDefault();
      const btn = sub.querySelector('button[type="submit"]');
      const labelBefore = btn.textContent;
      btn.disabled = true; btn.textContent = 'Subscribing…';
      try {
        const fd = new FormData(sub);
        const r = await fetch(sub.action, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd,
        });
        if (!r.ok) throw new Error();
        sub.innerHTML = '<p class="caption" style="margin:0;">Thanks — subscribed.</p>';
      } catch (_) {
        btn.disabled = false; btn.textContent = labelBefore;
        alert('Could not subscribe right now. Try again shortly.');
      }
    });
  }
})();
</script>
