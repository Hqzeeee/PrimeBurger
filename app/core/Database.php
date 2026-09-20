<?php
/**
 * Database
 * Thin singleton wrapper around a PDO connection using prepared statements
 * throughout the application. Never build SQL by string concatenation.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Never leak credentials or raw exception details to the browser.
                error_log('Database connection failed: ' . $e->getMessage());
                http_response_code(500);
                die('Database connection error. Please check config/config.php and try again.');
            }
        }

        return self::$instance;
    }
}
