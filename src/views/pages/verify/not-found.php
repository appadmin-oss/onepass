<?php
require_once AFS_ROOT . '/src/views/partials/icons.php';
partial('breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]);
?>

<?php partial('section-header', [
  'num'     => '404',
  'eyebrow' => 'Certificate not found',
  'title'   => "We couldn't find that <em>certificate.</em>",
  'lead'    => 'The code <code class="cert__code">' . e($code) . '</code> doesn\'t match any certificate we\'ve issued. Codes look like <code>AFS-YYYY-XXXX-XXXX</code>. Double-check the format and try again, or contact us if you believe this is an error.',
]); ?>

<section data-reveal>
  <div class="auth-form__row" style="max-width:520px;">
    <a class="btn btn-primary btn-block" href="<?= e(url('/verify')) ?>">Try another code</a>
    <a class="btn btn-ghost btn-block" href="mailto:afrostrength@gmail.com?subject=Certificate%20verification">Email us</a>
  </div>
</section>
