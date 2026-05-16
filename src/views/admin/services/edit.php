<?php /** @var array $service */ ?>
<header style="margin-bottom:24px;">
  <a class="nav-link" href="<?= e(url('/admin/services')) ?>">← Services</a>
  <h1 class="ff-display" style="font-weight:700;font-size:28px;letter-spacing:-0.02em;margin:8px 0 0;">Edit · <?= e($service['name']) ?></h1>
</header>

<form method="post" action="<?= e(url('/admin/services/save')) ?>" class="admin-card" style="display:grid;grid-template-columns:2fr 1fr;gap:32px;">
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= e((string)$service['id']) ?>">
  <div>
    <div class="field"><input name="name" placeholder=" " value="<?= e($service['name']) ?>" required><label>Name</label></div>
    <div class="field"><textarea name="tagline" rows="2" placeholder=" "><?= e($service['tagline'] ?? '') ?></textarea><label>Tagline</label></div>
    <div class="field"><textarea name="overview" rows="6" placeholder=" "><?= e($service['overview'] ?? '') ?></textarea><label>Overview</label></div>
    <div class="field"><textarea name="who_for" rows="3" placeholder=" "><?= e($service['who_for'] ?? '') ?></textarea><label>Who it's for</label></div>
  </div>
  <aside style="display:flex;flex-direction:column;gap:24px;">
    <div class="field"><input name="timeline" placeholder=" " value="<?= e($service['timeline'] ?? '4–6 WEEKS') ?>"><label>Timeline label</label></div>
    <div class="field"><input name="tag" placeholder=" " value="<?= e($service['tag'] ?? 'CORE') ?>"><label>Tag</label></div>
    <button class="btn btn-primary btn-block" type="submit">Save</button>
  </aside>
</form>
