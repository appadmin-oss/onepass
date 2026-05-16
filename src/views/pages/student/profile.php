<?php
/** @var array $student */
/** @var array $profile */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

$err = flash_pop('profile_error');
$ok  = flash_pop('profile_ok');

$bio       = (string)($profile['bio']        ?? '');
$tagline   = (string)($profile['tagline']    ?? '');
$goal      = (string)($profile['goal']       ?? '');
$skills    = is_array($profile['skills'] ?? null) ? $profile['skills'] : [];
$siteUrl   = (string)($profile['site_url']   ?? '');
$github    = (string)($profile['github_url'] ?? '');
$linkedin  = (string)($profile['linkedin_url']?? '');
$emoji     = (string)($profile['avatar_emoji']?? '');
$handle    = (string)($profile['public_handle'] ?? '');

$initials = strtoupper(mb_substr(strtok($student['name'] ?? '?', ' ') ?: '?', 0, 1));

// DiceBear avatar — abstract, brand-coloured, deterministic per-student.
// We seed by the student id so an account always renders the same shape
// across sessions, and pin the palette to our four brand inks so the
// avatar never clashes with the rest of the profile chrome. The image
// is small (~6 KB) and served from a stable CDN.
$avatarSeed = (string)($student['id'] ?? $student['email'] ?? 'student');
$diceBear   = 'https://api.dicebear.com/9.x/shapes/svg?'
    . http_build_query([
        'seed'              => $avatarSeed,
        'backgroundColor'   => 'fce7dd,f6f2ec,fafafa', // peach / warm bone / bone
        'shape1Color'       => 'c0392b',                // crimson
        'shape2Color'       => '0a0a0a',                // ink
        'shape3Color'       => '5c0000',                // maroon
        'backgroundType'    => 'solid',
        'radius'            => '50',
    ]);
?>

<?php partial('section-header', [
  'num'     => 'YOU',
  'eyebrow' => 'Profile',
  'title'   => 'Tell us who you <em>are.</em>',
  'lead'    => 'Owned by you, hosted by us. Used to route you to the right cohort lead, surface relevant opportunities, and connect you with peers in your track.',
]); ?>

<section class="profile" data-reveal>
  <aside class="profile__side">
    <div class="profile__avatar" aria-hidden="true">
      <?php if ($emoji !== ''): ?>
        <?= e($emoji) ?>
      <?php else: ?>
        <img src="<?= e($diceBear) ?>" alt="" width="96" height="96" loading="lazy" decoding="async" referrerpolicy="no-referrer">
      <?php endif; ?>
    </div>
    <div>
      <h2 class="profile__name"><?= e($student['name'] ?? 'Student') ?></h2>
      <div class="profile__email"><?= e($student['email'] ?? '') ?></div>
    </div>
    <?php if ($tagline !== ''): ?>
      <p style="font-size:14px;line-height:1.55;color:var(--text-mute);margin:0;">
        <?= e($tagline) ?>
      </p>
    <?php endif; ?>
    <div class="profile__stats">
      <div class="profile__stat">
        <strong data-profile-stat-streak>0</strong>
        <span>Day streak</span>
      </div>
      <div class="profile__stat">
        <strong data-profile-stat-week>0</strong>
        <span>This week</span>
      </div>
      <div class="profile__stat">
        <strong><?= e(ucfirst((string)($student['status'] ?? 'onboarding'))) ?></strong>
        <span>Status</span>
      </div>
      <div class="profile__stat">
        <strong><?= e($student['track_slug'] ?? '—') ?></strong>
        <span>Track</span>
      </div>
    </div>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/dashboard')) ?>">← Back to dashboard</a>
  </aside>

  <main class="profile__main">
    <?php if ($ok): ?>
      <div role="status" class="auth-form__alert" style="background:rgba(16,185,129,0.08);border-color:rgba(16,185,129,0.25);color:#0a6e51;"><?= e($ok) ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div role="alert" class="auth-form__alert"><?= e($err) ?></div>
    <?php endif; ?>

    <form action="<?= e(url('/dashboard/profile')) ?>" method="post" data-smart data-smart-key="profile">
      <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">

      <div class="profile__card">
        <h2>Identity</h2>
        <div class="grid-cols-2" style="gap:14px;">
          <label class="field field--auth">
            <span class="field__label">Display name</span>
            <input type="text" name="name" value="<?= e($student['name'] ?? '') ?>" maxlength="160" required>
          </label>
          <label class="field field--auth">
            <span class="field__label">Public handle (optional)</span>
            <input type="text" name="public_handle" value="<?= e($handle) ?>" maxlength="32" pattern="[A-Za-z0-9_\-]*" placeholder="e.g. tunde-builds" autocomplete="off">
          </label>
        </div>
        <label class="field field--auth">
          <span class="field__label">Tagline (one sentence)</span>
          <input type="text" name="tagline" value="<?= e($tagline) ?>" maxlength="120" placeholder="e.g. Backend, Lagos, learning ML.">
        </label>
        <label class="field field--auth">
          <span class="field__label">Avatar emoji (optional)</span>
          <input type="text" name="avatar_emoji" value="<?= e($emoji) ?>" maxlength="8" placeholder="🛠">
        </label>
      </div>

      <div class="profile__card">
        <h2>About</h2>
        <label class="field field--auth">
          <span class="field__label">Short bio</span>
          <textarea name="bio" rows="4" maxlength="800"
                    data-smart-counter data-smart-min="40" data-smart-max="800"
                    placeholder="Two or three lines. What you're building, what you're learning, what you'd love to work on."><?= e($bio) ?></textarea>
        </label>
        <label class="field field--auth">
          <span class="field__label">Current goal</span>
          <textarea name="goal" rows="3" maxlength="400"
                    data-smart-counter data-smart-min="20" data-smart-max="400"
                    placeholder="The single concrete outcome you're working toward in the next 90 days."><?= e($goal) ?></textarea>
        </label>
      </div>

      <div class="profile__card">
        <h2>Skills &amp; stack</h2>
        <label class="field field--auth">
          <span class="field__label">Skills (comma-separated)</span>
          <input type="text" name="skills" value="<?= e(implode(', ', $skills)) ?>" placeholder="e.g. PHP, MySQL, React, design systems, copywriting">
        </label>
        <?php if ($skills): ?>
          <div class="profile__tags" aria-label="Current skills">
            <?php foreach ($skills as $s): ?>
              <span class="profile__tag"><?= e((string)$s) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="profile__card">
        <h2>Links</h2>
        <div class="grid-cols-2" style="gap:14px;">
          <label class="field field--auth">
            <span class="field__label">Personal site</span>
            <input type="url" name="site_url" value="<?= e($siteUrl) ?>" placeholder="https://you.example" data-auto-https>
          </label>
          <label class="field field--auth">
            <span class="field__label">GitHub</span>
            <input type="url" name="github_url" value="<?= e($github) ?>" placeholder="https://github.com/you" data-auto-https>
          </label>
        </div>
        <label class="field field--auth">
          <span class="field__label">LinkedIn</span>
          <input type="url" name="linkedin_url" value="<?= e($linkedin) ?>" placeholder="https://www.linkedin.com/in/you/" data-auto-https>
        </label>
      </div>

      <div style="display:flex;gap:14px;align-items:center;">
        <button type="submit" class="btn btn-primary">Save profile</button>
        <a class="auth-form__link" href="<?= e(url('/dashboard')) ?>">Cancel</a>
      </div>
    </form>
  </main>
</section>

<script>
// Profile sidebar pulls streak + week-minutes from the tracker's
// localStorage. Honest — no separate stats endpoint to drift from truth.
(function () {
  const sid = <?= json_encode((string)($student['id'] ?? '')) ?>;
  try {
    const rows = JSON.parse(localStorage.getItem('afs_tracker_v1_' + sid) || '[]');
    const streak = (function () {
      if (!rows.length) return 0;
      const days = new Set(rows.map(r => new Date(r.ts).toISOString().slice(0,10)));
      let n = 0; const t = new Date(); t.setHours(0,0,0,0);
      for (;;) {
        const key = t.toISOString().slice(0,10);
        if (!days.has(key)) break;
        n++; t.setDate(t.getDate() - 1);
      }
      return n;
    })();
    const weekStart = (() => {
      const d = new Date(); const day = (d.getDay()+6)%7;
      d.setHours(0,0,0,0); d.setDate(d.getDate()-day);
      return d.getTime();
    })();
    const week = rows.filter(r => r.ts >= weekStart).reduce((s,r) => s + (r.minutes||0), 0);
    const s = document.querySelector('[data-profile-stat-streak]');
    const w = document.querySelector('[data-profile-stat-week]');
    if (s) s.textContent = streak;
    if (w) w.textContent = (week/60).toFixed(1) + 'h';
  } catch (_) { /* ignore */ }
})();
</script>
