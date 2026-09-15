<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| Application Information
|--------------------------------------------------------------------------
|
| Dito mo lang babaguhin ang application name, logo, at base URL.
|
*/

const APP_NAME = 'PrimeBurger';

const APP_SUBTITLE = 'Inventory System';


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
|
| Since nasa:
|
| C:\xampp\htdocs\PrimeBurger
|
| ang project mo, ang URL niya ay:
|
| http://localhost/PrimeBurger
|
*/

const BASE_URL = '/PrimeBurger';


/*
|--------------------------------------------------------------------------
| Logo
|--------------------------------------------------------------------------
|
| Kapag gusto mong palitan ang logo:
|
| Option 1:
| Palitan lang ang file:
| assets/images/primeburger-logo.png
|
| Option 2:
| Palitan itong path.
|
*/

const APP_LOGO =
    BASE_URL . '/assets/images/primeburger-logo.png';


/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
|
| Isang beses lang natin ide-define dito ang menu.
|
| Lahat ng modules automatically gagamit nito.
|
*/

const NAV_ITEMS = [

    [
        'key' => 'dashboard',
        'label' => 'Dashboard',
        'url' => BASE_URL . '/dashboard.php',
    ],

    [
        'key' => 'products',
        'label' => 'Products',
        'url' => '#',
    ],

    [
        'key' => 'inventory',
        'label' => 'Inventory',
        'url' => '#',
    ],

    [
        'key' => 'suppliers',
        'label' => 'Suppliers',
        'url' => '#',
    ],

    [
        'key' => 'reports',
        'label' => 'Reports',
        'url' => '#',
    ],

    [
        'key' => 'qr-scanner',
        'label' => 'QR Scanner',
        'url' => BASE_URL . '/qr-scanner.php',
    ],

];