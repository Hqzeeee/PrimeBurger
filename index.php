<?php

declare(strict_types=1);

require_once __DIR__ . '/config/auth.php';


/*
|--------------------------------------------------------------------------
| PrimeBurger Entry Point
|--------------------------------------------------------------------------
*/

if (isLoggedIn()) {

    redirectTo(
        'dashboard.php'
    );
}


redirectTo(
    'login.php'
);