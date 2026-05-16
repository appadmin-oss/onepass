<?php /** @var string $bodyContent */ ?>
<!DOCTYPE html>
<html lang="<?= e(AFS_LOCALE) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Inline theme bootstrap — runs before paint to prevent FOUC.
       Default is DARK (the studio's primary look), so first-visit
       users see the reference design system as designed. We still
       respect any saved preference and the OS toggle. -->
  <script>
    (function () {
      try {
        var stored = localStorage.getItem('afs_theme');
        var prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
        // Dark unless: user explicitly chose 'light' previously, OR
        // they have no stored choice AND OS prefers light.
        var theme = stored ? stored : (prefersLight ? 'light' : 'dark');
        if (theme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
      } catch (e) {
        // Fail-safe: still set dark so the floating nav doesn't render
        // against a white background.
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
  <link href="https://api.fontshare.com/v2/css?f[]=garet@400,500,600,700,800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Newsreader:ital,opsz,wght@0,6..72,300..600;1,6..72,300..600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset_v('css/custom.css')) ?>">
  <?php require AFS_ROOT . '/src/views/partials/meta.php'; ?>
  <?php require AFS_ROOT . '/src/views/partials/jsonld.php'; ?>
  <?php require AFS_ROOT . '/src/views/partials/analytics.php'; ?>
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23C0392B'%3E%3Cpath d='M12 2 L22 12 L12 22 L2 12 Z M12 7 L17 12 L12 17 L7 12 Z' fill-rule='evenodd'/%3E%3C/svg%3E">
  <?php if (!empty($needsSvgJs)): ?>
    <script src="https://cdn.jsdelivr.net/npm/@svgdotjs/svg.js@3.2.0/dist/svg.min.js" defer></script>
  <?php endif; ?>
  <?php if (!empty($needsGraphicsJs)): ?>
    <script src="https://cdn.jsdelivr.net/npm/graphicsjs@1.0.4/dist/graphics.min.js" defer></script>
  <?php endif; ?>
  <!-- Library upgrades: Fuse for fuzzy Cmd+K, Canvas-confetti for success bursts -->
  <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js" defer></script>
</head>
<body class="has-afro-nav <?= e($bodyClass ?? '') ?>">
  <a class="skip-link" href="#main">Skip to content</a>
  <?php require AFS_ROOT . '/src/views/partials/nav.php'; ?>
  <main id="main" class="container" style="padding-bottom:112px;">
    <?= $bodyContent ?>
  </main>
  <!-- Reading progress (animated only on long-form pages) -->
  <div class="reading-progress" data-reading-progress aria-hidden="true">
    <div class="reading-progress__bar"></div>
  </div>

  <!-- Back-to-top -->
  <button type="button" class="back-to-top" data-back-to-top aria-label="Back to top">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
      <path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>

  <!-- Command palette (Cmd/Ctrl+K) -->
  <?php require AFS_ROOT . '/src/views/partials/cmdk.php'; ?>

  <!-- Apply modal — fetches /academy/apply inline (SEO-friendly).
       The form lives at a real indexable URL; the modal is progressive
       enhancement that loads its #main content via fetch + History API
       so the address bar reads /academy/apply when open. -->
  <div class="apply-modal" data-apply-modal aria-hidden="true" role="dialog" aria-modal="true" aria-label="Apply to Afrotech Academy">
    <div class="apply-modal__shell">
      <div class="apply-modal__bar">
        <span class="ff-mono" style="font-size:11px;letter-spacing:0.22em;text-transform:uppercase;color:var(--text-mute);">// AFROTECH ACADEMY · APPLICATION</span>
        <div style="display:flex;gap:10px;align-items:center;">
          <a class="nav-link" style="font-size:12px;" href="<?= e(url('/academy/apply')) ?>" data-apply-fullpage>Open full page →</a>
          <button class="apply-modal__close" type="button" data-apply-close aria-label="Close">×</button>
        </div>
      </div>
      <div class="apply-modal__body" data-apply-body>
        <!-- Filled by fetch on open. Empty so search engines don't index this. -->
      </div>
    </div>
  </div>

  <?php require AFS_ROOT . '/src/views/partials/footer.php'; ?>
  <script src="<?= e(asset_v('js/app.js')) ?>" defer></script>
  <script src="<?= e(asset_v('js/form.js')) ?>" defer></script>
  <script src="<?= e(asset_v('js/smart-form.js')) ?>" defer></script>
  <script src="<?= e(asset_v('js/ai-action.js')) ?>" defer></script>
  <script src="<?= e(asset_v('js/tracker.js')) ?>" defer></script>
  <script>
    // Register the static-asset service worker. We only register on HTTPS
    // surfaces with the SW API present — both required by the spec. Failure
    // is silent: the site works fine without it.
    if ('serviceWorker' in navigator && location.protocol === 'https:') {
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('/service-worker.js').catch(function () {});
      });
    }
  </script>
  <script src="<?= e(asset_v('js/lightbox.js')) ?>" defer></script>
  <?php if (!empty($needsSvgJs)): ?>
    <script src="<?= e(asset_v('js/svg-hero.js')) ?>" defer></script>
  <?php endif; ?>
  <?php if (!empty($needsGraphicsJs)): ?>
    <script src="<?= e(asset_v('js/svg-academy.js')) ?>" defer></script>
  <?php endif; ?>
</body>
</html>
