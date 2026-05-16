<?php
/** @var array $sessions
 *  @var bool   $jaasEnabled
 *  @var ?string $jaasScript
 *  @var ?string $jaasToken
 *  @var ?string $jaasRoom
 *  @var string  $jaasDomain
 */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);

partial('subpage-hero', ['hero' => [
    'eyebrow'      => '// LIVE SESSIONS',
    'title'        => 'Live with the studio. <em>No replays.</em>',
    'sub'          => 'Working sessions, type clinics, and open Q&As — every session live, every session interactive, every session held in our own embedded room.',
    'cta_label'    => 'See the schedule',
    'cta_href'     => '#schedule',
    'illustration' => 'figure-phone',
    'variant'      => 'maroon',
]]);
?>
<p class="ff-mono" style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);margin:0 0 32px;">
  <?php if (($jaasMode ?? 'public') === 'jaas'): ?>
    // POWERED BY JaaS · TENANT <?= e(JAAS_APP_ID) ?> · DOMAIN <?= e($jaasDomain) ?>
  <?php else: ?>
    // POWERED BY OPEN-SOURCE JITSI · <?= e($jaasDomain) ?>
  <?php endif; ?>
</p>
<a id="schedule" aria-hidden="true"></a>

<!-- Embedded Jitsi player — works without JaaS via meet.jit.si fallback -->
<section class="hero-dark" data-reveal style="padding:0;margin-bottom:24px;background:#0A0A0A;border-radius:18px;overflow:hidden;">
  <div id="jaas-stage"
       style="aspect-ratio:16/9;width:100%;background:repeating-linear-gradient(135deg,#1A1A1A 0 10px,#0F0F0F 10px 20px);position:relative;display:flex;align-items:center;justify-content:center;">
    <!-- Pre-load splash (hidden once the API instantiates) -->
    <div data-jitsi-splash style="text-align:center;color:#fff;padding:32px;">
      <div class="ff-mono" style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#FCB7AB;">// LIVE STAGE READY</div>
      <p class="ff-display" style="font-weight:600;font-size:clamp(20px,2.6vw,26px);line-height:1.3;margin:10px 0 16px;max-width:30ch;">
        Click below to enter the room <em class="accent-italic" style="color:#FCB7AB;">live.</em>
      </p>
      <button type="button" data-jitsi-launch
              style="background:#fff;color:var(--ink);border:0;border-radius:999px;padding:12px 22px;font-weight:600;cursor:pointer;">
        Enter live room →
      </button>
      <p class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.5);margin-top:18px;">
        room · <?= e($jaasRoom) ?>
      </p>
    </div>
  </div>
</section>

<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:56px;">
  <a class="btn btn-ghost btn-sm" href="https://<?= e($jaasDomain) ?>/<?= e($jaasRoom) ?>" target="_blank" rel="noopener">
    Open in new tab ↗
  </a>
  <button type="button" class="btn btn-ghost btn-sm" data-jitsi-share>Copy room link</button>
  <span class="caption" data-jitsi-share-status></span>
</div>

<script src="<?= e($jaasScript) ?>" defer></script>
<script>
  (function(){
    var DOMAIN = <?= json_encode($jaasDomain) ?>;
    var ROOM   = <?= json_encode($jaasRoom) ?>;
    var JWT    = <?= json_encode($jaasToken) ?>;
    var launched = false;
    function launch() {
      if (launched) return;
      if (typeof JitsiMeetExternalAPI !== 'function') {
        // Script may still be loading — try again briefly.
        return setTimeout(launch, 250);
      }
      launched = true;
      var parent = document.getElementById('jaas-stage');
      parent.innerHTML = '';
      var opts = {
        roomName: ROOM,
        parentNode: parent,
        width: '100%', height: '100%',
        configOverwrite: {
          prejoinConfig:     { enabled: true },
          disableDeepLinking: true,
          startWithAudioMuted: true,
          startWithVideoMuted: true,
          defaultLanguage: 'en',
          toolbarButtons: ['microphone','camera','tileview','chat','raisehand','fullscreen','hangup'],
        },
        interfaceConfigOverwrite: {
          SHOW_JITSI_WATERMARK: false,
          DEFAULT_BACKGROUND:  '#0A0A0A',
        },
        userInfo: { displayName: 'Guest · Afrostrength' }
      };
      if (JWT) opts.jwt = JWT;
      new JitsiMeetExternalAPI(DOMAIN, opts);
    }
    var btn = document.querySelector('[data-jitsi-launch]');
    if (btn) btn.addEventListener('click', launch);

    var share  = document.querySelector('[data-jitsi-share]');
    var status = document.querySelector('[data-jitsi-share-status]');
    if (share) share.addEventListener('click', async function(){
      var url = 'https://' + DOMAIN + '/' + ROOM;
      try { await navigator.clipboard.writeText(url); status.textContent = '// link copied'; }
      catch(e){ status.textContent = '// copy failed — open in new tab instead'; }
    });
  })();
</script>

<!-- Schedule -->
<section data-reveal style="margin-bottom:64px;">
  <div class="eyebrow">UPCOMING SESSIONS</div>
  <div data-stagger="80" style="margin-top:24px;border-top:1px solid var(--hairline);">
    <?php foreach ($sessions as $s):
      $startTs = strtotime($s['starts_at']);
    ?>
      <div data-reveal class="session-row">
        <div>
          <div class="ff-display" style="font-weight:700;font-size:24px;line-height:1;letter-spacing:-0.02em;"><?= e(date('D · d M', $startTs)) ?></div>
          <div class="ff-mono" style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.55);margin-top:4px;"><?= e(date('H:i', $startTs)) ?> WAT · <?= e((string)$s['duration_minutes']) ?>min</div>
        </div>
        <div>
          <div class="ff-display" style="font-weight:600;font-size:20px;"><?= e($s['title']) ?></div>
          <div class="caption" style="margin-top:4px;"><?= e($s['summary'] ?? '') ?></div>
        </div>
        <div class="ff-mono" style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:rgba(10,10,10,0.7);"><?= e($s['instructor'] ?? '') ?></div>
        <a class="btn btn-primary btn-sm" href="<?= e(url('/academy/apply')) ?>">Reserve <?= icon_chev(12) ?></a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php partial('cta-final'); ?>
