<?php $err = flash_pop('admin_error'); ?>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#0F0F0F,#1A1A1A);color:#fff;">
  <div style="width:100%;max-width:420px;background:#0A0A0A;border:1px solid rgba(255,255,255,0.08);border-radius:18px;padding:40px 32px;">
    <div style="display:inline-flex;align-items:center;gap:10px;margin-bottom:32px;">
      <?php require_once AFS_ROOT . '/src/views/partials/icons.php'; echo icon_logo(22, '#FFFFFF'); ?>
      <span class="ff-display" style="font-weight:700;font-size:18px;color:#fff;">Afrostrength · Admin</span>
    </div>
    <h1 class="ff-display" style="font-weight:700;font-size:28px;line-height:1.1;letter-spacing:-0.02em;margin:0 0 8px;color:#fff;">Sign in</h1>
    <p style="font-size:13px;color:rgba(255,255,255,0.55);margin:0 0 24px;">Use the credentials created via <code style="background:rgba(255,255,255,0.06);padding:2px 6px;border-radius:4px;">scripts/create-admin.php</code>.</p>
    <?php if ($err): ?>
      <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.3);color:#FCB7AB;padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;">
        <?= e($err) ?>
      </div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/admin/login')) ?>">
      <?= Csrf::field() ?>
      <div class="field field--dark">
        <input type="text" name="username" placeholder=" " required>
        <label>Username</label>
      </div>
      <div class="field field--dark">
        <input type="password" name="password" placeholder=" " required>
        <label>Password</label>
      </div>
      <button class="btn btn-primary btn-block" style="margin-top:16px;">Sign in →</button>
    </form>
  </div>
</div>
