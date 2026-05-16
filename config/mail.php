<?php
/**
 * SMTP settings for PHPMailer.
 * Replace placeholders before going live. The Mailer soft-fails to
 * storage/mail.log if creds are missing, so the site keeps working
 * during initial setup.
 */

return [
    'enabled'    => filter_var(getenv('MAIL_ENABLED') ?: '0', FILTER_VALIDATE_BOOL),
    'host'       => getenv('MAIL_HOST')       ?: 'smtp.example.com',
    'port'       => (int)(getenv('MAIL_PORT') ?: 587),
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls', // 'tls' or 'ssl'
    'username'   => getenv('MAIL_USERNAME')   ?: '',
    'password'   => getenv('MAIL_PASSWORD')   ?: '',
    'from'       => getenv('MAIL_FROM')       ?: 'studio@afrostrength.com',
    'from_name'  => getenv('MAIL_FROM_NAME')  ?: 'Afrostrength Studio',
    'to_inbox'   => getenv('MAIL_INBOX')      ?: 'studio@afrostrength.com',
];
