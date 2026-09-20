<?php
/**
 * PrimeBurger IMS - Application Configuration
 * Edit the values below to match your local XAMPP / MySQL setup.
 */

// ---- Database ---------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'primeburger_ims');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Application --------------------------------------------------------
define('APP_NAME', 'PrimeBurger Inventory Management System');
define('APP_URL', 'http://localhost/PrimeBurger');
define('APP_TIMEZONE', 'Asia/Manila');

// Days before expiration to flag a product as "expiring soon" (YELLOW)
define('EXPIRY_WARNING_DAYS', 5);

// Session lifetime in seconds (2 hours)
define('SESSION_LIFETIME', 7200);

date_default_timezone_set(APP_TIMEZONE);

// ---- Error display -------------------------------------------------------
// Set to false in a live/production deployment.
define('APP_DEBUG', true);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
