<?php /** @var array $services */ ?>
<header style="margin-bottom:24px;">
  <div class="eyebrow">// SERVICES</div>
  <h1 class="ff-display" style="font-weight:700;font-size:32px;letter-spacing:-0.02em;margin:8px 0 0;">Service catalogue</h1>
  <p class="caption" style="margin:6px 0 0;">Edit copy and timelines. Service rows are seeded — slugs are fixed.</p>
</header>

<section class="admin-card">
  <?php $ok = flash_pop('admin_ok'); if ($ok): ?>
    <div style="padding:10px 14px;border-radius:8px;background:rgba(31,138,91,0.1);color:#1F8A5B;margin-bottom:16px;font-size:13px;"><?= e($ok) ?></div>
  <?php endif; ?>
  <table class="admin-table">
    <thead><tr><th>Name</th><th>Slug</th><th>Tag</th><th>Timeline</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($services as $s): ?>
        <tr>
          <td><strong><?= e($s['name']) ?></strong><br><span class="caption"><?= e(excerpt($s['tagline'] ?? '', 12)) ?></span></td>
          <td><?= e($s['slug']) ?></td>
          <td><?= e($s['tag'] ?? 'CORE') ?></td>
          <td><?= e($s['timeline'] ?? '') ?></td>
          <td>
            <?php if (!empty($s['id'])): ?>
              <a class="nav-link" href="<?= e(url('/admin/services/' . $s['id'] . '/edit')) ?>">Edit →</a>
            <?php else: ?>
              <span class="caption">Seed only</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
