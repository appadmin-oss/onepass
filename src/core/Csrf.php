<?php

class Csrf {
    public static function token(): string {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(24));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function check(?string $candidate): bool {
        return is_string($candidate)
            && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $candidate);
    }

    /**
     * Enforce CSRF on a mutating request. We pull the candidate from,
     * in order of preference: $_POST, the X-CSRF-Token header, then a
     * JSON body field. Refusal mode is content-negotiated:
     *
     *   - AJAX or Accept: application/json  → 419 JSON
     *   - everything else                   → 419 HTML with a clear
     *                                          message (no white screen)
     */
    public static function require(): void {
        $tok = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        // JSON bodies don't populate $_POST. Look there as a last resort.
        if (!$tok && stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== false) {
            $raw = (string) file_get_contents('php://input');
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded['_csrf'])) {
                    $tok = (string) $decoded['_csrf'];
                }
            }
        }

        if (self::check($tok)) return;

        http_response_code(419);
        $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
        $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
               || stripos($accept, 'application/json') !== false;

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'csrf', 'message' => 'Session expired. Refresh and try again.']);
        } else {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><meta charset="utf-8">'
               . '<title>Session expired · Afrostrength</title>'
               . '<style>body{font:16px/1.5 system-ui,sans-serif;padding:48px;max-width:640px;margin:auto;color:#0A0A0A;}'
               . 'a{color:#C0392B;}.b{font-family:JetBrains Mono,monospace;color:#777;font-size:12px;}</style>'
               . '<p class="b">// 419 — CSRF mismatch</p>'
               . '<h1 style="font-family:Garet,serif;font-size:28px;letter-spacing:-0.01em;">Session expired.</h1>'
               . '<p>Your sign-in or form token has aged out. Go back, refresh the page, and submit again.</p>'
               . '<p><a href="javascript:history.back()">← Go back</a> &nbsp;·&nbsp; '
               . '<a href="/">Home</a></p>';
        }
        exit;
    }
}
