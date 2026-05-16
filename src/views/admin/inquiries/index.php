<?php /** @var array $inquiries */ ?>
<header style="margin-bottom:24px;">
  <div class="eyebrow">// INBOX</div>
  <h1 class="ff-display" style="font-weight:700;font-size:32px;letter-spacing:-0.02em;margin:8px 0 0;">Inquiries</h1>
</header>

<section class="admin-card">
  <table class="admin-table">
    <thead><tr><th>Name</th><th>Kind</th><th>Service</th><th>Status</th><th>Route</th><th>Received</th><th></th></tr></thead>
    <tbody>
      <?php if (empty($inquiries)): ?>
        <tr><td colspan="7"><span class="caption">Inbox empty.</span></td></tr>
      <?php endif; foreach ($inquiries as $r):
        $assigned   = (string)($r['assigned_to'] ?? '');
        $suggested  = (string)($r['suggested_route'] ?? '');
        $why        = (string)($r['route_why'] ?? '');
        $conf       = (float) ($r['route_conf'] ?? 0);
      ?>
        <tr>
          <td><strong><?= e($r['name']) ?></strong><br><span class="caption"><?= e($r['email']) ?></span></td>
          <td><?= e($r['kind']) ?></td>
          <td><?= e($r['service'] ?? '—') ?></td>
          <td>
            <span class="pill <?= $r['status'] === 'new' ? 'pill-live' : ($r['status'] === 'open' ? 'pill-prod' : 'pill-case') ?>">
              <?= e(strtoupper($r['status'])) ?>
            </span>
          </td>
          <td>
            <?php if ($assigned): ?>
              <span class="pill pill-case" title="Assigned"><?= e(strtoupper($assigned)) ?></span>
            <?php elseif ($suggested): ?>
              <form method="post" action="<?= e(url('/admin/inquiries/' . $r['id'] . '/route')) ?>" style="display:inline-flex;align-items:center;gap:6px;margin:0;">
                <?= Csrf::field() ?>
                <input type="hidden" name="route" value="<?= e($suggested) ?>">
                <input type="hidden" name="back" value="inbox">
                <button class="pill pill-live" type="submit" title="<?= e($why) ?> · confidence <?= e(number_format($conf, 2)) ?>" style="border:0;cursor:pointer;">
                  ✨ <?= e(strtoupper($suggested)) ?>
                </button>
              </form>
            <?php else: ?>
              <span class="caption">—</span>
            <?php endif; ?>
          </td>
          <td><?= e(date_pretty($r['created_at'])) ?></td>
          <td><a class="nav-link" href="<?= e(url('/admin/inquiries/' . $r['id'])) ?>">Open →</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
