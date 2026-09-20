<?php
require_once __DIR__ . '/app/bootstrap.php';

header('Location: ' . (Auth::check() ? APP_URL . '/dashboard.php' : APP_URL . '/login.php'));
exit;
