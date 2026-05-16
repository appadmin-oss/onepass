<?php

/**
 * Thin wrapper around PHPMailer. Soft-fails to storage/mail.log so missing
 * SMTP creds don't break form submissions during initial setup.
 */
class Mailer {
    /** Send to the studio inbox (used by contact form, applications). */
    public static function send(string $subject, string $bodyHtml, ?string $replyTo = null): bool {
        $cfg = require AFS_ROOT . '/config/mail.php';
        return self::sendVia($cfg, $cfg['to_inbox'] ?? '', $subject, $bodyHtml, $replyTo);
    }

    /** Send a transactional email to a specific recipient (magic links, receipts). */
    public static function sendTo(string $to, string $subject, string $bodyHtml, ?string $replyTo = null): bool {
        $cfg = require AFS_ROOT . '/config/mail.php';
        return self::sendVia($cfg, $to, $subject, $bodyHtml, $replyTo);
    }

    private static function sendVia(array $cfg, string $to, string $subject, string $bodyHtml, ?string $replyTo): bool {
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            self::log("[bad-to] {$subject} → {$to}\n");
            return false;
        }
        if (!$cfg['enabled'] || empty($cfg['username']) || empty($cfg['password'])) {
            self::log("[disabled] {$subject} → {$to}\n{$bodyHtml}\n");
            return false;
        }

        $phpmailer = AFS_ROOT . '/vendor/phpmailer/src/PHPMailer.php';
        if (!is_file($phpmailer)) {
            self::log("[no-vendor] {$subject} → {$to}\n{$bodyHtml}\n");
            return false;
        }

        require_once AFS_ROOT . '/vendor/phpmailer/src/Exception.php';
        require_once AFS_ROOT . '/vendor/phpmailer/src/PHPMailer.php';
        require_once AFS_ROOT . '/vendor/phpmailer/src/SMTP.php';

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->Port       = $cfg['port'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'];
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($cfg['from'], $cfg['from_name']);
            $mail->addAddress($to);
            if ($replyTo) $mail->addReplyTo($replyTo);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $bodyHtml;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $bodyHtml));
            $mail->send();
            return true;
        } catch (Throwable $e) {
            self::log("[fail] {$subject} → {$to} — {$e->getMessage()}\n");
            return false;
        }
    }

    private static function log(string $line): void {
        $dir  = AFS_ROOT . '/storage';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $line = '[' . date('c') . "] " . $line . "\n";
        @file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX);
    }
}
