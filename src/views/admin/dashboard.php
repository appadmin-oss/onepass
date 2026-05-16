<?php
/** @var array $inquiries
 *  @var array $recent
 *  @var int   $postCount
 *  @var int   $projectCount
 *  @var int   $courseCount
 */
?>
<header style="margin-bottom:24px;">
  <div class="eyebrow">// DASHBOARD</div>
  <h1 class="ff-display" style="font-weight:700;font-size:36px;letter-spacing:-0.02em;margin:8px 0 4px;">Welcome back.</h1>
  <p class="caption" style="margin:0;">A quick read on the studio inbox and content state.</p>
</header>

<section style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
  <?php
  $stats = [
      ['n' => $inquiries['new']  ?? 0,  'l' => 'New inquiries',  'href' => '/admin/inquiries', 'accent' => true],
      ['n' => $inquiries['open'] ?? 0,  'l' => 'In progress',    'href' => '/admin/inquiries'],
      ['n' => $postCount,               'l' => 'Field notes',    'href' => '/admin/posts'],
      ['n' => $projectCount,            'l' => 'Projects',       'href' => '/admin/projects'],
  ];
  foreach ($stats as $s): ?>
    <a class="admin-card admin-stat" href="<?= e(url($s['href'])) ?>" style="text-decoration:none;color:inherit;<?= !empty($s['accent']) ? 'border-color:var(--crimson);' : '' ?>">
      <div class="admin-stat__num" style="<?= !empty($s['accent']) ? 'color:var(--crimson);' : '' ?>"><?= e((string)$s['n']) ?></div>
      <div class="admin-stat__label"><?= e($s['l']) ?></div>
    </a>
  <?php endforeach; ?>
</section>

<section class="admin-card" style="margin-bottom:24px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div class="eyebrow">// RECENT INQUIRIES</div>
    <a class="nav-link" href="<?= e(url('/admin/inquiries')) ?>">Open inbox →</a>
  </div>
  <?php if (empty($recent)): ?>
    <p class="caption">No inquiries yet — the inbox is empty.</p>
  <?php else: ?>
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Kind</th><th>Service</th><th>Received</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
          <tr>
            <td><strong><?= e($r['name']) ?></strong><br><span class="caption"><?= e($r['email']) ?></span></td>
            <td><?= e($r['kind']) ?></td>
            <td><?= e($r['service'] ?? '—') ?></td>
            <td><?= e(date_pretty($r['created_at'])) ?></td>
            <td><a href="<?= e(url('/admin/inquiries/' . $r['id'])) ?>" class="nav-link" style="font-size:13px;">View →</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<section style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div class="admin-card">
    <div class="eyebrow">// LMS STATUS</div>
    <p class="ff-display" style="font-weight:600;font-size:18px;margin:12px 0 0;">
      <?php if (defined('LMS_ENABLED') && LMS_ENABLED): ?>
        ✓ Moodle wired · <?= e(LMS_BASE_URL) ?>
      <?php else: ?>
        ○ LMS not configured. Set <code>LMS_*</code> vars in <code>config/app.php</code>.
      <?php endif; ?>
    </p>
  </div>
  <div class="admin-card">
    <div class="eyebrow">// LIVE STAGE (JaaS)</div>
    <p class="ff-display" style="font-weight:600;font-size:18px;margin:12px 0 0;">
      <?php if (defined('JAAS_ENABLED') && JAAS_ENABLED): ?>
        ✓ Jitsi-as-a-Service active · tenant <?= e(substr(JAAS_APP_ID, 0, 8)) ?>…
      <?php else: ?>
        ○ JaaS not configured. Set <code>JAAS_*</code> vars + drop the private key into <code>storage/</code>.
      <?php endif; ?>
    </p>
  </div>
</section>
