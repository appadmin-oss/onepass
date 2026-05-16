<?php
/** @var array $incident */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

$isResolved = !empty($incident['resolved_at']);
$severity   = (string)$incident['severity'];
$kind       = (string)($incident['kind'] ?? 'incident');
$startedAt  = strtotime((string)$incident['started_at']);
$resolvedAt = $isResolved ? strtotime((string)$incident['resolved_at']) : null;
$components = array_filter(array_map('trim', explode(',', (string)$incident['components_csv'])));
?>

<article class="incident-detail" data-reveal>

  <!-- Hero verdict band scoped to this incident -->
  <div class="status-hero" data-state="<?= e($severity === 'critical' ? 'down' : 'degraded') ?>">
    <div class="status-hero__inner">
      <div>
        <p class="status-hero__meta" style="margin-bottom:12px;">
          <span><?= e(strtoupper($kind === 'maintenance' ? 'Maintenance' : 'Incident')) ?></span>
          <span>·</span>
          <span><?= e(ucfirst($severity)) ?></span>
          <span>·</span>
          <?php if ($isResolved): ?>
            <span style="color: var(--state-up);">Resolved</span>
          <?php else: ?>
            <span style="color: var(--state-down);">Active</span>
          <?php endif; ?>
        </p>
        <h1 class="status-hero__verdict"><?= e($incident['title']) ?></h1>
      </div>
      <div class="status-hero__meta">
        <span>Started <time datetime="<?= e(date('c', $startedAt)) ?>"><?= e(date('j M Y · H:i T', $startedAt)) ?></time></span>
        <?php if ($resolvedAt): ?>
          <span>→ Resolved <time datetime="<?= e(date('c', $resolvedAt)) ?>"><?= e(date('H:i T', $resolvedAt)) ?></time></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($components): ?>
    <div class="incident__components" style="margin-bottom:24px;">
      <?php foreach ($components as $c): ?>
        <span class="incident__tag"><?= e(strtoupper($c)) ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($incident['body'])): ?>
    <section class="incident-detail__body">
      <h2 class="h3" style="margin:0 0 14px;">What's happening</h2>
      <div class="incident__text" data-md><?= e((string)$incident['body']) ?></div>
    </section>
  <?php endif; ?>

  <?php if ($isResolved && !empty($incident['postmortem'])): ?>
    <section class="incident-detail__post" style="margin-top:48px;">
      <h2 class="h3" style="margin:0 0 14px;">Postmortem</h2>
      <div class="incident__text" data-md><?= e((string)$incident['postmortem']) ?></div>
      <?php partial('ai-disclosure', ['note' => 'Operator-edited.']); ?>
    </section>
  <?php endif; ?>

  <footer class="incident-detail__foot" style="margin-top:48px;padding-top:18px;border-top:1px solid var(--hairline);">
    <a class="nav-link" href="<?= e(url('/status')) ?>">← Back to status</a>
  </footer>

</article>

<script src="https://cdn.jsdelivr.net/npm/marked@13.0.0/marked.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js" defer></script>
<script>
(function () {
  function render() {
    if (!window.marked || !window.DOMPurify) return;
    document.querySelectorAll('[data-md]').forEach(function (el) {
      if (el.dataset.mdRendered) return;
      el.dataset.mdRendered = '1';
      try {
        var html = window.marked.parse(el.textContent || '', { breaks: true, gfm: true });
        el.innerHTML = window.DOMPurify.sanitize(html, {
          ALLOWED_TAGS: ['p','a','ul','ol','li','strong','em','code','pre','br','blockquote','h2','h3','h4','img','hr'],
          ALLOWED_ATTR: ['href','title','src','alt'],
          ALLOW_DATA_ATTR: false,
        });
      } catch (_) {}
    });
  }
  var waited = 0;
  var t = setInterval(function () {
    if ((window.marked && window.DOMPurify) || waited >= 3000) { clearInterval(t); render(); }
    waited += 100;
  }, 100);
})();
</script>

<?php
// JSON-LD for search engines + a small OG card.
$ogTitle = $incident['title'] . ' — Afrostrength status';
$ogDesc  = ($isResolved ? 'Resolved ' : 'Active ') . $severity . ' incident.';
?>
<script type="application/ld+json"><?= json_encode([
  '@context' => 'https://schema.org',
  '@type'    => 'Event',
  'name'     => $incident['title'],
  'startDate'=> date('c', $startedAt),
  'endDate'  => $resolvedAt ? date('c', $resolvedAt) : null,
  'eventStatus' => $resolvedAt ? 'https://schema.org/EventScheduled' : 'https://schema.org/EventScheduled',
  'description' => $ogDesc,
  'url' => url('/status/incidents/' . (string)$incident['public_id']),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
