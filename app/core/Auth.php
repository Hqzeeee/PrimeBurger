<?php
/**
 * Auth
 * Handles login, logout, password verification and role-based access
 * control. Sessions are regenerated on login to prevent session fixation.
 */
class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'httponly' => true,   // not readable via JS -> mitigates XSS session theft
                'samesite' => 'Lax',  // mitigates CSRF via cross-site requests
            ]);
            session_start();
        }

        // Enforce idle timeout.
        if (self::check()) {
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
                self::logout();
                header('Location: ' . APP_URL . '/login.php?timeout=1');
                exit;
            }
            $_SESSION['last_activity'] = time();
        }
    }

    public static function attempt(string $username, string $password): bool
    {
        $userModel = new UserModel();
        $user = $userModel->findByUsernameOrEmail($username);

        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Rehash transparently if PHP's default algorithm/cost has changed.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $userModel->updatePasswordHash((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        session_regenerate_id(true);

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['last_activity'] = time();

        $userModel->touchLastLogin((int)$user['id']);

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function fullName(): string
    {
        return $_SESSION['full_name'] ?? 'Guest';
    }

    public static function isOwner(): bool
    {
        return self::role() === 'owner';
    }

    /** Redirect to login if not authenticated. Call at the top of every protected page. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }

    /** Restrict a page to specific roles, e.g. Auth::requireRole(['owner']). */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}
