<?php
/**
 * @var array  $student
 * @var ?array $course
 * @var array  $sessions
 * @var ?string $lmsUrl
 * @var ?string $jitsiUrl
 * @var bool   $lmsEnabled
 * @var bool   $jaasEnabled
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

$status = $student['status'] ?? 'onboarding';
$statusLabel = match ($status) {
    'active'      => ['Active cohort', 'pill-live'],
    'paused'      => ['Paused',         'pill-prod'],
    'withdrawn'   => ['Withdrawn',      'pill-case'],
    default       => ['Application pending', 'pill-prod'],
};
?>

<!-- Dashboard hero — cinematic personal greeting -->
<section data-reveal class="grad-ink" style="border-radius:24px;padding:48px 40px;margin:24px 0 32px;position:relative;overflow:hidden;color:#fff;">
  <span class="aurora" style="top:-160px;right:-120px;"></span>
  <div style="position:relative;z-index:2;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:20px;">
    <div>
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:rgba(255,255,255,0.65);text-transform:uppercase;">// AFROTECH ACADEMY · DASHBOARD</div>
      <h1 class="ff-display" style="font-weight:700;font-size:clamp(32px,4.5vw,52px);line-height:1.05;letter-spacing:-0.025em;margin:14px 0 8px;">
        Welcome, <em class="grad-text"><?= e(strtok($student['name'], ' ')) ?>.</em>
      </h1>
      <p style="margin:0;font-size:15px;color:rgba(255,255,255,0.78);max-width:50ch;">
        <?php if ($course): ?>You're on <strong style="color:#FCB7AB;"><?= e($course['title']) ?></strong>.<?php else: ?>Your track will be assigned after admissions confirm your seat.<?php endif; ?>
      </p>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
      <span class="pill <?= e($statusLabel[1]) ?>" style="border-color:rgba(255,255,255,0.3);color:#fff;background:rgba(255,255,255,0.06);"><?= e($statusLabel[0]) ?></span>
      <a class="btn btn-on-dark btn-sm" href="<?= e(url('/dashboard/2fa')) ?>">
        <?= empty($student['totp_enabled_at']) ? 'Enable 2FA' : '2FA on' ?>
      </a>
      <form method="post" action="<?= e(url('/logout')) ?>" style="display:inline;">
        <?= Csrf::field() ?>
        <button class="btn btn-on-dark btn-sm">Sign out</button>
      </form>
    </div>
  </div>
</section>

<!-- 3-up: current track · LMS · live sessions -->
<section data-reveal data-stagger="80" class="grid-cols-3 section-block">

  <div class="hairline" style="padding:28px;border-radius:18px;display:flex;flex-direction:column;gap:14px;">
    <div class="eyebrow">YOUR TRACK</div>
    <?php if ($course): ?>
      <h2 class="ff-display" style="font-weight:600;font-size:22px;line-height:1.2;margin:0;"><?= e($course['title']) ?></h2>
      <p class="body-m" style="color:var(--text-mute);margin:0;"><?= e($course['summary'] ?? '') ?></p>
      <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-dim);">
        <?= e(strtoupper($course['level'] ?? 'Foundations')) ?> · <?= e((string)$course['weeks']) ?> WEEKS · ₦<?= number_format((int)($course['price_naira'] ?? 0)) ?>
      </div>
      <?php if ($lmsEnabled && $lmsUrl): ?>
        <a class="btn btn-primary btn-sm" href="<?= e($lmsUrl) ?>" rel="external" style="align-self:flex-start;">Open in LMS <?= icon_chev(12) ?></a>
      <?php else: ?>
        <span class="caption">// LMS access opens when your cohort starts.</span>
      <?php endif; ?>
    <?php else: ?>
      <p class="body-m" style="color:var(--text-mute);">No track assigned yet. We'll email you once admissions matches you.</p>
      <a class="btn btn-ghost btn-sm" href="<?= e(url('/academy/courses')) ?>" style="align-self:flex-start;">Browse catalog</a>
    <?php endif; ?>
  </div>

  <div class="hairline" style="padding:28px;border-radius:18px;display:flex;flex-direction:column;gap:14px;">
    <div class="eyebrow">LIVE SESSIONS</div>
    <?php if (!empty($sessions)): ?>
      <?php foreach (array_slice($sessions, 0, 2) as $s): $ts = strtotime($s['starts_at']); ?>
        <div style="padding:12px 0;border-top:1px solid var(--hairline);">
          <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--crimson);"><?= e(date('D · d M · H:i', $ts)) ?> WAT</div>
          <div class="ff-display" style="font-weight:600;font-size:15px;margin-top:4px;"><?= e($s['title']) ?></div>
          <div class="caption" style="margin-top:2px;">w/ <?= e($s['instructor'] ?? 'Studio operator') ?> · <?= e((string)$s['duration_minutes']) ?>min</div>
        </div>
      <?php endforeach; ?>
      <a class="btn <?= $jaasEnabled ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="<?= e($jitsiUrl) ?>" style="align-self:flex-start;">
        <?= $jaasEnabled ? 'Join live room' : 'See all sessions' ?> <?= icon_chev(12) ?>
      </a>
    <?php else: ?>
      <p class="body-m" style="color:var(--text-mute);">No upcoming sessions on your calendar. Cohort orientation invite arrives once payment clears.</p>
    <?php endif; ?>
  </div>

  <div class="grad-crimson" style="padding:28px;border-radius:18px;color:#fff;display:flex;flex-direction:column;gap:14px;">
    <div class="ff-mono" style="font-size:10px;letter-spacing:0.22em;text-transform:uppercase;color:rgba(255,255,255,0.78);">// QUICK ACTIONS</div>
    <p style="font-family:'Garet',sans-serif;font-weight:600;font-size:18px;line-height:1.3;letter-spacing:-0.01em;margin:0;color:#fff;">
      Need to update something or talk to admissions?
    </p>
    <a class="btn btn-on-dark btn-sm" href="<?= e(url('/contact')) ?>" style="align-self:flex-start;">Talk to studio →</a>
    <a class="nav-link" href="<?= e(url('/tools')) ?>" style="color:#FCB7AB;font-size:13px;">Open free tools →</a>
  </div>

</section>

<!-- Next steps -->
<section data-reveal class="hairline section-block" style="padding:36px;border-radius:18px;">
  <div class="eyebrow">WHAT'S NEXT</div>
  <ol style="list-style:none;padding:0;margin:18px 0 0;display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-top:1px solid var(--hairline);">
    <li style="padding:16px 0;border-right:1px solid var(--hairline);display:flex;gap:14px;padding-right:18px;">
      <span class="ff-mono" style="color:var(--crimson);font-size:11px;letter-spacing:0.18em;">01</span>
      <div>
        <strong style="font-family:'Garet',sans-serif;font-size:15px;display:block;">Confirm your seat</strong>
        <span class="caption">Admissions reach out within one working day.</span>
      </div>
    </li>
    <li style="padding:16px 18px;border-right:1px solid var(--hairline);display:flex;gap:14px;">
      <span class="ff-mono" style="color:var(--crimson);font-size:11px;letter-spacing:0.18em;">02</span>
      <div>
        <strong style="font-family:'Garet',sans-serif;font-size:15px;display:block;">Cohort orientation</strong>
        <span class="caption">A 30-min live session on Jitsi with your lead.</span>
      </div>
    </li>
    <li style="padding:16px 0;padding-left:18px;display:flex;gap:14px;">
      <span class="ff-mono" style="color:var(--crimson);font-size:11px;letter-spacing:0.18em;">03</span>
      <div>
        <strong style="font-family:'Garet',sans-serif;font-size:15px;display:block;">Day one</strong>
        <span class="caption">LMS access, mentor pairing, hands on the keyboard.</span>
      </div>
    </li>
  </ol>
</section>

<!-- ============================================================
     Offline Learning Tracker
     ------------------------------------------------------------
     For students who study on intermittent connections (or
     self-pace ahead of cohort). All data lives in localStorage,
     keyed by student id, so it survives reloads, works offline,
     and never leaves the device until the operator wires the
     /api/student/progress endpoint to sync it server-side.
     ============================================================ -->
<section id="tracker" data-reveal class="tracker"
         aria-labelledby="tracker-h"
         data-tracker-student="<?= e((string)$student['id']) ?>">

  <header class="tracker__head">
    <div>
      <div class="ff-mono tracker__eye">// LEARNING TRACKER</div>
      <h2 id="tracker-h" class="tracker__h">What did you work on today?</h2>
      <p class="tracker__sub">
        Log offline sessions, lesson completions, notes. Stays on this device until we
        sync it. <span data-tracker-status data-state="idle">Ready</span>.
      </p>
    </div>
    <div class="tracker__metrics" aria-live="polite">
      <div class="tracker__metric"><strong data-tracker-streak>0</strong><span>day streak</span></div>
      <div class="tracker__metric"><strong data-tracker-week>0</strong><span>this week</span></div>
      <div class="tracker__metric"><strong data-tracker-total>0</strong><span>total</span></div>
    </div>
  </header>

  <!-- Weekly minutes chart. Rendered by Chart.js when the lib + data
       are ready; falls back to nothing when either is missing. -->
  <div class="tracker__chart-wrap" data-tracker-chart-wrap hidden>
    <canvas data-tracker-chart aria-label="Last 7 days of study minutes"></canvas>
  </div>

  <form class="tracker__form" data-tracker-form>
    <label class="field field--auth">
      <span class="field__label">Lesson / topic</span>
      <input type="text" name="topic" required maxlength="120" placeholder="e.g. Auth flows · Step 4">
    </label>
    <div class="tracker__grid">
      <label class="field field--auth">
        <span class="field__label">Minutes</span>
        <input type="number" name="minutes" min="1" max="600" step="5" value="25" required inputmode="numeric">
      </label>
      <label class="field field--auth">
        <span class="field__label">Completion</span>
        <select name="completion">
          <option value="started">Started</option>
          <option value="in-progress" selected>In progress</option>
          <option value="done">Completed</option>
          <option value="stuck">Stuck — needs help</option>
        </select>
      </label>
    </div>
    <label class="field field--auth">
      <span class="field__label">Notes (optional)</span>
      <textarea name="notes" rows="2" maxlength="600"
                placeholder="A line on what you learned, what's blocking, what's next."></textarea>
    </label>
    <button type="submit" class="btn btn-primary">Log session</button>
  </form>

  <div class="tracker__list" data-tracker-list aria-label="Recent sessions">
    <!-- rendered client-side by app.js → initLearningTracker() -->
  </div>

  <!-- AI weekly review — calls /api/ai/suggest with intent=learning_review.
       Sends only an anonymised snapshot of the local sessions (no email/name).
       Free upstream, soft-fails on outage. -->
  <div class="tracker__review" data-tracker-review data-state="idle">
    <div class="tracker__review-h">
      <span class="ff-mono">// MENTOR REVIEW</span>
      <button type="button" class="btn btn-ghost btn-sm" data-tracker-review-run>
        Run an AI review of the past 7 days
      </button>
    </div>
    <div class="tracker__review-body" data-tracker-review-body></div>
  </div>

  <div class="tracker__foot">
    <button type="button" class="auth-form__link" data-tracker-export>Export JSON</button>
    <button type="button" class="auth-form__link" data-tracker-clear>Clear local data</button>
    <button type="button" class="auth-form__link" data-tracker-sync>Sync now</button>
    <a class="auth-form__link" href="<?= e(url('/dashboard/profile')) ?>">Profile</a>
  </div>
</section>

<style>
  /* Stack on mobile */
  @media (max-width: 800px) {
    .is-dashboard section ol { grid-template-columns: 1fr !important; }
    .is-dashboard section ol li { border-right: 0 !important; border-bottom: 1px solid var(--hairline); padding-left: 0 !important; padding-right: 0 !important; }
    .is-dashboard section ol li:last-child { border-bottom: 0; }
  }
</style>

<!-- Charting + markdown + date libraries — loaded only on the dashboard
     so we don't tax every page. tracker.js feature-detects window.Chart
     / window.marked / window.DOMPurify / window.dayjs and degrades
     gracefully when any of them fails to load. -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/marked@13.0.0/marked.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/dayjs.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1.11.13/plugin/relativeTime.js" defer></script>
<script>
  // Wire the relativeTime plugin once both globals exist. defer-load
  // order isn't guaranteed across networks, so we wait.
  (function () {
    let waited = 0;
    const t = setInterval(function () {
      if (window.dayjs && window.dayjs_plugin_relativeTime) {
        clearInterval(t);
        window.dayjs.extend(window.dayjs_plugin_relativeTime);
      } else if ((waited += 100) >= 3000) {
        clearInterval(t);
      }
    }, 100);
  })();
</script>
