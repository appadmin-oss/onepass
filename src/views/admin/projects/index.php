<?php /** @var array $projects */ ?>
<header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
  <div>
    <div class="eyebrow">// PROJECTS</div>
    <h1 class="ff-display" style="font-weight:700;font-size:32px;letter-spacing:-0.02em;margin:8px 0 0;">All projects</h1>
  </div>
  <a href="<?= e(url('/admin/projects/new')) ?>" class="btn btn-primary btn-sm">New project</a>
</header>

<section class="admin-card">
  <?php $ok = flash_pop('admin_ok'); if ($ok): ?>
    <div style="padding:10px 14px;border-radius:8px;background:rgba(31,138,91,0.1);color:#1F8A5B;margin-bottom:16px;font-size:13px;"><?= e($ok) ?></div>
  <?php endif; ?>
  <table class="admin-table">
    <thead><tr><th>Title</th><th>Client</th><th>Year</th><th>Status</th><th>Featured</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
        <tr>
          <td><strong><?= e($p['title']) ?></strong><br><span class="caption"><?= e($p['slug']) ?></span></td>
          <td><?= e($p['client'] ?? '—') ?></td>
          <td><?= e((string)$p['year']) ?></td>
          <td><span class="pill"><?= e(strtoupper($p['status'] ?? 'draft')) ?></span></td>
          <td><?= !empty($p['is_featured']) ? '★' : '' ?></td>
          <td style="display:flex;gap:8px;justify-content:flex-end;">
            <a class="nav-link" href="<?= e(url('/admin/projects/' . $p['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/projects/' . $p['id'] . '/delete')) ?>" onsubmit="return confirm('Delete?')" style="display:inline;">
              <?= Csrf::field() ?>
              <button class="nav-link" style="background:none;border:0;color:var(--crimson);">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
