<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
$err  = flash_pop('twofa_error');
$ok   = flash_pop('twofa_ok');
?>

<section style="max-width:640px;margin:32px auto 64px;" data-reveal>
  <header style="margin-bottom:32px;">
    <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// SECURITY</div>
    <h1 class="h-display-2" style="margin:14px 0 12px;">Two-factor <em class="grad-text">authentication</em></h1>
    <p class="body-l" style="color:var(--text-mute);">
      <?php if ($isEnabled): ?>
        2FA is on for this account. You'll be asked for a 6-digit code from your authenticator app on every sign-in.
      <?php else: ?>
        Add a second factor to your sign-in. We use the open TOTP standard, so any free authenticator works
        (Google Authenticator, 1Password, Aegis, Authy, Microsoft Authenticator).
      <?php endif; ?>
    </p>
  </header>

  <?php if ($ok): ?>
    <div role="alert" style="padding:14px 18px;border-radius:12px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#0a6e51;margin-bottom:20px;font-size:13px;">
      <?= e($ok) ?>
    </div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div role="alert" style="padding:14px 18px;border-radius:12px;background:rgba(192,57,43,0.08);border:1px solid rgba(192,57,43,0.25);color:var(--crimson);margin-bottom:20px;font-size:13px;">
      // <?= e($err) ?>
    </div>
  <?php endif; ?>

  <?php if (!$isEnabled): ?>
    <div style="padding:32px;border:1px solid var(--hairline);border-radius:18px;background:var(--surface);">
      <ol style="padding-left:18px;margin:0 0 24px;display:flex;flex-direction:column;gap:18px;font-size:14px;">
        <li>
          <strong>Install an authenticator app.</strong>
          We recommend <em>Aegis</em> (Android, open-source) or <em>1Password</em>.
          Google Authenticator and any other RFC 6238 app also works.
        </li>
        <li>
          <strong>Scan this QR code (or copy the key below).</strong>
          <div style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;margin-top:14px;">
            <?php if ($qrUrl): ?>
              <img src="<?= e($qrUrl) ?>" alt="2FA QR code" width="220" height="220" style="border:1px solid var(--hairline);border-radius:12px;background:#fff;padding:8px;">
            <?php endif; ?>
            <div>
              <div class="eyebrow" style="margin-bottom:6px;">// MANUAL KEY</div>
              <code style="font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:0.04em;background:var(--bone-warm);padding:8px 12px;border-radius:8px;display:inline-block;word-break:break-all;max-width:100%;"><?= e(chunk_split((string)$secret, 4, ' ')) ?></code>
              <p style="font-size:12px;color:var(--text-mute);margin-top:10px;max-width:32ch;">
                Algorithm: SHA-1 · Digits: 6 · Period: 30s.
              </p>
            </div>
          </div>
        </li>
        <li>
          <strong>Enter the 6-digit code your app shows.</strong>
          <form action="<?= e(url('/dashboard/2fa/enable')) ?>" method="post" style="margin-top:14px;">
            <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
            <div style="display:flex;gap:10px;align-items:center;">
              <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required
                     style="width:160px;padding:10px 14px;border:1px solid var(--hairline);border-radius:10px;font-family:'JetBrains Mono',monospace;font-size:18px;letter-spacing:0.4em;text-align:center;">
              <button type="submit" class="btn btn-primary">Turn on 2FA →</button>
            </div>
          </form>
        </li>
      </ol>
      <p style="font-size:12px;color:var(--text-mute);margin:0;">
        Lost your authenticator? Email <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a> from the address on file and we'll reset it after identity checks.
      </p>
    </div>
  <?php else: ?>
    <div style="padding:32px;border:1px solid var(--hairline);border-radius:18px;background:var(--surface);">
      <p style="font-size:14px;color:var(--text-mute);margin:0 0 24px;">
        Turning 2FA off removes the second factor. Confirm your password to continue.
      </p>
      <form action="<?= e(url('/dashboard/2fa/disable')) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <div class="field">
          <input type="password" name="password" placeholder=" " required autocomplete="current-password">
          <label>Current password</label>
        </div>
        <button type="submit" class="btn btn-ghost">Turn off 2FA</button>
      </form>
    </div>
  <?php endif; ?>

  <p class="caption" style="text-align:center;margin-top:24px;">
    <a class="nav-link" href="<?= e(url('/dashboard')) ?>">← Back to dashboard</a>
  </p>
</section>
