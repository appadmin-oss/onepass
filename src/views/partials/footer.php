<?php require_once AFS_ROOT . '/src/views/partials/icons.php'; ?>
<footer class="site-footer" role="contentinfo">
  <div class="site-footer__mantra">
    <h2 class="ff-display">Building brands, <em>strengthening</em> legacies.</h2>
    <div class="site-footer__mantra-side">
      <span class="eyebrow ff-mono">// LET&rsquo;S BUILD</span>
      <a class="btn btn-light" href="<?= e(url('/contact')) ?>">Start a conversation <?= icon_chev() ?></a>
    </div>
  </div>

  <div class="site-footer__inner">
    <div class="site-footer__col">
      <a href="<?= e(url('/')) ?>" class="site-footer__brand-tag" aria-label="Afrostrength home">
        <?= icon_logo(28, 'light') ?>
        <span class="ff-display">Afrostrength <em>Limited</em></span>
      </a>
      <p style="margin-top:14px;font-size:0.9rem;color:rgba(255,255,255,0.55);max-width:34ch;">
        Strategy, identity, media, and project execution for the brands shaping the next decade of African business.
      </p>
      <form class="site-footer__newsletter" method="post" action="<?= e(url('/api/newsletter')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
        <?= Security::honeypotField('website') ?>
        <input type="email" name="email" required placeholder="you@studio.com" aria-label="Email">
        <button type="submit">Subscribe</button>
      </form>
    </div>

    <div class="site-footer__col">
      <h5>Studio</h5>
      <ul>
        <li><a href="<?= e(url('/about')) ?>">About</a></li>
        <li><a href="<?= e(url('/services')) ?>">Services</a></li>
        <li><a href="<?= e(url('/projects')) ?>">Projects</a></li>
        <li><a href="<?= e(url('/testimonials')) ?>">Testimonials</a></li>
        <li><a href="<?= e(url('/blog')) ?>">Field notes</a></li>
        <li><a href="<?= e(url('/contact')) ?>">Contact</a></li>
      </ul>
    </div>

    <div class="site-footer__col">
      <h5>Academy</h5>
      <ul>
        <li><a href="<?= e(url('/academy')) ?>">Overview</a></li>
        <li><a href="<?= e(url('/academy/courses')) ?>">Courses</a></li>
        <li><a href="<?= e(url('/academy/live-sessions')) ?>">Live sessions</a></li>
        <li><a href="<?= e(url('/academy/certifications')) ?>">Certifications</a></li>
        <li><a href="<?= e(url('/academy/apply')) ?>">Apply</a></li>
        <li><a href="<?= e(url('/verify')) ?>">Verify a certificate</a></li>
      </ul>
    </div>

    <div class="site-footer__col">
      <h5>Resources</h5>
      <ul>
        <li><a href="<?= e(url('/tools')) ?>">Free tools</a></li>
        <li><a href="<?= e(url('/faq')) ?>">FAQ</a></li>
        <li><a href="<?= e(url('/opportunities')) ?>">Opportunities</a></li>
        <li><a href="<?= e(url('/status')) ?>">System status</a></li>
        <li><a href="<?= e(url('/legal/privacy')) ?>">Privacy</a></li>
        <li><a href="<?= e(url('/legal/terms')) ?>">Terms</a></li>
      </ul>
    </div>

    <div class="site-footer__col">
      <h5>Theme</h5>
      <div class="site-footer__locale" role="group" aria-label="Theme">
        <button type="button" data-theme-set="light">Light</button>
        <button type="button" data-theme-set="dark">Dark</button>
      </div>
      <h5 style="margin-top:24px;">Locale</h5>
      <div class="site-footer__locale" role="group" aria-label="Locale">
        <button type="button" class="is-active" aria-pressed="true">EN</button>
      </div>
    </div>
  </div>

  <div class="site-footer__legal">
    <span>&copy; <?= date('Y') ?> Afrostrength Limited. All rights reserved.</span>
    <span>
      <a href="<?= e(url('/legal/privacy')) ?>">Privacy</a> ·
      <a href="<?= e(url('/legal/terms')) ?>">Terms</a> ·
      <a href="<?= e(url('/legal/cookies')) ?>">Cookies</a> ·
      <a href="<?= e(url('/legal/acceptable-use')) ?>">Acceptable use</a> ·
      <a href="<?= e(url('/legal/imprint')) ?>">Imprint</a>
    </span>
  </div>
</footer>
