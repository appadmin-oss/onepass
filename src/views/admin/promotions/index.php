<?php /** @var array $rows */ ?>
<header style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px;gap:16px;flex-wrap:wrap;">
  <div>
    <div class="eyebrow">// PROMOTIONS</div>
    <h1 class="ff-display" style="font-weight:700;font-size:32px;letter-spacing:-0.02em;margin:8px 0 0;">All promotions</h1>
    <p class="caption" style="margin:6px 0 0;">Banners · cohorts · services · scholarships · interstitials · hackathons. Used everywhere a promotion surfaces on the site.</p>
  </div>
  <a href="<?= e(url('/admin/promotions/new')) ?>" class="btn btn-primary btn-sm">+ New promotion</a>
</header>

<?php $ok = flash_pop('admin_ok'); if ($ok): ?>
  <div style="padding:10px 14px;border-radius:8px;background:rgba(31,138,91,0.1);color:#1F8A5B;margin-bottom:16px;font-size:13px;"><?= e($ok) ?></div>
<?php endif; ?>

<section class="admin-card">
  <table class="admin-table">
    <thead><tr><th></th><th>Title</th><th>Kind</th><th>Placements</th><th>Window</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="7"><span class="caption">No promotions yet. <a class="nav-link" href="<?= e(url('/admin/promotions/new')) ?>">Create the first one →</a></span></td></tr>
      <?php endif; foreach ($rows as $r): ?>
        <tr>
          <td style="width:64px;">
            <?php if (!empty($r['image_url'])): ?>
              <img src="<?= e($r['image_url']) ?>" alt="" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--hairline);">
            <?php else: ?>
              <div style="width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,#C0392B,#8B0000);"></div>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= e($r['title']) ?></strong>
            <?php if (!empty($r['subtitle'])): ?><br><span class="caption"><?= e(excerpt($r['subtitle'], 16)) ?></span><?php endif; ?>
          </td>
          <td><span class="pill"><?= e(strtoupper($r['kind'] ?? '')) ?></span></td>
          <td><span class="caption ff-mono" style="font-size:11px;letter-spacing:0.08em;"><?= e($r['placements'] ?? '') ?></span></td>
          <td>
            <span class="caption ff-mono" style="font-size:11px;">
              <?= !empty($r['starts_at']) ? e(substr($r['starts_at'], 0, 10)) : '—' ?>
              →
              <?= !empty($r['ends_at']) ? e(substr($r['ends_at'], 0, 10)) : '—' ?>
            </span>
          </td>
          <td>
            <span class="pill <?= ($r['status'] ?? 'draft') === 'active' ? 'pill-live' : (($r['status'] ?? '') === 'paused' ? 'pill-prod' : 'pill-case') ?>"><?= e(strtoupper($r['status'] ?? 'draft')) ?></span>
          </td>
          <td style="text-align:right;display:flex;gap:8px;justify-content:flex-end;">
            <a class="nav-link" href="<?= e(url('/admin/promotions/' . $r['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/promotions/' . $r['id'] . '/delete')) ?>" onsubmit="return confirm('Delete this promotion?')" style="display:inline;">
              <?= Csrf::field() ?>
              <button class="nav-link" style="background:none;border:0;color:var(--crimson);cursor:pointer;">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
