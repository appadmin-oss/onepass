<?php /** @var string $bodyContent */ ?>
<!DOCTYPE html>
<html lang="<?= e(AFS_LOCALE) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>(function(){try{var s=localStorage.getItem('afs_theme');var p=window.matchMedia&&matchMedia('(prefers-color-scheme: dark)').matches;if((s||(p?'dark':'light'))==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
  <link href="https://api.fontshare.com/v2/css?f[]=garet@400,500,600,700,800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset_v('css/custom.css')) ?>">
  <?php require AFS_ROOT . '/src/views/partials/meta.php'; ?>
  <?php require AFS_ROOT . '/src/views/partials/jsonld.php'; ?>
  <?php require AFS_ROOT . '/src/views/partials/analytics.php'; ?>
  <?php if (!empty($needsSvgJs)): ?>
    <script src="https://cdn.jsdelivr.net/npm/@svgdotjs/svg.js@3.2.0/dist/svg.min.js" defer></script>
  <?php endif; ?>
  <?php if (!empty($needsGraphicsJs)): ?>
    <script src="https://cdn.jsdelivr.net/npm/graphicsjs@1.0.4/dist/graphics.min.js" defer></script>
  <?php endif; ?>
</head>
<?php $isModal = !empty($_GET['modal']); ?>
<body class="<?= e($bodyClass ?? '') ?><?= $isModal ? ' is-modal' : '' ?>">
  <?php if (!$isModal) require AFS_ROOT . '/src/views/partials/nav-academy.php'; ?>
  <main id="main" class="container" style="<?= $isModal ? 'padding-top:24px;padding-bottom:24px;' : 'padding-top:64px;padding-bottom:112px;' ?>">
    <?= $bodyContent ?>
  </main>
  <?php if (!$isModal) require AFS_ROOT . '/src/views/partials/footer.php'; ?>
  <script src="<?= e(asset_v('js/app.js')) ?>" defer></script>
  <script src="<?= e(asset_v('js/form.js')) ?>" defer></script>
  <?php if (!empty($needsSvgJs)): ?>
    <script src="<?= e(asset_v('js/svg-hero.js')) ?>" defer></script>
  <?php endif; ?>
  <?php if (!empty($needsGraphicsJs)): ?>
    <script src="<?= e(asset_v('js/svg-academy.js')) ?>" defer></script>
  <?php endif; ?>
</body>
</html>
