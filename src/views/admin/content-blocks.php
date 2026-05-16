<?php /** @var array $keys @var array $blocks */ ?>
<header style="margin-bottom:24px;">
  <div class="eyebrow">// CONTENT BLOCKS</div>
  <h1 class="ff-display" style="font-weight:700;font-size:32px;letter-spacing:-0.02em;margin:8px 0 0;">Homepage copy</h1>
  <p class="caption" style="margin:6px 0 0;">Wrap the punch phrase in <code>{{accent}}…{{/accent}}</code> to render it in italic crimson.</p>
</header>

<form method="post" action="<?= e(url('/admin/content/save')) ?>" class="admin-card" style="display:flex;flex-direction:column;gap:24px;max-width:800px;">
  <?= Csrf::field() ?>
  <?php $ok = flash_pop('admin_ok'); if ($ok): ?>
    <div style="padding:10px 14px;border-radius:8px;background:rgba(31,138,91,0.1);color:#1F8A5B;font-size:13px;"><?= e($ok) ?></div>
  <?php endif; ?>
  <?php foreach ($keys as $k => $label): $isLong = in_array($k, ['hero.sub','final.cta_title','hero.title'], true); ?>
    <div class="field">
      <?php if ($isLong): ?>
        <textarea name="<?= e($k) ?>" rows="3" placeholder=" "><?= e($blocks[$k] ?? '') ?></textarea>
      <?php else: ?>
        <input name="<?= e($k) ?>" placeholder=" " value="<?= e($blocks[$k] ?? '') ?>">
      <?php endif; ?>
      <label><?= e($label) ?></label>
    </div>
  <?php endforeach; ?>
  <button class="btn btn-primary" type="submit" style="align-self:flex-start;">Save changes</button>
</form>
