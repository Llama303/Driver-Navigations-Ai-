<?php
/**
 * Drive-NAV auth config and database connection.
 * Run install.php once to create the database and tables.
 */

if (!defined('DRIVE_NAV_AUTH')) {
    define('DRIVE_NAV_AUTH', true);
}

$AUTH_DIR = __DIR__;
$DATA_DIR = $AUTH_DIR . DIRECTORY_SEPARATOR . 'data';
$DB_PATH = $DATA_DIR . DIRECTORY_SEPARATOR . 'drive_nav.db';

/**
 * Base URL for password reset links (no trailing slash).
 * Change to your real domain, e.g. https://yourdomain.com/Log%20IN%20Sign%20Up
 */
function getBaseUrl() {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    return ($https ? 'https' : 'http') . '://' . $host . $script;
}

/**
 * @return PDO
 */
function getDb() {
    global $DB_PATH, $DATA_DIR;
    if (!is_dir($DATA_DIR)) {
        mkdir($DATA_DIR, 0755, true);
    }
    $dsn = 'sqlite:' . $DB_PATH;
    $pdo = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}
