<?php /** @var array $inquiry */ ?>
<header style="margin-bottom:24px;">
  <a class="nav-link" href="<?= e(url('/admin/inquiries')) ?>">← Inbox</a>
  <h1 class="ff-display" style="font-weight:700;font-size:28px;letter-spacing:-0.02em;margin:8px 0 0;">
    <?= e($inquiry['name']) ?> <span class="caption" style="font-weight:400;font-size:14px;">· <?= e($inquiry['email']) ?></span>
  </h1>
</header>

<section style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
  <div class="admin-card">
    <div class="eyebrow">// MESSAGE</div>
    <p style="font-size:16px;line-height:1.65;margin-top:14px;color:rgba(10,10,10,0.85);white-space:pre-wrap;"><?= e($inquiry['message'] ?? '') ?></p>

    <div style="margin-top:32px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
      <?php
      $fields = [
          'kind' => 'Kind', 'inquiry_type' => 'Inquiry type', 'service' => 'Service',
          'brand_stage' => 'Brand stage', 'event_size' => 'Event size', 'event_date' => 'Event date',
          'preferred_time' => 'Preferred time', 'phone' => 'Phone', 'company' => 'Company',
      ];
      foreach ($fields as $k => $l):
        if (empty($inquiry[$k])) continue; ?>
        <div>
          <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(10,10,10,0.5);"><?= e($l) ?></div>
          <div style="font-size:14px;margin-top:4px;"><?= e((string)$inquiry[$k]) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <aside class="admin-card">
    <div class="eyebrow">// STATUS</div>
    <p class="caption" style="margin:8px 0 14px;">Received <?= e(date_pretty($inquiry['created_at'])) ?></p>
    <form method="post" action="<?= e(url('/admin/inquiries/' . $inquiry['id'] . '/status')) ?>" style="display:flex;flex-direction:column;gap:10px;">
      <?= Csrf::field() ?>
      <?php foreach (['new','open','resolved'] as $s): ?>
        <button name="status" value="<?= e($s) ?>" type="submit" class="btn <?= $inquiry['status'] === $s ? 'btn-primary' : 'btn-ghost' ?> btn-block" style="justify-content:flex-start;">
          <?= e(ucfirst($s)) ?>
        </button>
      <?php endforeach; ?>
    </form>
    <hr style="border:0;border-top:1px solid var(--hairline);margin:20px 0;">
    <a class="btn btn-dark btn-block" href="mailto:<?= e($inquiry['email']) ?>?subject=Re: your Afrostrength brief">Reply by email</a>

    <?php
    $assigned  = (string)($inquiry['assigned_to'] ?? '');
    $suggested = (string)($inquiry['suggested_route'] ?? '');
    $why       = (string)($inquiry['route_why'] ?? '');
    $conf      = (float) ($inquiry['route_conf'] ?? 0);
    ?>
    <hr style="border:0;border-top:1px solid var(--hairline);margin:20px 0;">
    <div class="eyebrow">// ROUTING</div>
    <?php if ($assigned): ?>
      <p style="margin:10px 0 6px;font-size:14px;">Assigned to <strong><?= e(strtoupper($assigned)) ?></strong>.</p>
      <form method="post" action="<?= e(url('/admin/inquiries/' . $inquiry['id'] . '/route')) ?>" style="margin:0;">
        <?= Csrf::field() ?>
        <input type="hidden" name="route" value="">
        <button class="caption" type="submit" style="background:none;border:0;padding:0;color:var(--crimson);cursor:pointer;">Clear assignment</button>
      </form>
    <?php elseif ($suggested): ?>
      <p style="margin:10px 0 6px;font-size:14px;">
        Mentor suggests <strong><?= e(strtoupper($suggested)) ?></strong>
        <span class="caption" style="margin-left:6px;">conf <?= e(number_format($conf, 2)) ?></span>
      </p>
      <?php if ($why): ?>
        <p class="caption" style="margin:0 0 12px;font-style:italic;">"<?= e($why) ?>"</p>
      <?php endif; ?>
      <form method="post" action="<?= e(url('/admin/inquiries/' . $inquiry['id'] . '/route')) ?>" style="margin:0;display:flex;flex-direction:column;gap:8px;">
        <?= Csrf::field() ?>
        <select name="route" style="padding:8px;border:1px solid var(--hairline);border-radius:4px;font-size:13px;">
          <?php foreach (Inquiry::ROUTES as $route): ?>
            <option value="<?= e($route) ?>" <?= $route === $suggested ? 'selected' : '' ?>><?= e(strtoupper($route)) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-block" type="submit">Accept route</button>
      </form>
    <?php else: ?>
      <p class="caption" style="margin:10px 0;">No routing suggestion yet.</p>
      <?php if (class_exists('Ai') && Ai::enabled()): ?>
        <form method="post" action="<?= e(url('/admin/inquiries/' . $inquiry['id'] . '/route-now')) ?>" style="margin:0;">
          <?= Csrf::field() ?>
          <button class="btn btn-ghost btn-block" type="submit">✨ Run routing now</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </aside>
</section>
