<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| Application Configuration
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/app.php';


/*
|--------------------------------------------------------------------------
| Start Secure Session
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {

    ini_set(
        'session.use_strict_mode',
        '1'
    );

    ini_set(
        'session.use_only_cookies',
        '1'
    );


    $isHttps =
        !empty($_SERVER['HTTPS'])
        &&
        $_SERVER['HTTPS'] !== 'off';


    session_name(
        'primeburger_session'
    );


    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);


    session_start();
}


/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/

const PUBLIC_PAGES = [
    'index.php',
    'login.php',
    'signup.php',
];


/*
|--------------------------------------------------------------------------
| URL Helper
|--------------------------------------------------------------------------
*/

function appUrl(
    string $path = ''
): string {

    $path =
        ltrim(
            $path,
            '/'
        );


    if ($path === '') {

        return BASE_URL . '/';

    }


    return
        BASE_URL
        .
        '/'
        .
        $path;
}


/*
|--------------------------------------------------------------------------
| Redirect Helper
|--------------------------------------------------------------------------
*/

function redirectTo(
    string $path
): never {

    header(
        'Location: '
        .
        appUrl($path)
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Check If User Is Logged In
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool {

    return isset(
        $_SESSION['user_id'],
        $_SESSION['username'],
        $_SESSION['role']
    );
}


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

function currentUser(): ?array {

    if (!isLoggedIn()) {

        return null;
    }


    return [

        'user_id' =>
            (int)
            $_SESSION['user_id'],

        'name' =>
            (string)
            (
                $_SESSION['name']
                ??
                ''
            ),

        'username' =>
            (string)
            $_SESSION['username'],

        'email' =>
            (string)
            (
                $_SESSION['email']
                ??
                ''
            ),

        'role' =>
            (string)
            $_SESSION['role'],

    ];
}


/*
|--------------------------------------------------------------------------
| Login User
|--------------------------------------------------------------------------
*/

function loginUser(
    array $user
): void {

    session_regenerate_id(
        true
    );


    $_SESSION['user_id'] =
        (int)
        $user['user_id'];


    $_SESSION['name'] =
        (string)
        $user['name'];


    $_SESSION['username'] =
        (string)
        $user['username'];


    $_SESSION['email'] =
        (string)
        (
            $user['email']
            ??
            ''
        );


    $_SESSION['role'] =
        (string)
        $user['role'];
}


/*
|--------------------------------------------------------------------------
| Logout User
|--------------------------------------------------------------------------
*/

function logoutUser(): void {

    $_SESSION = [];


    if (
        ini_get(
            'session.use_cookies'
        )
    ) {

        $params =
            session_get_cookie_params();


        setcookie(

            session_name(),

            '',

            time() - 42000,

            $params['path'],

            $params['domain'],

            (bool)
            $params['secure'],

            (bool)
            $params['httponly']

        );
    }


    session_destroy();
}


/*
|--------------------------------------------------------------------------
| Role Helpers
|--------------------------------------------------------------------------
*/

function isAdmin(): bool {

    return
        isLoggedIn()
        &&
        (
            $_SESSION['role']
            ??
            ''
        )
        ===
        'admin';
}


function isStaff(): bool {

    return
        isLoggedIn()
        &&
        (
            $_SESSION['role']
            ??
            ''
        )
        ===
        'staff';
}


/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function requireLogin(): void {

    if (!isLoggedIn()) {

        redirectTo(
            'login.php'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Require Specific Role
|--------------------------------------------------------------------------
*/

function requireRole(
    string|array $roles
): void {

    requireLogin();


    $allowedRoles =
        (array)
        $roles;


    $currentRole =
        (string)
        (
            $_SESSION['role']
            ??
            ''
        );


    if (
        !in_array(
            $currentRole,
            $allowedRoles,
            true
        )
    ) {

        http_response_code(
            403
        );


        exit(
            'Access denied.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/

function csrfToken(): string {

    if (
        empty(
            $_SESSION['csrf_token']
        )
    ) {

        $_SESSION['csrf_token'] =
            bin2hex(
                random_bytes(32)
            );
    }


    return
        (string)
        $_SESSION['csrf_token'];
}


/*
|--------------------------------------------------------------------------
| Verify CSRF Token
|--------------------------------------------------------------------------
*/

function verifyCsrfToken(
    ?string $token
): bool {

    if (
        $token === null
        ||
        empty(
            $_SESSION['csrf_token']
        )
    ) {

        return false;
    }


    return hash_equals(

        (string)
        $_SESSION['csrf_token'],

        $token

    );
}


/*
|--------------------------------------------------------------------------
| Protect Private Pages
|--------------------------------------------------------------------------
*/

function protectCurrentPage(): void {

    if (
        PHP_SAPI === 'cli'
    ) {

        return;
    }


    $currentPage =
        basename(
            (string)
            (
                $_SERVER['SCRIPT_NAME']
                ??
                ''
            )
        );


    if (
        in_array(
            $currentPage,
            PUBLIC_PAGES,
            true
        )
    ) {

        return;
    }


    requireLogin();
}