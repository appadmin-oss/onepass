<?php
/**
 * Database connection settings.
 * Update these before first deploy. Cpanel typically gives you
 * DB names in the form `cpaneluser_dbname`.
 */

return [
    'host'    => getenv('DB_HOST') ?: '127.0.0.1',
    'port'    => (int)(getenv('DB_PORT') ?: 3306),
    'name'    => getenv('DB_NAME') ?: 'afrostrength',
    'user'    => getenv('DB_USER') ?: 'root',
    'pass'    => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
