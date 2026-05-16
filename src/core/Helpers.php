<?php
/** Tiny global helper set. */

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string {
    if (preg_match('#^https?://#', $path)) return $path;
    return rtrim(AFS_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return rtrim(AFS_URL, '/') . '/assets/' . ltrim($path, '/');
}

/**
 * Versioned asset URL. Appends ?v=<mtime> so browsers refresh after a
 * deploy without manual cache-busting. Falls back to the bare URL if the
 * file isn't on disk (dev typo, CDN proxy, etc).
 */
function asset_v(string $path): string {
    $clean = ltrim($path, '/');
    $abs   = AFS_ROOT . '/assets/' . $clean;
    $url   = rtrim(AFS_URL, '/') . '/assets/' . $clean;
    if (is_file($abs)) {
        $mt = filemtime($abs);
        if ($mt) return $url . '?v=' . $mt;
    }
    return $url;
}

function current_path(): string {
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
}

function is_active(string $prefix): bool {
    $p = current_path();
    if ($prefix === '/') return $p === '/';
    return str_starts_with($p, rtrim($prefix, '/'));
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? '';
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = strtolower($text);
    return preg_replace('~[^-\w]+~', '', $text) ?: 'item';
}

function excerpt(string $body, int $words = 28): string {
    $text = strip_tags($body);
    $arr  = preg_split('/\s+/', trim($text)) ?: [];
    if (count($arr) <= $words) return $text;
    return implode(' ', array_slice($arr, 0, $words)) . '…';
}

function date_pretty(string $iso): string {
    $t = strtotime($iso);
    return $t ? date('M j, Y', $t) : $iso;
}

function read_time(string $body): string {
    $words = str_word_count(strip_tags($body));
    return max(1, (int)ceil($words / 220)) . ' min read';
}

function json_attr(array $data): string {
    return e(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function partial(string $name, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    require AFS_ROOT . '/src/views/partials/' . $name . '.php';
}

/** Render a transactional email template to a string. */
function render_email(string $name, array $vars = []): string {
    $path = AFS_ROOT . '/src/views/emails/' . $name . '.php';
    if (!is_file($path)) return '';
    extract($vars, EXTR_SKIP);
    ob_start();
    require $path;
    return (string) ob_get_clean();
}

function illustration(string $name): void {
    $path = AFS_ROOT . '/assets/svg/illustrations/' . $name . '.svg';
    if (is_file($path)) readfile($path);
}

function on_academy(): bool {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return str_starts_with($host, 'academy.') || str_starts_with(current_path(), '/academy');
}

function flash_set(string $key, string $msg): void { $_SESSION['_flash'][$key] = $msg; }
function flash_pop(string $key): ?string {
    $v = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $v;
}
