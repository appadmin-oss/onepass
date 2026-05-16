<?php
/**
 * @var string $code
 * @var string $target
 * @var array  $promos
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
$targetHost = parse_url($target, PHP_URL_HOST) ?: $target;
?>
<!-- Hard-redirect fallback for users with JS disabled / bots that respect meta-refresh. -->
<meta http-equiv="refresh" content="6;url=<?= e($target) ?>">

<section class="redirect-stage" data-redirect-stage data-target="<?= e($target) ?>">
  <div class="redirect-stage__bar">
    <div class="redirect-stage__bar-fill" data-redirect-fill></div>
  </div>

  <header style="text-align:center;margin:32px auto 16px;max-width:600px;">
    <div class="ff-mono" style="font-size:11px;letter-spacing:0.22em;color:var(--crimson);text-transform:uppercase;">// SHORT LINK · <?= e($code) ?></div>
    <h1 class="h-display-2" style="margin:14px 0 12px;font-size:clamp(28px, 4vw, 40px);">
      Sending you to <em class="grad-text"><?= e($targetHost) ?></em>
    </h1>
    <p class="caption" style="margin:0;">
      Auto-continues in <span data-redirect-count>5</span>s · <a href="<?= e($target) ?>" data-redirect-skip class="nav-link" style="color:var(--crimson);">skip the wait →</a>
    </p>
  </header>

  <!-- Rotating promotional cards while we wait -->
  <?php if (!empty($promos)): ?>
    <div class="redirect-promos" data-redirect-promos>
      <?php foreach ($promos as $i => $p):
        $tone = $p['tone'] ?? 'crimson';
      ?>
        <article class="redirect-promo redirect-promo--<?= e($tone) ?><?= $i === 0 ? ' is-active' : '' ?>" data-promo-idx="<?= e((string)$i) ?>">
          <?php if (!empty($p['image_url'])): ?>
            <div class="redirect-promo__media" style="background-image:url('<?= e($p['image_url']) ?>');"></div>
          <?php endif; ?>
          <div class="redirect-promo__body">
            <?php if (!empty($p['badge'])): ?>
              <span class="redirect-promo__badge"><?= e($p['badge']) ?></span>
            <?php endif; ?>
            <?php if (!empty($p['eyebrow'])): ?>
              <div class="redirect-promo__eyebrow"><?= e($p['eyebrow']) ?></div>
            <?php endif; ?>
            <h2 class="redirect-promo__title"><?= e($p['title']) ?></h2>
            <?php if (!empty($p['subtitle'])): ?>
              <p class="redirect-promo__sub"><?= e($p['subtitle']) ?></p>
            <?php endif; ?>
            <?php if (!empty($p['cta_label']) && !empty($p['cta_href'])): ?>
              <a class="btn btn-on-dark btn-sm" href="<?= e($p['cta_href']) ?>" data-redirect-cancel><?= e($p['cta_label']) ?></a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>

      <?php if (count($promos) > 1): ?>
        <div class="redirect-promos__dots">
          <?php for ($i = 0; $i < count($promos); $i++): ?>
            <button type="button" class="redirect-promos__dot<?= $i === 0 ? ' is-active' : '' ?>"
                    data-promo-go="<?= e((string)$i) ?>" aria-label="Show promotion <?= e((string)($i+1)) ?>"></button>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <p class="caption" style="text-align:center;margin-top:24px;">
    Powered by <a href="<?= e(url('/tools/short-link')) ?>" class="nav-link">Afrostrength short-link</a> · free for everyone.
  </p>
</section>

<style>
  .redirect-stage { max-width: 720px; margin: 0 auto 64px; padding: 0 16px; }
  .redirect-stage__bar { height: 3px; background: var(--hairline); border-radius: 2px; overflow: hidden; margin-top: 12px; }
  .redirect-stage__bar-fill { width: 0; height: 100%; background: linear-gradient(90deg, #C0392B, #8B0000); transition: width .25s linear; }

  .redirect-promos { position: relative; min-height: 380px; }
  .redirect-promo {
    position: absolute; inset: 0;
    border-radius: 24px; overflow: hidden;
    display: grid; grid-template-columns: 1fr 1fr;
    opacity: 0; pointer-events: none;
    transition: opacity .5s ease;
  }
  .redirect-promo.is-active { opacity: 1; pointer-events: auto; }
  .redirect-promo__media {
    background-size: cover; background-position: center;
    min-height: 320px;
  }
  .redirect-promo__body {
    padding: 36px 32px;
    display: flex; flex-direction: column; justify-content: center;
    color: #fff;
    position: relative;
  }
  .redirect-promo--crimson .redirect-promo__body { background: linear-gradient(135deg, #C0392B 0%, #8B0000 100%); }
  .redirect-promo--ink     .redirect-promo__body { background: linear-gradient(135deg, #1A1A1A 0%, #0A0A0A 100%); }
  .redirect-promo--maroon  .redirect-promo__body { background: linear-gradient(135deg, #5C0000 0%, #2A0606 100%); }
  .redirect-promo--peach   .redirect-promo__body { background: linear-gradient(135deg, #FCE7DD 0%, #F8D2BF 100%); color: var(--ink); }
  .redirect-promo--bone    .redirect-promo__body { background: var(--bg-soft); color: var(--ink); }

  .redirect-promo__badge {
    align-self: flex-start;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: #fff;
    padding: 4px 10px; border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase;
    margin-bottom: 14px;
  }
  .redirect-promo--peach .redirect-promo__badge,
  .redirect-promo--bone  .redirect-promo__badge { background: rgba(10,10,10,0.06); border-color: var(--hairline); color: var(--crimson); }
  .redirect-promo__eyebrow {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase;
    color: rgba(255,255,255,0.7);
    margin-bottom: 12px;
  }
  .redirect-promo--peach .redirect-promo__eyebrow,
  .redirect-promo--bone  .redirect-promo__eyebrow { color: var(--crimson); }
  .redirect-promo__title {
    font-family: 'Garet', sans-serif;
    font-weight: 700;
    font-size: clamp(22px, 2.8vw, 32px);
    line-height: 1.15;
    letter-spacing: -0.02em;
    margin: 0 0 12px;
  }
  .redirect-promo__sub {
    font-size: 14.5px; line-height: 1.55; margin: 0 0 18px;
    opacity: 0.85;
  }
  .redirect-promo .btn.btn-on-dark { align-self: flex-start; }

  .redirect-promos__dots {
    position: absolute; bottom: 14px; left: 0; right: 0;
    display: flex; gap: 6px; justify-content: center;
  }
  .redirect-promos__dot {
    width: 6px; height: 6px; border-radius: 50%;
    border: 0; background: rgba(255,255,255,0.4); cursor: pointer;
    transition: width .25s ease, background .2s ease;
    padding: 0;
  }
  .redirect-promos__dot.is-active { width: 24px; border-radius: 4px; background: #fff; }

  @media (max-width: 720px) {
    .redirect-promo { grid-template-columns: 1fr; }
    .redirect-promo__media { min-height: 160px; }
  }
</style>

<noscript>
  <p style="text-align:center;margin-top:24px;">
    JavaScript is off — you'll be redirected automatically in a few seconds.
    Or <a href="<?= e($target) ?>">continue manually</a>.
  </p>
</noscript>

<script>
  (function(){
    var stage   = document.querySelector('[data-redirect-stage]');
    var target  = stage.dataset.target;
    var bar     = document.querySelector('[data-redirect-fill]');
    var count   = document.querySelector('[data-redirect-count]');
    var skip    = document.querySelector('[data-redirect-skip]');
    var DURATION = 5000;
    var startedAt = Date.now();
    var cancelled = false;

    // Rotating promos
    var promos = Array.from(document.querySelectorAll('[data-promo-idx]'));
    var dots   = Array.from(document.querySelectorAll('[data-promo-go]'));
    var current = 0;
    function showPromo(i) {
      promos.forEach(function(p){ p.classList.remove('is-active'); });
      dots.forEach(function(d){ d.classList.remove('is-active'); });
      if (promos[i]) promos[i].classList.add('is-active');
      if (dots[i]) dots[i].classList.add('is-active');
      current = i;
    }
    var rotor = null;
    if (promos.length > 1) {
      rotor = setInterval(function(){ showPromo((current + 1) % promos.length); }, 2200);
      dots.forEach(function(d){
        d.addEventListener('click', function(){
          if (rotor) { clearInterval(rotor); rotor = null; }
          showPromo(parseInt(d.dataset.promoGo, 10));
        });
      });
    }

    // Pause auto-redirect if the visitor clicks an in-promo CTA — they want to stay.
    document.querySelectorAll('[data-redirect-cancel]').forEach(function(a){
      a.addEventListener('click', function(){ cancelled = true; });
    });

    function tick() {
      if (cancelled) return;
      var elapsed = Date.now() - startedAt;
      var pct = Math.min(100, (elapsed / DURATION) * 100);
      bar.style.width = pct + '%';
      var remaining = Math.max(0, Math.ceil((DURATION - elapsed) / 1000));
      if (count) count.textContent = remaining;
      if (elapsed >= DURATION) { location.replace(target); return; }
      requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);

    skip.addEventListener('click', function(e){
      e.preventDefault();
      cancelled = true;
      location.replace(target);
    });
  })();
</script>
