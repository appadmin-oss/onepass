<?php
/**
 * App-wide constants. Override per environment by editing this file
 * (or via environment variables before deploy).
 */

define('AFS_NAME',    'Afrostrength');
define('AFS_TAGLINE', 'Building Brands, Strengthening Legacies');
define('AFS_DESC',    'Afrostrength — strategy, identity, media, and project execution for the brands shaping the next decade of African business.');

// Public URL — explicit env wins; otherwise auto-detect from current request,
// so local dev, staging, and production all generate correct absolute URLs
// without per-environment config edits. Host is allowlisted to prevent
// Host-header injection from leaking into canonical/OG metadata.
$afs_default_url = (function (): string {
    $allowed = ['afrostrength.com', 'academy.afrostrength.com', 'localhost', '127.0.0.1'];
    $rawHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host    = preg_replace('/[^a-zA-Z0-9\.\-:]/', '', $rawHost) ?: 'localhost';
    $bareHost = preg_replace('/:\d+$/', '', $host);
    $ok = false;
    foreach ($allowed as $h) if ($bareHost === $h || str_ends_with($bareHost, '.' . $h)) { $ok = true; break; }
    if (!$ok) $host = 'afrostrength.com';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host;
})();
define('AFS_URL',     getenv('AFS_URL')    ?: $afs_default_url);
define('AFS_LOCALE',  getenv('AFS_LOCALE') ?: 'en');
define('AFS_TZ',      getenv('AFS_TZ')     ?: 'Africa/Lagos');

// Toggle verbose error display while building locally.
define('AFS_DEBUG',   filter_var(getenv('AFS_DEBUG') ?: '0', FILTER_VALIDATE_BOOL));

// Subdomain detection — every subdomain points at the same document
// root (cPanel addon-domain / subdomain pattern); the front controller
// branches on host to deliver a focused IA per subdomain. The main
// domain still serves everything when accessed via its own paths.
define('AFS_ACADEMY_HOST', 'academy.afrostrength.com');
define('AFS_TOOLS_HOST',   'tools.afrostrength.com');
define('AFS_STATUS_HOST',  'status.afrostrength.com');

/**
 * Returns one of: 'main' | 'academy' | 'tools' | 'status'.
 * Used by the router to scope routes (e.g. tools.* only resolves /tools/*
 * paths) and by view code to flip layouts. Defaults to 'main' if the
 * host header is unknown — defence against host-header weirdness.
 */
function afs_host_scope(): string {
    static $scope = null;
    if ($scope !== null) return $scope;
    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    if ($host === AFS_ACADEMY_HOST) return $scope = 'academy';
    if ($host === AFS_TOOLS_HOST)   return $scope = 'tools';
    if ($host === AFS_STATUS_HOST)  return $scope = 'status';
    return $scope = 'main';
}

date_default_timezone_set(AFS_TZ);

// ---------------------------------------------------------------
// Academy integrations: Moodle (LMS) + Jitsi-as-a-Service (live)
// ---------------------------------------------------------------
// Moodle runs as a separate install at the academy subdomain
// (/lms path). We pull catalog + enrolment via its REST web services
// and deep-link learners into the Moodle player after auth.
define('LMS_BASE_URL', getenv('LMS_BASE_URL') ?: 'https://academy.afrostrength.com/lms');
define('LMS_TOKEN',    getenv('LMS_TOKEN')    ?: '');     // Moodle web-service token
define('LMS_ENABLED',  filter_var(getenv('LMS_ENABLED') ?: '0', FILTER_VALIDATE_BOOL));

// Jitsi as a Service — server mints a JWT for each room with the
// tenant's private key. Set up at https://jaas.8x8.vc.
define('JAAS_APP_ID',     getenv('JAAS_APP_ID')     ?: '');     // vpaas-magic-cookie-...
define('JAAS_API_KEY',    getenv('JAAS_API_KEY')    ?: '');     // vpaas-magic-cookie-.../...
define('JAAS_PRIVATE_KEY',getenv('JAAS_PRIVATE_KEY')?: AFS_ROOT . '/storage/jaas-private-key.pem');
define('JAAS_DOMAIN',     getenv('JAAS_DOMAIN')     ?: '8x8.vc');
define('JAAS_ENABLED',    filter_var(getenv('JAAS_ENABLED') ?: '0', FILTER_VALIDATE_BOOL));

// ---------------------------------------------------------------
// Google Analytics 4 — set GA_MEASUREMENT_ID in env (e.g. G-XXXX)
// We only inject the gtag snippet when an ID is present so dev
// stays clean. Consent is gated by the cookie banner.
// ---------------------------------------------------------------
define('GA_MEASUREMENT_ID', getenv('GA_MEASUREMENT_ID') ?: '');

// ---------------------------------------------------------------
// Firebase Authentication (Spark / free tier) — Google sign-in
// On the Firebase console (free plan), create a project, enable
// "Google" provider, register a web app, then drop its config
// values into the environment. We use Firebase only for OAuth
// (Google account) so we never see passwords; the server verifies
// the returned ID token against Google's JWKS.
// ---------------------------------------------------------------
define('FIREBASE_API_KEY',            getenv('FIREBASE_API_KEY')            ?: '');
define('FIREBASE_AUTH_DOMAIN',        getenv('FIREBASE_AUTH_DOMAIN')        ?: '');
define('FIREBASE_PROJECT_ID',         getenv('FIREBASE_PROJECT_ID')         ?: '');
define('FIREBASE_APP_ID',             getenv('FIREBASE_APP_ID')             ?: '');
define('FIREBASE_MESSAGING_SENDER_ID',getenv('FIREBASE_MESSAGING_SENDER_ID')?: '');
define('FIREBASE_ENABLED', FIREBASE_API_KEY !== '' && FIREBASE_PROJECT_ID !== '');

// ---------------------------------------------------------------
// AI surface visibility
// ---------------------------------------------------------------
// Whether to render the "Drafted with AI" disclosure line below
// AI-generated content. Default true: honest by default. Can be
// toggled per-environment for clean screenshot captures.
define('AFS_AI_DISCLOSURE_VISIBLE',
    filter_var(getenv('AFS_AI_DISCLOSURE_VISIBLE') ?: '1', FILTER_VALIDATE_BOOL));
