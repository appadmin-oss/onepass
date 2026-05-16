<?php
/**
 * Magic-link email body.
 *
 * @var string $link      Full https URL to consume the token in one click.
 * @var string $otp       The 6-digit code shown beside the link.
 * @var int    $minutes   How long the link stays valid.
 * @var string $ip        Best-effort IP of the requester.
 * @var string $ua        Best-effort User-Agent of the requester.
 */
$minutes = $minutes ?? 15;
$ip      = $ip      ?? '';
$ua      = $ua      ?? '';
?>
<div style="background:#FAFAFA;padding:32px 0;font-family:Arial,Helvetica,sans-serif;color:#0A0A0A;">
  <div style="max-width:560px;margin:0 auto;background:#FFFFFF;border:1px solid #ECECEC;border-radius:14px;overflow:hidden;">
    <div style="padding:24px 28px;border-bottom:1px solid #ECECEC;">
      <div style="font-family:Georgia,serif;font-size:18px;font-weight:700;letter-spacing:-0.01em;">Afrostrength</div>
      <div style="font-size:11px;color:#777;text-transform:uppercase;letter-spacing:0.14em;margin-top:2px;">// Sign-in request</div>
    </div>

    <div style="padding:28px;">
      <h1 style="font-family:Georgia,serif;font-size:22px;margin:0 0 12px;line-height:1.25;">Welcome back. Tap the button below to sign in.</h1>
      <p style="font-size:14px;line-height:1.6;color:#444;margin:0 0 24px;">
        This link is valid for the next <strong><?= (int)$minutes ?> minutes</strong> and works once.
        If you didn't ask to sign in, you can ignore this email — nothing happens until you click.
      </p>

      <div style="text-align:center;margin:24px 0 28px;">
        <a href="<?= htmlspecialchars($link, ENT_QUOTES) ?>"
           style="display:inline-block;background:#C0392B;color:#FFFFFF;text-decoration:none;
                  padding:12px 24px;border-radius:999px;font-size:14px;font-weight:600;letter-spacing:0.01em;">
          Sign in to Afrostrength →
        </a>
      </div>

      <p style="font-size:12px;color:#777;margin:0 0 6px;">Prefer to paste a code? Use this one:</p>
      <div style="font-family:'JetBrains Mono','SF Mono',Menlo,monospace;font-size:24px;font-weight:600;letter-spacing:0.4em;
                  background:#F6F2EC;border:1px solid #ECECEC;border-radius:10px;padding:14px 18px;text-align:center;
                  margin:0 0 24px;color:#0A0A0A;">
        <?= htmlspecialchars(implode(' ', str_split($otp, 3))) ?>
      </div>

      <p style="font-size:12px;line-height:1.55;color:#888;margin:0;">
        If the button doesn't work, copy and paste this URL into your browser:<br>
        <span style="word-break:break-all;color:#555;"><?= htmlspecialchars($link) ?></span>
      </p>
    </div>

    <div style="padding:18px 28px;background:#F6F2EC;font-size:11px;color:#777;line-height:1.55;">
      Request from <?= htmlspecialchars($ip ?: 'an unknown network') ?><?php if ($ua) echo ' · ' . htmlspecialchars(substr($ua, 0, 80)); ?>.<br>
      If this wasn't you, no action is needed — the link will expire on its own. Reach us at
      <a href="mailto:afrostrength@gmail.com" style="color:#C0392B;">afrostrength@gmail.com</a> if anything looks off.
    </div>
  </div>
</div>
