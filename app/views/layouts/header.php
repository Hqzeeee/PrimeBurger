<?php
/**
 * Shared header/layout shell.
 * Expects: $pageTitle (string). Auth::* must already be available (bootstrap.php loaded).
 */

$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = basename($_SERVER['SCRIPT_NAME']);

$notifService = new NotificationService();
$notifService->sync();
$unreadCount = $notifService->unreadCount();

function navClass(string $file, string $current): string
{
    return $file === $current ? 'nav-link active' : 'nav-link';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    <?= Validator::e($pageTitle) ?> · PrimeBurger IMS
</title>

<link
    rel="stylesheet"
    href="<?= APP_URL ?>/assets/css/app.css"
>

<link
    rel="icon"
    type="image/png"
    href="<?= APP_URL ?>/assets/images/primeburger-logo.png"
>

</head>


<body>

<div class="app-shell">


    <!-- =========================================================
         SIDEBAR
         ========================================================= -->

    <aside
        class="sidebar"
        id="sidebar"
    >

        <div class="sidebar-brand">

            <img
                src="<?= APP_URL ?>/assets/images/primeburger-logo.png"
                alt="PrimeBurger"
                class="sidebar-logo"
            >

            <div class="sidebar-brand-text">

                <strong>PrimeBurger</strong>

                <span>Inventory System</span>

            </div>

        </div>


        <nav>


            <!-- MAIN -->

            <div class="sidebar-section-label">
                Main
            </div>


            <a
                href="<?= APP_URL ?>/dashboard.php"
                class="<?= navClass('dashboard.php', $currentPage) ?>"
            >
                <span class="nav-icon">⌂</span>
                <span>Dashboard</span>
            </a>


            <a
                href="<?= APP_URL ?>/products.php"
                class="<?= navClass('products.php', $currentPage) ?>"
            >
                <span class="nav-icon">▣</span>
                <span>Products</span>
            </a>


            <a
                href="<?= APP_URL ?>/stock.php"
                class="<?= navClass('stock.php', $currentPage) ?>"
            >
                <span class="nav-icon">↻</span>
                <span>Stock Management</span>
            </a>


            <a
                href="<?= APP_URL ?>/qr-scanner.php"
                class="<?= navClass('qr-scanner.php', $currentPage) ?>"
            >
                <span class="nav-icon">▦</span>
                <span>QR Scanner</span>
            </a>


            <?php if (Auth::isOwner()): ?>

                <a
                    href="<?= APP_URL ?>/reports.php"
                    class="<?= navClass('reports.php', $currentPage) ?>"
                >
                    <span class="nav-icon">▤</span>
                    <span>Reports</span>
                </a>

            <?php endif; ?>


            <a
                href="<?= APP_URL ?>/notifications.php"
                class="<?= navClass('notifications.php', $currentPage) ?>"
            >

                <span class="nav-icon">●</span>

                <span>Notifications</span>

                <?php if ($unreadCount > 0): ?>

                    <span
                        class="badge badge-red"
                        style="margin-left:auto;"
                    >
                        <?= $unreadCount ?>
                    </span>

                <?php endif; ?>

            </a>


            <?php if (Auth::isOwner()): ?>

                <div class="sidebar-section-label">
                    Administration
                </div>


                <a
                    href="<?= APP_URL ?>/users.php"
                    class="<?= navClass('users.php', $currentPage) ?>"
                >
                    <span class="nav-icon">♙</span>
                    <span>Users</span>
                </a>

            <?php endif; ?>


            <div class="sidebar-section-label">
                System
            </div>


            <a
                href="<?= APP_URL ?>/settings.php"
                class="<?= navClass('settings.php', $currentPage) ?>"
            >
                <span class="nav-icon">⚙</span>
                <span>Settings</span>
            </a>


            <a
                href="<?= APP_URL ?>/logout.php"
                class="nav-link logout-link"
            >
                <span class="nav-icon">↪</span>
                <span>Logout</span>
            </a>


        </nav>

    </aside>


    <!-- =========================================================
         MAIN
         ========================================================= -->

    <div class="main">


        <!-- TOPBAR -->

        <header class="topbar">

            <div class="flex items-center gap-3">

                <button
                    class="menu-toggle"
                    aria-label="Toggle menu"
                >
                    ☰
                </button>

                <div>

                    <h1 class="topbar-title">
                        <?= Validator::e($pageTitle) ?>
                    </h1>

                    <div class="topbar-subtitle">
                        PrimeBurger Inventory Management System
                    </div>

                </div>

            </div>


            <div class="topbar-right">


                <!-- Notifications -->

                <a
                    href="<?= APP_URL ?>/notifications.php"
                    class="notif-btn"
                    title="Notifications"
                >

                    🔔

                    <?php if ($unreadCount > 0): ?>

                        <span class="notif-dot">
                            <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                        </span>

                    <?php endif; ?>

                </a>


                <!-- User -->

                <div class="user-chip">

                    <div class="user-avatar">

                        <?= strtoupper(
                            substr(
                                Auth::fullName(),
                                0,
                                1
                            )
                        ) ?>

                    </div>


                    <div>

                        <div class="user-name">

                            <?= Validator::e(
                                Auth::fullName()
                            ) ?>

                        </div>

                        <span class="role-badge">

                            <?= Validator::e(
                                Auth::role()
                            ) ?>

                        </span>

                    </div>

                </div>

            </div>

        </header>


        <main class="content">