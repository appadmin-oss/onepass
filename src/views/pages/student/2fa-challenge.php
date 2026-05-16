<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
$err = flash_pop('twofa_error');
?>

<section style="max-width:440px;margin:32px auto 64px;" data-reveal>
  <header style="text-align:center;margin-bottom:28px;">
    <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// SECOND FACTOR</div>
    <h1 class="h-display-2" style="margin:14px 0 12px;">One more <em class="grad-text">step.</em></h1>
    <p class="body-l" style="color:var(--text-mute);">Open your authenticator app and enter the 6-digit code for Afrostrength.</p>
  </header>

  <?php if ($err): ?>
    <div role="alert" style="padding:14px 18px;border-radius:12px;background:rgba(192,57,43,0.08);border:1px solid rgba(192,57,43,0.25);color:var(--crimson);margin-bottom:20px;font-size:13px;">
      // <?= e($err) ?>
    </div>
  <?php endif; ?>

  <form action="<?= e(url('/login/2fa')) ?>" method="post"
        style="padding:32px;border:1px solid var(--hairline);border-radius:18px;background:var(--surface);">
    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
    <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
           autocomplete="one-time-code" required autofocus
           style="width:100%;padding:14px 16px;border:1px solid var(--hairline);border-radius:12px;font-family:'JetBrains Mono',monospace;font-size:22px;letter-spacing:0.5em;text-align:center;">
    <button class="btn btn-primary btn-block" type="submit" style="margin-top:18px;">Continue →</button>
  </form>

  <p class="caption" style="text-align:center;margin-top:24px;">
    Lost your authenticator? <a class="nav-link" href="mailto:afrostrength@gmail.com?subject=Account%20recovery" style="color:var(--crimson);">Email us</a>.
  </p>
</section>
