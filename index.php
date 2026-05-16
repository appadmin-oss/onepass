<?php
/**
 * Afrostrength — front controller.
 * All non-asset traffic enters here via .htaccess rewrite.
 */

declare(strict_types=1);

define('AFS_ROOT', __DIR__);
define('AFS_START', microtime(true));

session_start();

require AFS_ROOT . '/config/app.php';
require AFS_ROOT . '/src/core/Helpers.php';
require AFS_ROOT . '/src/core/Database.php';
require AFS_ROOT . '/src/core/Csrf.php';
require AFS_ROOT . '/src/core/Auth.php';
require AFS_ROOT . '/src/core/View.php';
require AFS_ROOT . '/src/core/StudentAuth.php';
require AFS_ROOT . '/src/core/Validator.php';
require AFS_ROOT . '/src/core/Mailer.php';
require AFS_ROOT . '/src/core/Lms.php';
require AFS_ROOT . '/src/core/Jaas.php';
require AFS_ROOT . '/src/core/Ai.php';
require AFS_ROOT . '/src/core/AiRegistry.php';
require AFS_ROOT . '/src/core/AiCache.php';
require AFS_ROOT . '/src/core/AiBudget.php';
require AFS_ROOT . '/src/core/AiLog.php';
require AFS_ROOT . '/src/core/Firebase.php';
require AFS_ROOT . '/src/core/Security.php';
require AFS_ROOT . '/src/core/MagicLink.php';
Security::sendHeaders();
require AFS_ROOT . '/src/core/Controller.php';
require AFS_ROOT . '/src/core/Router.php';

// Lightweight autoload for controllers/models
spl_autoload_register(function (string $class): void {
    $candidates = [
        AFS_ROOT . '/src/controllers/' . str_replace('\\', '/', $class) . '.php',
        AFS_ROOT . '/src/models/' . $class . '.php',
    ];
    foreach ($candidates as $path) {
        if (is_file($path)) { require $path; return; }
    }
});

$router = new Router();
require AFS_ROOT . '/config/routes.php';

try {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    // ---- Path normalisation -------------------------------------------------
    // Production 404s came from three real-world cases, all upstream of the
    // route table:
    //   (a) App installed in a subdirectory (e.g. /staging/, addon-domain
    //       layouts). REQUEST_URI is `/staging/about` but routes register
    //       `/about`. Without stripping, every page 404s.
    //   (b) mod_rewrite silently disabled / AllowOverride trimmed. The
    //       DirectoryIndex still serves /index.php, so the user lands on
    //       `/index.php` for the homepage. No route matches the literal
    //       string `/index.php`, so the homepage itself 404s.
    //   (c) PATH_INFO-style URLs (/index.php/about) that some shared hosts
    //       generate when their rewrite layer is half-configured.
    //
    // We normalise here, once, so the router stays simple.

    // (a) Strip the subdirectory the front controller lives in. dirname()
    //     of SCRIPT_NAME returns '/staging' when index.php is at
    //     /staging/index.php, and '/' (or '' on some systems) at root.
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($scriptDir !== '' && $scriptDir !== '/' && $scriptDir !== '.') {
        if ($path === $scriptDir) {
            $path = '/';
        } elseif (str_starts_with($path, $scriptDir . '/')) {
            $path = substr($path, strlen($scriptDir));
        }
    }

    // (b) + (c) Treat /index.php and /index.php/<rest> as if the user asked
    //     for / or /<rest>. Defensive against missing/partial rewrites.
    if (str_starts_with($path, '/index.php')) {
        $rest  = substr($path, strlen('/index.php'));
        $path  = ($rest === '' || $rest === '/') ? '/' : $rest;
    }

    // Collapse accidental // → /. Some upstream proxies introduce these.
    $path = '/' . ltrim(preg_replace('#/+#', '/', $path) ?? $path, '/');

    // Subdomain → path scoping. We resolve everything against the same
    // route table by prefixing the path with the scope. tools.example.com/qr
    // becomes /tools/qr internally. Asset URLs aren't touched (.htaccess
    // serves them before this file runs). Already-prefixed paths are a no-op.
    $scope = afs_host_scope();
    $scopePrefix = ['academy' => '/academy', 'tools' => '/tools', 'status' => '/status'][$scope] ?? '';
    if ($scopePrefix !== '' && !str_starts_with($path, $scopePrefix) && $path !== '/api' && !str_starts_with($path, '/api/')) {
        $path = ($path === '/') ? $scopePrefix : ($scopePrefix . $path);
    }

    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
} catch (Throwable $e) {
    if (AFS_DEBUG) {
        http_response_code(500);
        echo '<pre style="font:12px/1.5 monospace;padding:24px;">';
        echo htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString());
        echo '</pre>';
    } else {
        error_log('[afs] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        // Last-resort handler: never let a 500 cascade into a white screen.
        // If even the 500 template throws (e.g. when the page itself
        // depends on the broken module), emit a static fallback.
        try {
            http_response_code(500);
            (new Controller())->view('pages/500', ['title' => 'Something broke'], 'main');
        } catch (Throwable $inner) {
            error_log('[afs/500] ' . $inner->getMessage());
            if (!headers_sent()) header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><meta charset="utf-8"><title>Service interrupted</title>'
               . '<style>body{font:16px/1.5 system-ui,sans-serif;padding:48px;max-width:640px;margin:auto;}'
               . 'h1{font-size:28px;letter-spacing:-0.01em;}a{color:#C0392B;}</style>'
               . '<h1>The studio is briefly unavailable.</h1>'
               . '<p>We logged this. Try again in a moment, or '
               . '<a href="mailto:afrostrength@gmail.com">email us</a> if it persists.</p>';
        }
    }
} finally {
    // Ensure the session file lock is released even on a fatal path —
    // a hung lock makes the next request block for ~30s on shared cPanel.
    if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
        @session_write_close();
    }
}
