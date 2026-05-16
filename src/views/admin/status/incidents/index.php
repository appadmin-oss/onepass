<?php
/** @var array $incidents */
$ok  = flash_pop('admin_status_ok');
$err = flash_pop('admin_status_error');
?>

<header style="display:flex;align-items:end;justify-content:space-between;gap:24px;margin-bottom:24px;">
  <div>
    <p class="ff-mono" style="font-size:11px;letter-spacing:0.22em;text-transform:uppercase;color:var(--text-mute);margin:0 0 6px;">// STATUS · INCIDENTS</p>
    <h1 class="h-display-2" style="margin:0;">Operator incidents</h1>
    <p class="body-m" style="color:var(--text-mute);margin:8px 0 0;max-width:62ch;">
      Everything you write here shows up on the public <a class="nav-link" href="<?= e(url('/status')) ?>">/status</a> page within 30 seconds. Active incidents overlay the matching component pill; scheduled-maintenance rows surface as a distinct card.
    </p>
  </div>
  <a class="btn btn-primary" href="<?= e(url('/admin/status/incidents/new')) ?>">
    + New incident
  </a>
</header>

<?php if ($ok): ?>
  <div role="status" class="auth-form__alert" style="background:var(--state-up-bg);border-color:var(--state-up-border);color:var(--state-up);margin-bottom:18px;"><?= e($ok) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div role="alert" class="auth-form__alert" style="margin-bottom:18px;"><?= e($err) ?></div>
<?php endif; ?>

<?php if (!$incidents): ?>
  <div style="padding:32px;border:1px dashed var(--hairline);border-radius:14px;text-align:center;color:var(--text-mute);">
    No incidents yet. The public status page is reading "all systems normal".
    Authoring the first one tells visitors something has happened — keep titles plain and severities honest.
  </div>
<?php else: ?>
  <div style="border:1px solid var(--hairline);border-radius:14px;overflow:hidden;background:var(--surface);">
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead>
        <tr style="background:var(--bone-warm);">
          <th style="text-align:left;padding:12px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-mute);">Title</th>
          <th style="text-align:left;padding:12px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-mute);">Severity</th>
          <th style="text-align:left;padding:12px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-mute);">Components</th>
          <th style="text-align:left;padding:12px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-mute);">State</th>
          <th style="text-align:left;padding:12px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--text-mute);">Started</th>
          <th style="text-align:right;padding:12px 14px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($incidents as $i):
          $resolved = !empty($i['resolved_at']);
          $sev      = (string)$i['severity'];
          $sevColor = ['minor' => 'var(--state-degraded)', 'major' => 'var(--state-degraded)', 'critical' => 'var(--state-down)'][$sev] ?? 'var(--text-mute)';
          $parts    = array_filter(array_map('trim', explode(',', (string)$i['components_csv'])));
        ?>
          <tr style="border-top:1px solid var(--hairline);">
            <td style="padding:12px 14px;">
              <a href="<?= e(url('/admin/status/incidents/' . (int)$i['id'] . '/edit')) ?>" style="color:var(--ink);text-decoration:none;font-weight:600;">
                <?= e($i['title']) ?>
              </a>
              <?php if (!empty($i['public_id'])): ?>
                <div style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text-mute);margin-top:2px;">
                  <a class="nav-link" style="font-size:11px;color:var(--text-mute);" href="<?= e(url('/status/incidents/' . $i['public_id'])) ?>" target="_blank" rel="noopener">/<?= e($i['public_id']) ?> ↗</a>
                </div>
              <?php endif; ?>
            </td>
            <td style="padding:12px 14px;">
              <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:<?= $sevColor ?>;"><?= e($sev) ?></span>
              <?php if (($i['kind'] ?? '') === 'maintenance'): ?>
                <div style="font-family:'JetBrains Mono',monospace;font-size:10px;color:var(--state-scheduled);margin-top:2px;">Maintenance</div>
              <?php endif; ?>
            </td>
            <td style="padding:12px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text-mute);">
              <?= e(strtoupper(implode(' · ', $parts ?: ['—']))) ?>
            </td>
            <td style="padding:12px 14px;">
              <?php if ($resolved): ?>
                <span style="color:var(--state-up);font-weight:500;">Resolved</span>
              <?php else: ?>
                <span style="color:var(--state-down);font-weight:500;">Active</span>
              <?php endif; ?>
            </td>
            <td style="padding:12px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text-mute);font-variant-numeric:tabular-nums;">
              <?= e(date('j M · H:i', strtotime((string)$i['started_at']))) ?>
            </td>
            <td style="padding:12px 14px;text-align:right;">
              <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/status/incidents/' . (int)$i['id'] . '/edit')) ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
