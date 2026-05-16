<?php /** Auth layout — no site nav. Two-column on desktop, stacked on mobile. */ ?>
<!DOCTYPE html>
<html lang="<?= e(AFS_LOCALE) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>
    (function () {
      try {
        var stored = localStorage.getItem('afs_theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = stored || (prefersDark ? 'dark' : 'light');
        if (theme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
      } catch (e) {}
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
  <link href="https://api.fontshare.com/v2/css?f[]=garet@400,500,600,700,800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset_v('css/custom.css')) ?>">
  <?php require AFS_ROOT . '/src/views/partials/meta.php'; ?>
  <?php require AFS_ROOT . '/src/views/partials/analytics.php'; ?>
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23C0392B'%3E%3Cpath d='M12 2 L22 12 L12 22 L2 12 Z M12 7 L17 12 L12 17 L7 12 Z' fill-rule='evenodd'/%3E%3C/svg%3E">
  <?php if (!empty($noindex)): ?><meta name="robots" content="noindex,nofollow"><?php endif; ?>
</head>
<?php require_once AFS_ROOT . '/src/views/partials/icons.php'; ?>
<body class="auth-body <?= e($bodyClass ?? '') ?>">
  <a class="skip-link" href="#auth-main">Skip to sign-in form</a>

  <div class="auth-shell">
    <!-- Promo column (decorative; carousel is built in auth.js) -->
    <aside class="auth-aside" aria-label="Afrostrength highlights">
      <div class="auth-aside__head">
        <a class="auth-brand" href="<?= e(url('/')) ?>" aria-label="Afrostrength home">
          <?= icon_logo(22, 'white') ?>
          <span>Afrostrength</span>
        </a>
        <div class="auth-aside__eyebrow">Building brands · strengthening legacies</div>
      </div>

      <?php
        $slides = [
          [
            'tag'   => 'Afrotech Academy',
            'title' => 'Operators teaching operators.',
            'body'  => 'Nine certification tracks, taught by people shipping in production today. Live sessions on Jitsi, hands-on work in Moodle.',
            'meta'  => 'Next cohort opens in 14 days · remote-friendly',
            'mark'  => 'method',
          ],
          [
            'tag'   => 'Case study',
            'title' => 'Eight rebrands shipped in 2025.',
            'body'  => 'From a Lagos luminaire studio to a fintech in Nairobi. Field Notes 04 unpacks the through-line.',
            'meta'  => 'Read on the blog after sign-in',
            'mark'  => 'editorial',
          ],
          [
            'tag'   => 'Free tools',
            'title' => 'Build with us, even before you sign up.',
            'body'  => 'Eight free tools: short links, brand-coloured QR, palette extractor, salary calculator, career-path quiz.',
            'meta'  => 'No account needed — open /tools',
            'mark'  => 'spark',
          ],
          [
            'tag'   => 'Community',
            'title' => 'Two studios in Lagos.',
            'body'  => 'CACENTRE Egbeda and CACENTRE Ayobo. Drop in for office hours every Thursday afternoon.',
            'meta'  => 'WAT · Mon–Fri 09:00–18:00',
            'mark'  => 'pin',
          ],
        ];
        $slideCount = count($slides);
      ?>

      <div class="embla" data-embla aria-roledescription="carousel" aria-label="Afrostrength highlights">
        <div class="embla__viewport">
          <div class="embla__container">
            <?php foreach ($slides as $i => $slide): ?>
              <div class="embla__slide" role="group" aria-roledescription="slide" aria-label="Slide <?= $i+1 ?> of <?= $slideCount ?>">
                <div class="auth-slide">
                  <div class="auth-slide__mark" aria-hidden="true"><?= icon($slide['mark'], 18) ?></div>
                  <div class="auth-slide__counter" aria-hidden="true">
                    <?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?>
                    <span> / </span>
                    <?= str_pad((string)$slideCount, 2, '0', STR_PAD_LEFT) ?>
                  </div>
                  <div class="auth-slide__tag"><?= e($slide['tag']) ?></div>
                  <h2 class="auth-slide__title"><?= e($slide['title']) ?></h2>
                  <p class="auth-slide__body"><?= e($slide['body']) ?></p>
                  <div class="auth-slide__meta"><?= e($slide['meta']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="embla__controls" role="group" aria-label="Carousel controls">
          <button class="embla__prev" type="button" aria-label="Previous slide">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <div class="embla__dots" data-embla-dots aria-hidden="true"></div>
          <button class="embla__next" type="button" aria-label="Next slide">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
              <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
      </div>

      <footer class="auth-aside__foot">
        <span>© <?= date('Y') ?> Afrostrength Limited</span>
        <span aria-hidden="true">·</span>
        <a href="<?= e(url('/legal/privacy')) ?>">Privacy</a>
        <span aria-hidden="true">·</span>
        <a href="<?= e(url('/legal/terms')) ?>">Terms</a>
      </footer>
    </aside>

    <!-- Form column -->
    <main id="auth-main" class="auth-main" role="main">
      <div class="auth-main__inner">
        <?= $bodyContent ?>
      </div>
    </main>
  </div>

  <script src="<?= e(asset_v('js/auth.js')) ?>" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/embla-carousel@8.3.0/embla-carousel.umd.js" defer></script>
</body>
</html>
