<?php /** @var ?array $project */ ?>
<header style="margin-bottom:24px;">
  <div class="eyebrow">// PROJECTS</div>
  <h1 class="ff-display" style="font-weight:700;font-size:28px;letter-spacing:-0.02em;margin:8px 0 0;"><?= $project ? 'Edit project' : 'New project' ?></h1>
</header>

<form method="post" action="<?= e(url('/admin/projects/save')) ?>" class="admin-card" style="display:grid;grid-template-columns:2fr 1fr;gap:32px;">
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= e((string)($project['id'] ?? '')) ?>">
  <div>
    <div class="field"><input name="title" placeholder=" " value="<?= e($project['title'] ?? '') ?>" required><label>Title</label></div>
    <div class="field"><input name="slug" placeholder=" " value="<?= e($project['slug'] ?? '') ?>"><label>Slug</label></div>
    <div class="field"><input name="client" placeholder=" " value="<?= e($project['client'] ?? '') ?>"><label>Client</label></div>
    <div class="field"><input name="category" placeholder=" " value="<?= e($project['category'] ?? '') ?>"><label>Category</label></div>
    <div class="field"><input name="tags" placeholder=" " value="<?= e($project['tags'] ?? '') ?>"><label>Tags</label></div>
    <div class="field"><textarea name="summary" rows="2" placeholder=" "><?= e($project['summary'] ?? '') ?></textarea><label>Summary</label></div>
    <div class="field"><textarea name="problem" rows="4" placeholder=" "><?= e($project['problem'] ?? '') ?></textarea><label>The Problem</label></div>
    <div class="field"><textarea name="solution" rows="4" placeholder=" "><?= e($project['solution'] ?? '') ?></textarea><label>The Solution</label></div>
    <div class="field"><textarea name="outcome" rows="3" placeholder=" "><?= e($project['outcome'] ?? '') ?></textarea><label>The Outcome</label></div>
  </div>
  <aside style="display:flex;flex-direction:column;gap:24px;">
    <div class="field"><input name="year" type="number" placeholder=" " value="<?= e((string)($project['year'] ?? date('Y'))) ?>"><label>Year</label></div>
    <div class="field" style="margin:0;">
      <select name="status_label" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
        <?php foreach (['live','production','case'] as $s): ?>
          <option value="<?= e($s) ?>" <?= ($project['status_label'] ?? '') === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="margin:0;">
      <select name="status" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
        <option value="draft"     <?= ($project['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="published" <?= ($project['status'] ?? '')      === 'published' ? 'selected' : '' ?>>Published</option>
      </select>
    </div>
    <label style="display:flex;align-items:center;gap:10px;font-size:14px;">
      <input type="checkbox" name="is_featured" value="1" <?= !empty($project['is_featured']) ? 'checked' : '' ?>> Featured on home
    </label>
    <button class="btn btn-primary btn-block" type="submit">Save</button>
    <a class="nav-link" href="<?= e(url('/admin/projects')) ?>">← Back</a>
  </aside>
</form>
