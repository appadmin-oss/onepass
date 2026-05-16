<?php
/** @var array $cert */
/** @var array $skills */
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
$revoked = !empty($cert['revoked_at']);
$expired = !empty($cert['expires_on']) && strtotime($cert['expires_on']) < time();
$state   = $revoked ? 'revoked' : ($expired ? 'expired' : 'valid');
$stateMeta = [
    'valid'   => ['Verified', 'cert-state--valid'],
    'expired' => ['Expired',  'cert-state--warn'],
    'revoked' => ['Revoked',  'cert-state--bad'],
];
[$stateLabel, $stateClass] = $stateMeta[$state];
?>

<article class="cert" data-reveal>
  <header class="cert__head">
    <div class="cert__seal" aria-hidden="true">
      <?= icon_logo(40) ?>
    </div>
    <div>
      <div class="ff-mono cert__eyebrow">// CERTIFICATE OF COMPLETION</div>
      <h1 class="cert__title"><?= e($cert['certification_title']) ?></h1>
      <p class="cert__sub">Awarded by <strong>Afrostrength Limited</strong> · Lagos, Nigeria</p>
    </div>
    <div class="cert__state <?= e($stateClass) ?>" role="status" aria-live="polite"><?= e($stateLabel) ?></div>
  </header>

  <dl class="cert__facts">
    <div>
      <dt>Recipient</dt>
      <dd><?= e($cert['student_name']) ?></dd>
    </div>
    <?php if (!empty($cert['course_title'])): ?>
    <div>
      <dt>Programme</dt>
      <dd><?= e($cert['course_title']) ?></dd>
    </div>
    <?php endif; ?>
    <?php if (!empty($cert['cohort'])): ?>
    <div>
      <dt>Cohort</dt>
      <dd><?= e($cert['cohort']) ?></dd>
    </div>
    <?php endif; ?>
    <div>
      <dt>Issued on</dt>
      <dd><?= e(date('j F Y', strtotime($cert['issued_on']))) ?></dd>
    </div>
    <?php if (!empty($cert['expires_on'])): ?>
    <div>
      <dt>Valid through</dt>
      <dd><?= e(date('j F Y', strtotime($cert['expires_on']))) ?></dd>
    </div>
    <?php endif; ?>
    <?php if (!empty($cert['grade'])): ?>
    <div>
      <dt>Award</dt>
      <dd><?= e($cert['grade']) ?></dd>
    </div>
    <?php endif; ?>
    <div>
      <dt>Certificate code</dt>
      <dd><code class="cert__code"><?= e($cert['code']) ?></code></dd>
    </div>
  </dl>

  <?php if ($skills): ?>
    <section class="cert__skills" aria-label="Skills certified">
      <div class="ff-mono cert__skills-h">// SKILLS CERTIFIED</div>
      <ul>
        <?php foreach ($skills as $s): ?>
          <li><?= e((string)$s) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <!-- Plain-language note: what this credential typically means in the
       NG / pan-African job market. Cached for 30 days per certification
       title — Ai::run handles the cache key. Hidden entirely when AI
       is off (the credential reads fine without it). -->
  <?php if (!$revoked && Ai::enabled()): ?>
    <section class="cert__plain" aria-label="What this credential means" style="margin-top:24px;">
      <?php partial('ai-block', [
        'intent'   => 'verify_plain',
        'kind'     => 'summary',
        'label'    => '✨ What does this credential mean?',
        'eyebrow'  => '// MENTOR CONTEXT',
        'auto'     => true,
        'context'  => json_encode([
            'title'  => (string)$cert['certification_title'],
            'course' => (string)($cert['course_title'] ?? ''),
            'level'  => (string)($cert['grade'] ?? ''),
            'skills' => array_slice(array_map(
                fn($s) => mb_substr((string)$s, 0, 40),
                $skills
            ), 0, 20),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      ]); ?>
    </section>
  <?php endif; ?>

  <?php if ($revoked): ?>
    <aside class="cert__notice cert__notice--bad" role="note">
      <strong>This certificate has been revoked.</strong>
      <?php if (!empty($cert['revoked_reason'])): ?>
        Reason: <?= e($cert['revoked_reason']) ?>.
      <?php endif; ?>
      It is no longer valid. Contact <a href="mailto:afrostrength@gmail.com">afrostrength@gmail.com</a>
      for any questions.
    </aside>
  <?php elseif ($expired): ?>
    <aside class="cert__notice cert__notice--warn" role="note">
      <strong>This certificate has expired.</strong>
      It documents work completed on <?= e(date('j F Y', strtotime($cert['issued_on']))) ?> but is no longer current.
    </aside>
  <?php endif; ?>

  <footer class="cert__foot">
    <span>Verification URL: <code><?= e(url('/verify/' . $cert['code'])) ?></code></span>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/verify')) ?>">Verify another</a>
  </footer>
</article>

<?php
// Structured data so search engines and badge platforms can read the credential.
$jsonld = [
  '@context' => 'https://schema.org',
  '@type'    => 'EducationalOccupationalCredential',
  'name'     => $cert['certification_title'],
  'url'      => url('/verify/' . $cert['code']),
  'credentialCategory' => 'Certification',
  'recognizedBy' => [
    '@type' => 'Organization',
    'name'  => 'Afrostrength Limited',
    'url'   => url('/'),
  ],
  'about'    => $cert['course_title'] ?? null,
  'dateCreated' => $cert['issued_on'],
];
if (!empty($cert['expires_on'])) $jsonld['expires'] = $cert['expires_on'];
?>
<script type="application/ld+json"><?= json_encode($jsonld, JSON_UNESCAPED_SLASHES) ?></script>
