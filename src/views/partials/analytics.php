<?php
/**
 * Loads Google Analytics 4 (if GA_MEASUREMENT_ID is set) and exposes the
 * Firebase Web SDK config to the client (if FIREBASE_API_KEY is set).
 *
 * GA consent: we boot gtag in *denied* state and only flip to *granted*
 * after the cookie banner records consent. This aligns with NDPA + GDPR.
 * Firebase config: the API key is public by design (security comes from
 * Firebase rules + server-side ID-token verification), so it is safe to
 * print into HTML.
 */
?>
<?php if (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID !== ''): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(GA_MEASUREMENT_ID) ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    window.gtag = gtag;
    // Default to denied until the cookie banner records consent.
    gtag('consent', 'default', {
      ad_storage: 'denied',
      analytics_storage: (localStorage.getItem('afs_cookie_consent') === 'all' ? 'granted' : 'denied'),
      ad_user_data: 'denied',
      ad_personalization: 'denied'
    });
    gtag('js', new Date());
    gtag('config', <?= json_encode(GA_MEASUREMENT_ID) ?>, {
      anonymize_ip: true,
      send_page_view: true
    });
  </script>
<?php endif; ?>

<?php if (defined('FIREBASE_ENABLED') && FIREBASE_ENABLED): ?>
  <script>
    window.AFS_FIREBASE_CONFIG = <?= json_encode([
        'apiKey'           => FIREBASE_API_KEY,
        'authDomain'       => FIREBASE_AUTH_DOMAIN,
        'projectId'        => FIREBASE_PROJECT_ID,
        'appId'            => FIREBASE_APP_ID,
        'messagingSenderId'=> FIREBASE_MESSAGING_SENDER_ID,
    ], JSON_UNESCAPED_SLASHES) ?>;
  </script>
<?php endif; ?>
