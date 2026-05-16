<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => 'API',
  'eyebrow' => 'Status API',
  'title'   => 'Read our status, <em>any way you want.</em>',
  'lead'    => 'Every state shown on /status is also available as JSON, RSS, Atom, an SVG badge, and a tiny embed widget. Use them for monitors, partner pages, dashboards, or README badges.',
]); ?>

<section data-reveal>
  <div class="status-api">

    <article class="status-api__card">
      <h2>Live state JSON</h2>
      <p><code>GET /status/health.json</code> — same shape the status page renders. <code>Cache-Control: max-age=15</code>.</p>
<pre><code class="ff-mono">curl -s <?= e(rtrim(AFS_URL, '/')) ?>/status/health.json | jq</code></pre>
      <p class="caption">Add <code>?deep=1</code> to make the server actually round-trip the AI providers (slower, ~1–3 s).</p>
    </article>

    <article class="status-api__card">
      <h2>RSS / Atom incident feeds</h2>
      <p><code>GET /status/feed.rss</code> · <code>GET /status/feed.atom</code></p>
      <p>Last 20 incidents (active + resolved), oldest at the bottom. Subscribe in your reader, or hook into a webhook that forwards to Slack / Discord.</p>
    </article>

    <article class="status-api__card">
      <h2>SVG status badges</h2>
      <p>Three variants, all <code>Cache-Control: max-age=60</code>:</p>
      <ul>
        <li><code>GET /status/badge.svg</code> — classic pill (operational / degraded / outage)</li>
        <li><code>GET /status/badge-dark.svg</code> — white-on-ink for dark partner sites</li>
        <li><code>GET /status/badge-square.svg</code> — 64 × 64 for compact dashboards</li>
      </ul>
<pre><code class="ff-mono">&lt;a href="<?= e(rtrim(AFS_URL, '/')) ?>/status"&gt;
  &lt;img alt="Afrostrength status" src="<?= e(rtrim(AFS_URL, '/')) ?>/status/badge.svg"&gt;
&lt;/a&gt;</code></pre>
    </article>

    <article class="status-api__card">
      <h2>Embed widget</h2>
      <p><code>GET /status/embed</code> — minimal 320 × 60 card. Inline CSS only; no JS; under 4 kB.</p>
<pre><code class="ff-mono">&lt;iframe src="<?= e(rtrim(AFS_URL, '/')) ?>/status/embed"
        width="320" height="60" loading="lazy"
        title="Afrostrength status" style="border:0"&gt;&lt;/iframe&gt;</code></pre>
      <p class="caption">Click on the card opens <code>/status</code> in the parent window (<code>target="_top"</code>).</p>
    </article>

    <article class="status-api__card">
      <h2>Per-incident permalinks</h2>
      <p><code>GET /status/incidents/{public_id}</code> — stable URL for sharing in apology emails or X posts. Public-id stays consistent across re-tools.</p>
    </article>

  </div>
</section>
