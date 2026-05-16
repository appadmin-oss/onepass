<?php
/** @var string $section
 *  @var array  $rows
 *  @var bool   $lmsEnabled
 */
$labels = ['courses' => 'Courses', 'instructors' => 'Instructors', 'certifications' => 'Certifications', 'enrollments' => 'Enrolments'];
?>
<header style="margin-bottom:24px;">
  <div class="eyebrow">// ACADEMY · <?= e(strtoupper($labels[$section] ?? $section)) ?></div>
  <h1 class="ff-display" style="font-weight:700;font-size:32px;letter-spacing:-0.02em;margin:8px 0 4px;"><?= e($labels[$section]) ?></h1>
  <?php if ($lmsEnabled && $section === 'courses'): ?>
    <p class="caption" style="margin:0;">Moodle is wired — the live catalog is read from <code><?= e(LMS_BASE_URL) ?></code>. Local rows below are used as a fallback or for surfaces not yet in the LMS.</p>
  <?php endif; ?>
</header>

<nav style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;">
  <?php foreach ($labels as $k => $l): ?>
    <a class="btn <?= $section === $k ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="<?= e(url('/admin/academy/' . $k)) ?>"><?= e($l) ?></a>
  <?php endforeach; ?>
</nav>

<?php $ok = flash_pop('admin_ok'); if ($ok): ?>
  <div style="padding:10px 14px;border-radius:8px;background:rgba(31,138,91,0.1);color:#1F8A5B;margin-bottom:16px;font-size:13px;"><?= e($ok) ?></div>
<?php endif; ?>

<section class="admin-card" style="margin-bottom:24px;">
  <?php if (empty($rows)): ?>
    <p class="caption">Nothing here yet.</p>
  <?php else: ?>
    <table class="admin-table">
      <?php if ($section === 'courses'): ?>
        <thead><tr><th>Title</th><th>Level</th><th>Weeks</th><th>Price</th><th>Status</th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?>
          <tr><td><strong><?= e($r['title']) ?></strong><br><span class="caption"><?= e($r['slug']) ?></span></td>
              <td><?= e($r['level'] ?? '') ?></td><td><?= e((string)($r['weeks'] ?? '')) ?></td>
              <td>₦ <?= number_format((int)($r['price_naira'] ?? 0)) ?></td>
              <td><span class="pill"><?= e(strtoupper($r['status'] ?? '')) ?></span></td></tr>
        <?php endforeach; ?></tbody>
      <?php elseif ($section === 'instructors'): ?>
        <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Skills</th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?>
          <tr><td><strong><?= e($r['name']) ?></strong></td>
              <td><?= e($r['role'] ?? '') ?></td>
              <td><span class="pill <?= ($r['status_pill'] ?? 'live') === 'live' ? 'pill-live' : 'pill-prod' ?>"><?= e($r['status_label'] ?? '') ?></span></td>
              <td><span class="caption"><?= e($r['skills'] ?? '') ?></span></td></tr>
        <?php endforeach; ?></tbody>
      <?php elseif ($section === 'certifications'): ?>
        <thead><tr><th>Title</th><th>Summary</th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?>
          <tr><td><strong><?= e($r['title']) ?></strong></td>
              <td><span class="caption"><?= e($r['summary'] ?? '') ?></span></td></tr>
        <?php endforeach; ?></tbody>
      <?php elseif ($section === 'enrollments'): ?>
        <thead><tr><th>Name</th><th>Email</th><th>Course</th><th>Status</th><th>Received</th><th></th></tr></thead>
        <tbody><?php foreach ($rows as $r): ?>
          <tr><td><strong><?= e($r['name']) ?></strong></td>
              <td><span class="caption"><?= e($r['email']) ?></span></td>
              <td><?= e($r['course_title'] ?? '') ?></td>
              <td><span class="pill"><?= e(strtoupper($r['status'])) ?></span></td>
              <td><?= e(date_pretty($r['created_at'])) ?></td>
              <td>
                <form method="post" action="<?= e(url('/admin/academy/save')) ?>" style="display:inline;">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="section" value="enrollments">
                  <input type="hidden" name="id" value="<?= e((string)$r['id']) ?>">
                  <select name="status" onchange="this.form.submit()" style="font-size:12px;border:1px solid var(--hairline);padding:4px 8px;border-radius:6px;">
                    <?php foreach (['pending','confirmed','cancelled'] as $s): ?>
                      <option value="<?= e($s) ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td></tr>
        <?php endforeach; ?></tbody>
      <?php endif; ?>
    </table>
  <?php endif; ?>
</section>

<?php if (in_array($section, ['courses', 'instructors', 'certifications'], true)): ?>
<details class="admin-card" style="max-width:760px;">
  <summary style="cursor:pointer;font-family:Garet,sans-serif;font-weight:600;font-size:18px;">+ Add new <?= e(rtrim($labels[$section], 's')) ?></summary>
  <form method="post" action="<?= e(url('/admin/academy/save')) ?>" style="margin-top:24px;display:flex;flex-direction:column;gap:0;">
    <?= Csrf::field() ?>
    <input type="hidden" name="section" value="<?= e($section) ?>">
    <?php if ($section === 'courses'): ?>
      <div class="field"><input name="title" placeholder=" " required><label>Title</label></div>
      <div class="field"><input name="slug" placeholder=" "><label>Slug (auto)</label></div>
      <div class="field"><input name="level" placeholder=" " value="Foundations"><label>Level</label></div>
      <div class="field"><input name="weeks" type="number" placeholder=" " value="6"><label>Weeks</label></div>
      <div class="field"><input name="instructor" placeholder=" "><label>Instructor</label></div>
      <div class="field"><input name="price_naira" type="number" placeholder=" "><label>Price (NGN)</label></div>
      <div class="field"><textarea name="summary" rows="2" placeholder=" "></textarea><label>Summary</label></div>
      <div class="field"><textarea name="body" rows="6" placeholder=" "></textarea><label>Body (HTML)</label></div>
      <div class="field"><select name="status" style="width:100%;padding:14px 0 10px;border:0;border-bottom:1px solid var(--hairline);background:transparent;font-size:15px;"><option value="draft">Draft</option><option value="published">Published</option></select></div>
    <?php elseif ($section === 'instructors'): ?>
      <div class="field"><input name="name" placeholder=" " required><label>Name</label></div>
      <div class="field"><input name="role" placeholder=" "><label>Role</label></div>
      <div class="field"><textarea name="bio" rows="3" placeholder=" "></textarea><label>Bio</label></div>
      <div class="field"><input name="skills" placeholder=" "><label>Skills (comma-separated)</label></div>
      <div class="field"><input name="sort" type="number" placeholder=" " value="0"><label>Sort order</label></div>
    <?php elseif ($section === 'certifications'): ?>
      <div class="field"><input name="title" placeholder=" " required><label>Title</label></div>
      <div class="field"><textarea name="summary" rows="3" placeholder=" "></textarea><label>Summary</label></div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary" style="align-self:flex-start;margin-top:12px;">Add</button>
  </form>
</details>
<?php endif; ?>
