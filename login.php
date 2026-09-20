<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

if (Auth::check()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$error = null;
$timeout = isset($_GET['timeout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $error = $controller->login($_POST);

    if ($error === null) {
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login · PrimeBurger IMS</title>

    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">

    <link rel="icon"
          type="image/png"
          href="<?= APP_URL ?>/assets/images/primeburger-logo.png">
</head>

<body class="login-page">

<div class="login-container">

    <!-- LEFT BRANDING PANEL -->
    <div class="login-brand-panel">

        <div class="brand-content">

            <img
                src="<?= APP_URL ?>/assets/images/primeburger-logo.png"
                alt="PrimeBurger Logo"
                class="login-logo"
            >

            <div class="brand-divider"></div>

            <h1>PrimeBurger</h1>

            <p class="brand-tagline">
                Panalo ang lasa, Masarap kasama!
            </p>

            <div class="brand-description">
                <p>
                    <strong>Inventory Management System</strong>
                </p>

                <p>
                    Manage products, stock, users, reports,
                    and inventory transactions in one place.
                </p>
            </div>

        </div>

        <div class="brand-footer">
            PrimeBurger IMS
        </div>

    </div>


    <!-- RIGHT LOGIN PANEL -->
    <div class="login-form-panel">

        <div class="login-box">

            <div class="mobile-logo">
                <img
                    src="<?= APP_URL ?>/assets/images/primeburger-logo.png"
                    alt="PrimeBurger Logo"
                >
            </div>

            <div class="login-heading">
                <span class="welcome-text">WELCOME BACK</span>

                <h2>Sign in to your account</h2>

                <p>
                    Enter your credentials to access the inventory system.
                </p>
            </div>


            <?php if ($timeout): ?>

                <div class="login-alert login-alert-info">
                    <span>ⓘ</span>
                    <div>
                        You were logged out due to inactivity.
                    </div>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="login-alert login-alert-error">
                    <span>!</span>
                    <div>
                        <?= Validator::e($error) ?>
                    </div>
                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="<?= APP_URL ?>/login.php"
                class="login-form"
                novalidate
            >

                <?= Csrf::field() ?>


                <!-- USERNAME -->
                <div class="login-field">

                    <label for="username">
                        Username or Email
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.42 0-8 2.24-8 5v2h16v-2c0-2.76-3.58-5-8-5Z"/>
                            </svg>
                        </span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Enter your username"
                            required
                            autofocus
                            autocomplete="username"
                            value="<?= Validator::e($_POST['username'] ?? '') ?>"
                        >

                    </div>

                </div>


                <!-- PASSWORD -->
                <div class="login-field">

                    <div class="password-label-row">

                        <label for="password">
                            Password
                        </label>

                    </div>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2Zm-7-2a2 2 0 0 1 4 0v2h-4V6Zm5 11H9v-2h6v2Z"/>
                            </svg>
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword"
                            aria-label="Show password"
                        >
                            <svg
                                id="eyeIcon"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                            </svg>
                        </button>

                    </div>

                </div>


                <!-- LOGIN BUTTON -->
                <button
                    type="submit"
                    class="login-button"
                >
                    <span>Sign In</span>

                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m13 5 7 7-7 7-1.4-1.4 4.6-4.6H4v-2h11.2l-4.6-4.6L13 5Z"/>
                    </svg>
                </button>

            </form>


            <!-- DEFAULT ACCOUNTS -->
            <div class="demo-accounts">

                <div class="demo-title">
                    Default Accounts
                </div>

                <div class="demo-account">
                    <span class="demo-role">OWNER</span>
                    <span>
                        <strong>admin</strong>
                        <span class="demo-separator">/</span>
                        Password123!
                    </span>
                </div>

                <div class="demo-account">
                    <span class="demo-role staff">STAFF</span>
                    <span>
                        <strong>staff1</strong>
                        <span class="demo-separator">/</span>
                        Password123!
                    </span>
                </div>

            </div>


            <div class="login-copyright">
                © <?= date('Y') ?> PrimeBurger Inventory Management System
            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (password && togglePassword) {

        togglePassword.addEventListener('click', function () {

            const isPassword = password.type === 'password';

            password.type = isPassword ? 'text' : 'password';

            togglePassword.setAttribute(
                'aria-label',
                isPassword ? 'Hide password' : 'Show password'
            );

        });

    }

});
</script>

</body>
</html>