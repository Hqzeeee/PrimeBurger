<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';


/*
|--------------------------------------------------------------------------
| Redirect Already-Logged-In Users
|--------------------------------------------------------------------------
*/

if (isLoggedIn()) {

    header('Location: dashboard.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$errors = [];
$oldUsername = '';

$justRegistered = isset($_GET['registered']);


/*
|--------------------------------------------------------------------------
| Handle Login Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $oldUsername = $username;


    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $errors[] = 'Your session has expired. Please try again.';

    } elseif ($username === '' || $password === '') {

        $errors[] = 'Please enter both username and password.';

    } else {

        try {

            $statement = $pdo->prepare(
                "
                    SELECT user_id, username, password_hash, full_name, role
                    FROM users
                    WHERE username = :username
                    LIMIT 1
                "
            );

            $statement->execute([
                'username' => $username,
            ]);

            $user = $statement->fetch();

            if (
                $user
                && password_verify($password, $user['password_hash'])
            ) {

                session_regenerate_id(true);

                $_SESSION['user_id']   = (int) $user['user_id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                header('Location: dashboard.php');

                exit;

            }

            $errors[] = 'Invalid username or password.';

        } catch (PDOException $exception) {

            $errors[] = 'Unable to sign in right now. Please try again later.';

        }

    }

}

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Sign in to PrimeBurger Inventory"
    >

    <title>
        Sign In | PrimeBurger Inventory
    </title>

    <link
        rel="stylesheet"
        href="assets/css/output.css"
    >

</head>


<body
    class="
        flex
        min-h-screen
        items-center
        justify-center
        bg-slate-50
        px-4
        text-slate-900
        antialiased
    "
>


    <div class="w-full max-w-sm">


        <!-- BRAND -->

        <div class="mb-8 flex flex-col items-center">

            <div
                class="
                    mb-4
                    flex
                    h-14
                    w-14
                    items-center
                    justify-center
                    rounded-2xl
                    bg-red-700
                    text-lg
                    font-bold
                    text-white
                "
            >
                PB
            </div>

            <h1 class="text-xl font-bold text-slate-900">
                PrimeBurger
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Inventory Management System
            </p>

        </div>


        <!-- LOGIN CARD -->

        <div
            class="
                rounded-2xl
                border
                border-slate-200
                bg-white
                p-7
                shadow-sm
            "
        >

            <h2 class="mb-1 text-lg font-semibold text-slate-900">
                Sign in
            </h2>

            <p class="mb-6 text-sm text-slate-500">
                Enter your credentials to access the dashboard.
            </p>


            <?php if ($justRegistered && empty($errors)): ?>

                <div
                    class="
                        mb-5
                        rounded-xl
                        border
                        border-emerald-200
                        bg-emerald-50
                        px-4
                        py-3
                        text-sm
                        text-emerald-700
                    "
                >
                    <p>Account created. You can now sign in.</p>
                </div>

            <?php endif; ?>


            <?php if (!empty($errors)): ?>

                <div
                    class="
                        mb-5
                        rounded-xl
                        border
                        border-red-200
                        bg-red-50
                        px-4
                        py-3
                        text-sm
                        text-red-700
                    "
                >

                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <form method="post" action="login.php" class="space-y-4">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                >

                <div>

                    <label
                        for="username"
                        class="mb-1.5 block text-sm font-medium text-slate-700"
                    >
                        Username
                    </label>

                    <input
                        id="username"
                        name="username"
                        type="text"
                        autocomplete="username"
                        required
                        value="<?= htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8') ?>"
                        class="
                            w-full
                            rounded-xl
                            border
                            border-slate-300
                            px-4
                            py-2.5
                            text-sm
                            text-slate-900
                            outline-none

                            focus:border-red-700
                            focus:ring-2
                            focus:ring-red-100
                        "
                    >

                </div>


                <div>

                    <label
                        for="password"
                        class="mb-1.5 block text-sm font-medium text-slate-700"
                    >
                        Password
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="
                            w-full
                            rounded-xl
                            border
                            border-slate-300
                            px-4
                            py-2.5
                            text-sm
                            text-slate-900
                            outline-none

                            focus:border-red-700
                            focus:ring-2
                            focus:ring-red-100
                        "
                    >

                </div>


                <button
                    type="submit"
                    class="
                        w-full
                        rounded-xl
                        bg-red-700
                        px-4
                        py-2.5
                        text-sm
                        font-semibold
                        text-white
                        transition

                        hover:bg-red-800
                    "
                >
                    Sign in
                </button>

            </form>


            <p class="mt-5 text-center text-sm text-slate-500">
                New employee?
                <a
                    href="register.php"
                    class="font-semibold text-red-700 hover:text-red-800"
                >
                    Create an account
                </a>
            </p>

        </div>


        <p class="mt-6 text-center text-xs text-slate-400">
            Default admin: <strong>admin</strong> / <strong>admin123</strong>
            &mdash; change this after first login.
        </p>


    </div>


</body>

</html>
