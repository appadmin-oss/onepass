<?php
/** @var array $post
 *  @var array $related
 */
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<!-- Prism for code blocks in field notes -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css">
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js" defer></script>

<?php
$pub = $post['published_at'] ?? $post['created_at'] ?? date('c');
?>

<article data-reveal style="max-width:760px;margin:0 auto;padding:32px 0 64px;">
  <div class="ff-mono" style="font-size:11px;letter-spacing:0.14em;color:rgba(10,10,10,0.55);text-transform:uppercase;">
    <?= e($post['category'] ?? 'Editorial') ?> · <?= e(date_pretty($pub)) ?> · <?= e(read_time($post['body'] ?? '')) ?>
  </div>
  <h1 class="h-display-2" style="margin:18px 0 24px;max-width:24ch;line-height:1.05;">
    <?= e($post['title']) ?>
  </h1>
  <p class="body-l" style="color:rgba(10,10,10,0.7);margin:0 0 32px;">
    <?= e($post['excerpt'] ?? '') ?>
  </p>
  <div class="caption">By <?= e($post['author'] ?? 'Afrostrength Studio') ?></div>

  <div class="placeholder" style="aspect-ratio:21/9;margin:32px 0;border-radius:14px;">
    // <?= e(strtoupper($post['title'])) ?>
  </div>

  <div class="prose">
    <?= $post['body'] ?? '' ?>
  </div>

  <?php partial('share-bar', ['share' => ['title' => $post['title'], 'url' => url('/blog/' . $post['slug'])]]); ?>
</article>

<?php if (!empty($related)): ?>
<section data-reveal style="margin-top:64px;border-top:1px solid var(--hairline);padding-top:48px;">
  <div class="eyebrow">MORE FIELD NOTES</div>
  <div data-stagger="80" class="grid-cols-3" style="margin-top:24px;">
    <?php foreach ($related as $p) partial('blog-card', ['post' => $p]); ?>
  </div>
</section>
<?php endif; ?>

<?php partial('cta-final'); ?>
