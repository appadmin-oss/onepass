<?php
/** @var array $pending */
$err  = flash_pop('login_error');
$email = $pending['email'] ?? '';
?>

<header class="auth-form__head">
  <div class="auth-form__icon" aria-hidden="true">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
      <rect x="3" y="5" width="18" height="14" rx="2"/>
      <path d="M3 6l9 7 9-7"/>
    </svg>
  </div>
  <h1 class="auth-form__h1">Check your email.</h1>
  <p class="auth-form__sub">
    We sent a sign-in link to <strong><?= e($email) ?></strong>. It's good for 15 minutes.
  </p>
</header>

<?php if ($err): ?>
  <div role="alert" aria-live="assertive" class="auth-form__alert"><?= e($err) ?></div>
<?php endif; ?>

<form class="auth-form"
      action="<?= e(url('/login/verify-otp')) ?>"
      method="post"
      data-form
      novalidate
      aria-describedby="otp-help">
  <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
  <fieldset class="otp-group" aria-label="6-digit code">
    <legend class="sr-only">Enter the 6-digit code from your email</legend>
    <input type="text"
           name="code"
           inputmode="numeric"
           autocomplete="one-time-code"
           pattern="[0-9]{6}"
           maxlength="6"
           required
           autofocus
           class="otp-input"
           aria-label="6-digit code"
           placeholder="• • • • • •">
  </fieldset>

  <button type="submit" class="btn btn-primary btn-block">Continue →</button>

  <p id="otp-help" class="auth-form__help">
    The email also has a one-tap link. Either works. The code is single-use.
  </p>
</form>

<div class="auth-form__row" style="margin-top:32px;">
  <form action="<?= e(url('/login')) ?>" method="post" style="display:contents;">
    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
    <input type="hidden" name="email" value="<?= e($email) ?>">
    <?= Security::honeypotField('website') ?>
    <button type="submit" class="auth-form__link">Resend email</button>
  </form>
  <a href="<?= e(url('/login')) ?>" class="auth-form__link">Use a different email</a>
</div>

<p class="auth-form__small">
  Wrong account, or can't find the email? Check spam, then
  <a href="mailto:afrostrength@gmail.com?subject=Sign-in%20help">email us</a>.
</p>
