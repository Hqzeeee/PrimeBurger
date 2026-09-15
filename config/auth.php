<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Auth Helpers
|--------------------------------------------------------------------------
|
| Small session-based authentication helpers shared by login.php,
| logout.php and any page that requires a logged-in user.
|
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {

        header('Location: login.php');

        exit;

    }
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'user_id'   => $_SESSION['user_id'],
        'username'  => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role'      => $_SESSION['role'],
    ];
}
