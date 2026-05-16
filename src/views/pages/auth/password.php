<?php $err = flash_pop('student_login_error'); ?>

<header class="auth-form__head">
  <h1 class="auth-form__h1">Sign in with a password.</h1>
  <p class="auth-form__sub">For accounts created before passwordless sign-in.</p>
</header>

<?php if ($err): ?>
  <div role="alert" aria-live="assertive" class="auth-form__alert"><?= e($err) ?></div>
<?php endif; ?>

<form class="auth-form" action="<?= e(url('/login/password')) ?>" method="post" data-form novalidate>
  <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
  <?= Security::honeypotField('website') ?>

  <label class="field field--auth" for="pw-email">
    <span class="field__label">Email address</span>
    <input id="pw-email" type="email" name="email" autocomplete="email" required data-rules="required|email">
  </label>
  <label class="field field--auth" for="pw-password">
    <span class="field__label">Password</span>
    <input id="pw-password" type="password" name="password" autocomplete="current-password" required minlength="8" data-rules="required|min:8">
  </label>

  <button type="submit" class="btn btn-primary btn-block">Sign in →</button>
</form>

<p class="auth-form__small">
  Prefer passwordless? <a href="<?= e(url('/login')) ?>">Use a magic link</a> instead.
</p>
