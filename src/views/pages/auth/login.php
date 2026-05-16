<?php
/** @var array|null $pending */
$err  = flash_pop('login_error');
?>

<header class="auth-form__head">
  <h1 class="auth-form__h1">Sign in to <em>Afrostrength.</em></h1>
  <p class="auth-form__sub">Get a sign-in link in your inbox. One tap, no password.</p>
</header>

<?php if ($err): ?>
  <div role="alert" aria-live="assertive" class="auth-form__alert"><?= e($err) ?></div>
<?php endif; ?>

<form class="auth-form"
      action="<?= e(url('/login')) ?>"
      method="post"
      data-form
      novalidate
      aria-describedby="auth-help">
  <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
  <?= Security::honeypotField('website') ?>

  <label class="field field--auth" for="auth-email">
    <span class="field__label">Email address</span>
    <input id="auth-email"
           type="email"
           name="email"
           inputmode="email"
           autocomplete="email"
           required
           autofocus
           data-rules="required|email"
           placeholder="you@studio.com">
  </label>

  <button type="submit" class="btn btn-primary btn-block">
    <span>Send me a sign-in link</span>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
      <path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>

  <details class="auth-disclosure" id="auth-help">
    <summary>What's a magic link?</summary>
    <p>
      We email you a one-time link and a 6-digit code. Tap the link or paste the
      code — either signs you in. The link expires after 15 minutes and works
      once. No password to remember, no shared device to worry about.
    </p>
  </details>
</form>

<div class="auth-form__divider" role="separator" aria-label="or">
  <span>or use</span>
</div>

<div class="auth-form__row">
  <?php if (Firebase::enabled()): ?>
    <button type="button" class="btn btn-ghost btn-block" data-google-signin>
      <span class="auth-google-mark" aria-hidden="true"></span>
      Google
    </button>
  <?php endif; ?>
  <a href="<?= e(url('/login/password')) ?>" class="btn btn-ghost btn-block">
    Password
  </a>
</div>
<div data-google-error role="alert" aria-live="polite" class="auth-form__alert auth-form__alert--inline" style="display:none;"></div>

<p class="auth-form__small">
  No account yet?
  <a href="<?= e(url('/academy/apply')) ?>">Apply to the Academy</a>
  &nbsp;·&nbsp;
  <a href="<?= e(url('/contact')) ?>">Talk to the studio</a>
</p>

<?php if (Firebase::enabled()): ?>
<script type="module">
  import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.13.2/firebase-app.js';
  import { getAuth, GoogleAuthProvider, signInWithPopup }
    from 'https://www.gstatic.com/firebasejs/10.13.2/firebase-auth.js';

  const cfg = window.AFS_FIREBASE_CONFIG;
  if (cfg && cfg.apiKey) {
    const app = initializeApp(cfg);
    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });

    const btn  = document.querySelector('[data-google-signin]');
    const err  = document.querySelector('[data-google-error]');
    const csrf = document.querySelector('input[name="_csrf"]')?.value || '';

    btn?.addEventListener('click', async () => {
      err.style.display = 'none';
      btn.setAttribute('disabled', 'true');
      const original = btn.innerHTML;
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
