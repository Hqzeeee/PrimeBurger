<?php
class AuthController
{
    /** @return string|null error message, or null on success */
    public function login(array $post): ?string
    {
        Csrf::verifyRequest();

        $username = trim($post['username'] ?? '');
        $password = (string)($post['password'] ?? '');

        if ($username === '' || $password === '') {
            return 'Please enter your username/email and password.';
        }

        // Simple brute-force throttle using the session.
        $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
        $_SESSION['login_attempts_window'] = $_SESSION['login_attempts_window'] ?? time();

        if (time() - $_SESSION['login_attempts_window'] > 300) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_attempts_window'] = time();
        }
        if ($_SESSION['login_attempts'] >= 6) {
            return 'Too many failed login attempts. Please wait a few minutes and try again.';
        }

        if (Auth::attempt($username, $password)) {
            $_SESSION['login_attempts'] = 0;
            ActivityLogger::log('login', 'User logged in');
            return null;
        }

        $_SESSION['login_attempts']++;
        return 'Invalid username/email or password.';
    }

    public function logout(): void
    {
        ActivityLogger::log('logout', 'User logged out');
        Auth::logout();
    }
}
