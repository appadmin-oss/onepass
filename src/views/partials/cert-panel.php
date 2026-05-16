<?php require_once AFS_ROOT . '/src/views/partials/icons.php'; ?>
<section data-reveal class="section-block">
  <div class="cert-panel--split">
    <div class="cert-panel" style="display:flex;flex-direction:column;justify-content:space-between;min-height:340px;">
      <div class="ff-mono" style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:rgba(255,255,255,0.7);">// CERTIFICATION TRACKS</div>
      <div>
        <h3 class="ff-display" style="font-weight:700;font-size:clamp(34px,5vw,44px);line-height:0.95;margin:0;">
          Skill up.<br><em class="accent-italic" style="font-style:italic;color:#FCB7AB;">Move up.</em>
        </h3>
        <a href="<?= e(url('/academy/certifications')) ?>" class="btn btn-maroon" style="margin-top:28px;">Get certified <?= icon_chev() ?></a>
      </div>
    </div>
    <div class="cert-panel" style="min-height:340px;">
      <h4 class="ff-display" style="font-weight:600;font-size:20px;line-height:1.4;max-width:30ch;margin:0 0 24px;">
        Build the skills that move careers — and certify them with credentials our clients hire against.
      </h4>
      <div>
        <?php
        $certs = [
            ['title' => 'Brand Strategist Certificate'],
            ['title' => 'Editorial Systems Certificate'],
            ['title' => 'Media Production Skill Badge'],
            ['title' => 'Event Management Foundations'],
        ];
        foreach ($certs as $cert) { partial('cert-row', ['cert' => $cert]); }
        ?>
      </div>
    </div>
  </div>
</section>
