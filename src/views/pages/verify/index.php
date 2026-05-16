<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => '01',
  'eyebrow' => 'Certificate authenticity',
  'title'   => 'Verify an Afrostrength <em>certificate.</em>',
  'lead'    => 'Every certificate we issue carries a unique code. Paste it below to confirm the recipient, programme and date of issuance. We only show fields that the recipient has agreed to disclose.',
]); ?>

<section data-reveal>
  <form action="<?= e(url('/verify')) ?>" method="get" class="verify-form" role="search" aria-label="Look up a certificate by code">
    <label class="field field--auth" for="cert-code">
      <span class="field__label">Certificate code</span>
      <input id="cert-code"
             type="text"
             name="code"
             autocomplete="off"
             autocapitalize="characters"
             spellcheck="false"
             required
             placeholder="AFS-2026-XXXX-XXXX"
             pattern="[Aa][Ff][Ss][- ]?\d{4}[- ]?[A-Za-z0-9]{4}[- ]?[A-Za-z0-9]{4}">
    </label>
    <button type="submit" class="btn btn-primary">
      <span>Verify</span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path d="M5 12h14M13 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
  </form>

  <p class="caption" style="max-width:62ch;margin-top:18px;">
    Codes look like <code>AFS-2026-XXXX-XXXX</code>. Spaces and lowercase are fine —
    we'll normalise the input before looking it up. If a certificate has been revoked
    we'll say so on its detail page.
  </p>
</section>
