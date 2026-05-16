<?php /** @var ?array $post */ ?>
<header style="margin-bottom:24px;">
  <div class="eyebrow">// FIELD NOTES</div>
  <h1 class="ff-display" style="font-weight:700;font-size:28px;letter-spacing:-0.02em;margin:8px 0 0;"><?= $post ? 'Edit post' : 'New post' ?></h1>
</header>

<form method="post" action="<?= e(url('/admin/posts/save')) ?>" class="admin-card" style="display:grid;grid-template-columns:2fr 1fr;gap:32px;">
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= e((string)($post['id'] ?? '')) ?>">

  <div>
    <div class="field"><input type="text" name="title" placeholder=" " value="<?= e($post['title'] ?? '') ?>" required><label>Title</label></div>
    <div class="field"><input type="text" name="slug" placeholder=" " value="<?= e($post['slug'] ?? '') ?>"><label>Slug (auto if blank)</label></div>
    <div class="field"><textarea name="excerpt" rows="2" placeholder=" "><?= e($post['excerpt'] ?? '') ?></textarea><label>Excerpt</label></div>
    <div class="field"><textarea name="body" rows="14" placeholder=" "><?= e($post['body'] ?? '') ?></textarea><label>Body (HTML allowed)</label></div>
  </div>

  <aside style="display:flex;flex-direction:column;gap:24px;">
    <div class="field"><input type="text" name="category" placeholder=" " value="<?= e($post['category'] ?? 'Editorial') ?>"><label>Category</label></div>
    <div class="field"><input type="text" name="author" placeholder=" " value="<?= e($post['author'] ?? 'Afrostrength Studio') ?>"><label>Author</label></div>
    <div class="field" style="margin:0;">
      <select name="status" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;">
        <option value="draft"     <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="published" <?= ($post['status'] ?? '')      === 'published' ? 'selected' : '' ?>>Published</option>
      </select>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Save</button>
    <a href="<?= e(url('/admin/posts')) ?>" class="nav-link">← Back</a>
  </aside>
</form>
