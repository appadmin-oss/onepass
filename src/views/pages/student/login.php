<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
$err = flash_pop('student_login_error');
?>

<section style="max-width:480px;margin:32px auto 64px;" data-reveal>
  <header style="text-align:center;margin-bottom:28px;">
    <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// ACADEMY DASHBOARD</div>
    <h1 class="h-display-2" style="margin:14px 0 12px;">Welcome <em class="grad-text">back.</em></h1>
    <p class="body-l" style="color:var(--text-mute);">Sign in to track your cohort progress and join live sessions.</p>
  </header>

  <?php if ($err): ?>
    <div role="alert" style="padding:14px 18px;border-radius:12px;background:rgba(192,57,43,0.08);border:1px solid rgba(192,57,43,0.25);color:var(--crimson);margin-bottom:20px;font-size:13px;">
      // <?= e($err) ?>
    </div>
  <?php endif; ?>

  <div style="padding:32px;border:1px solid var(--hairline);border-radius:18px;background:var(--surface);">
    <?php if (Firebase::enabled()): ?>
      <button type="button" class="btn btn-ghost btn-block" data-google-signin id="google">
        <span aria-hidden="true" style="display:inline-block;width:18px;height:18px;margin-right:10px;vertical-align:-3px;background:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 48 48%22><path fill=%22%23FFC107%22 d=%22M43.6 20.5H42V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.8 1.1 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.2-.1-2.3-.4-3.5z%22/><path fill=%22%23FF3D00%22 d=%22M6.3 14.7l6.6 4.8C14.6 16.1 18.9 13 24 13c3 0 5.8 1.1 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z%22/><path fill=%22%234CAF50%22 d=%22M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35 26.7 36 24 36c-5.3 0-9.7-3.4-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z%22/><path fill=%22%231976D2%22 d=%22M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.3-4.3 5.7l6.2 5.2C40.9 35.4 44 30.2 44 24c0-1.2-.1-2.3-.4-3.5z%22/></svg>') center/contain no-repeat;"></span>
        Continue with Google
      </button>
      <div data-google-error class="helper" style="display:none;color:var(--crimson);margin-top:10px;font-size:12px;"></div>
      <div style="display:flex;align-items:center;gap:10px;margin:20px 0 12px;color:var(--text-mute);font-size:12px;">
        <span style="flex:1;height:1px;background:var(--hairline);"></span>
        <span>or with email</span>
        <span style="flex:1;height:1px;background:var(--hairline);"></span>
      </div>
    <?php endif; ?>

    <form action="<?= e(url('/login')) ?>" method="post" data-form>
      <?= Csrf::field() ?>
      <?= Security::honeypotField('website') ?>
      <div class="field">
        <input type="email" name="email" placeholder=" " data-rules="required|email" autocomplete="email">
        <label>Email address</label>
      </div>
      <div class="field" style="margin-bottom:8px;">
        <input type="password" name="password" placeholder=" " data-rules="required|min:8" autocomplete="current-password">
        <label>Password</label>
      </div>
      <div data-failure class="helper" style="display:none;color:var(--crimson);margin-bottom:12px;"></div>
      <button class="btn btn-primary btn-block" type="submit" style="margin-top:16px;">Sign in →</button>
    </form>
  </div>

  <p class="caption" style="text-align:center;margin-top:24px;">
    Don't have an account yet?
    <a class="nav-link" href="<?= e(url('/academy/apply')) ?>" style="color:var(--crimson);">Apply free →</a>
  </p>
</section>

<?php if (Firebase::enabled()): ?>
<script type="module">
  import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.13.2/firebase-app.js';
  import { getAuth, GoogleAuthProvider, signInWithPopup }
    from 'https://www.gstatic.com/firebasejs/10.13.2/firebase-auth.js';

  const cfg = window.AFS_FIREBASE_CONFIG;
  if (!cfg || !cfg.apiKey) { console.warn('Firebase config missing.'); }
  else {
    const app = initializeApp(cfg);
    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });

    const btn = document.querySelector('[data-google-signin]');
    const err = document.querySelector('[data-google-error]');
    const csrf = document.querySelector('input[name="_csrf"]')?.value || '';

    btn?.addEventListener('click', async () => {
      err.style.display = 'none';
      btn.setAttribute('disabled', 'true');
      const original = btn.textContent;
      btn.textContent = 'Opening Google…';
      try {
        const cred = await signInWithPopup(auth, provider);
        const idToken = await cred.user.getIdToken();
        const res = await fetch('<?= e(url('/auth/google')) ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrf,
          },
          body: JSON.stringify({ idToken, _csrf: csrf })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) {
          err.textContent = data.message || 'We couldn\'t complete sign-in. Try again.';
          err.style.display = 'block';
          btn.removeAttribute('disabled');
          btn.innerHTML = original;
          return;
        }
        if (typeof gtag === 'function') gtag('event', 'login', { method: 'google' });
        location.href = data.redirect || '/dashboard';
      } catch (e) {
        // user cancelled the popup, or network error
        if (e?.code !== 'auth/popup-closed-by-user' && e?.code !== 'auth/cancelled-popup-request') {
          err.textContent = e?.message || 'Google sign-in failed.';
          err.style.display = 'block';
        }
        btn.removeAttribute('disabled');
        btn.innerHTML = original;
      }
    });
  }
</script>
<?php endif; ?>
