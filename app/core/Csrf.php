<?php
/**
 * Csrf
 * Generates and verifies per-session CSRF tokens for every state-changing
 * form/POST request in the application.
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Echo a ready-to-use hidden input field for HTML forms. */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function verify(?string $token): bool
    {
        return $token !== null && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /** Call at the top of every POST handler. Aborts the request on mismatch. */
    public static function verifyRequest(): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!self::verify($token)) {
            http_response_code(419);
            die('Your session has expired or the request could not be verified. Please refresh the page and try again.');
        }
    }
}
