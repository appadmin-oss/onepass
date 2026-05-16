<?php
/** Reusable social-share bar.
 *  Args ($share):
 *    title — page/post title
 *    url   — canonical url to share (defaults to current canonical)
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
$share = $share ?? [];
$title = $share['title'] ?? AFS_NAME;
$url   = $share['url']   ?? url(current_path());
$titleEnc = rawurlencode($title);
$urlEnc   = rawurlencode($url);
?>
<div class="share-bar" data-share-bar>
  <span class="share-bar__label">// SHARE</span>
  <a href="https://twitter.com/intent/tweet?text=<?= e($titleEnc) ?>&url=<?= e($urlEnc) ?>" target="_blank" rel="noopener" aria-label="Share on X"><?= icon('x', 14) ?></a>
  <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= e($urlEnc) ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><?= icon('linkedin', 14) ?></a>
  <a href="https://www.facebook.com/sharer/sharer.php?u=<?= e($urlEnc) ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><?= icon('instagram', 14) ?></a>
  <a href="mailto:?subject=<?= e($titleEnc) ?>&body=<?= e($urlEnc) ?>" aria-label="Share by email"><?= icon('mail', 14) ?></a>
  <button type="button" data-share-copy data-share-url="<?= e($url) ?>" aria-label="Copy link">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
      <rect x="9" y="9" width="13" height="13" rx="2"/>
      <path d="M5 15V5a2 2 0 0 1 2-2h10"/>
    </svg>
  </button>
  <span class="share-bar__copied">Copied</span>
</div>
