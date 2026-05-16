<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
$attempted = current_path();
?>
<section class="subpage-hero" data-reveal>
  <div>
    <div class="subpage-hero__eyebrow">// 404 · NOT FOUND</div>
    <h1 class="subpage-hero__title">
      The page you wanted isn't <em>on this surface.</em>
    </h1>
    <p class="subpage-hero__sub">
      We looked for <code style="background:rgba(10,10,10,0.06);padding:2px 8px;border-radius:6px;font-family:'JetBrains Mono',monospace;font-size:13px;"><?= e($attempted) ?></code> and came up empty. It might have moved, or never existed.
    </p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="<?= e(url('/')) ?>" class="btn btn-primary">Studio home <?= icon_chev() ?></a>
      <button type="button" class="btn btn-ghost" data-cmdk-trigger>Try a search</button>
    </div>
  </div>

  <div class="subpage-hero__art" aria-hidden="true">
    <?php illustration('figure-question'); ?>
  </div>
</section>

<!-- Suggested routes -->
<section data-reveal class="section-block" style="margin-top:48px;">
  <div class="eyebrow">PERHAPS YOU WANTED</div>
  <h2 class="h2" style="margin:12px 0 24px;">Common starting points.</h2>
  <div class="grid-cols-3" data-stagger="80">
    <?php
    $suggestions = [
        ['title' => 'Services',     'desc' => 'Six capabilities. One studio.',      'href' => '/services',     'icon' => 'brand'],
        ['title' => 'Projects',     'desc' => 'Work that speaks.',                  'href' => '/projects',     'icon' => 'media'],
        ['title' => 'Field notes',  'desc' => 'Editorial direct from the studio.', 'href' => '/blog',         'icon' => 'editorial'],
        ['title' => 'Academy',      'desc' => 'Cohort-based courses.',              'href' => '/academy',      'icon' => 'cert'],
        ['title' => 'Contact',      'desc' => 'Tell us about the brief.',           'href' => '/contact',      'icon' => 'mail'],
        ['title' => 'FAQ',          'desc' => 'Direct answers, honest ones.',       'href' => '/faq',          'icon' => 'brief'],
    ];
    foreach ($suggestions as $s): ?>
      <a class="hairline" data-reveal href="<?= e(url($s['href'])) ?>"
         style="display:flex;align-items:flex-start;gap:16px;padding:24px;border-radius:14px;text-decoration:none;color:inherit;transition:transform .25s var(--ease-out), border-color .2s ease;">
        <span style="color:var(--crimson);flex-shrink:0;"><?= icon($s['icon'], 22) ?></span>
        <div>
          <div class="ff-display" style="font-weight:600;font-size:18px;letter-spacing:-0.01em;"><?= e($s['title']) ?></div>
          <div class="caption" style="margin-top:4px;"><?= e($s['desc']) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<script>
  // Wire the "Try a search" button to open the command palette.
  document.addEventListener('DOMContentLoaded', () => {
    const t = document.querySelector('[data-cmdk-trigger]');
    if (!t) return;
    t.addEventListener('click', () => {
      const ev = new KeyboardEvent('keydown', { key: 'k', metaKey: true });
      document.dispatchEvent(ev);
    });
  });
</script>
