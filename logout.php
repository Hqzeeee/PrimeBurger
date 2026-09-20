<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

if (Auth::check()) {
    (new AuthController())->logout();
}

header('Location: ' . APP_URL . '/login.php');
exit;
