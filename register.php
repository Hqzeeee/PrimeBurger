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

$oldFullName = '';
$oldUsername = '';


/*
|--------------------------------------------------------------------------
| Handle Registration Submission
|--------------------------------------------------------------------------
|
| New accounts created here are always registered with the 'staff'
| role. Owner/admin accounts are managed separately (see
| database/seed.sql) and are not created through this public form.
|
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    $fullName        = trim((string) ($_POST['full_name'] ?? ''));
    $username        = trim((string) ($_POST['username'] ?? ''));
    $password        = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    $oldFullName = $fullName;
    $oldUsername = $username;


    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $errors[] = 'Your session has expired. Please try again.';

    }

    if ($fullName === '') {
        $errors[] = 'Please enter your full name.';
    }

    if ($username === '') {

        $errors[] = 'Please enter a username.';

    } elseif (!preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $username)) {

        $errors[] = 'Username must be 3-50 characters and may only contain letters, numbers, dots and underscores.';

    }

    if (strlen($password) < 8) {

        $errors[] = 'Password must be at least 8 characters long.';

    } elseif ($password !== $confirmPassword) {

        $errors[] = 'Passwords do not match.';

    }


    if (empty($errors)) {

        try {

            $checkStatement = $pdo->prepare(
                "
                    SELECT user_id
                    FROM users
                    WHERE username = :username
                    LIMIT 1
                "
            );

            $checkStatement->execute([
                'username' => $username,
            ]);

            if ($checkStatement->fetch()) {

                $errors[] = 'That username is already taken.';

            } else {

                $insertStatement = $pdo->prepare(
                    "
                        INSERT INTO users
                            (username, password_hash, full_name, role)
                        VALUES
                            (:username, :password_hash, :full_name, 'staff')
                    "
                );

                $insertStatement->execute([
                    'username'      => $username,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'full_name'     => $fullName,
                ]);

                header('Location: login.php?registered=1');

                exit;

            }

        } catch (PDOException $exception) {

            $errors[] = 'Unable to create your account right now. Please try again later.';

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
        content="Create an employee account for PrimeBurger Inventory"
    >

    <title>
        Create Account | PrimeBurger Inventory
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
        py-10
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


        <!-- REGISTER CARD -->

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
                Create an employee account
            </h2>

            <p class="mb-6 text-sm text-slate-500">
                For PrimeBurger staff who need access to the inventory
                system.
            </p>


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


            <form method="post" action="register.php" class="space-y-4">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                >

                <div>

                    <label
                        for="full_name"
                        class="mb-1.5 block text-sm font-medium text-slate-700"
                    >
                        Full name
                    </label>

                    <input
                        id="full_name"
                        name="full_name"
                        type="text"
                        autocomplete="name"
                        required
                        value="<?= htmlspecialchars($oldFullName, ENT_QUOTES, 'UTF-8') ?>"
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

                    <p class="mt-1.5 text-xs text-slate-400">
                        3-50 characters: letters, numbers, dots or underscores.
                    </p>

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
                        autocomplete="new-password"
                        required
                        minlength="8"
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

                    <p class="mt-1.5 text-xs text-slate-400">
                        At least 8 characters.
                    </p>

                </div>


                <div>

                    <label
                        for="confirm_password"
                        class="mb-1.5 block text-sm font-medium text-slate-700"
                    >
                        Confirm password
                    </label>

                    <input
                        id="confirm_password"
                        name="confirm_password"
                        type="password"
                        autocomplete="new-password"
                        required
                        minlength="8"
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
                    Create account
                </button>

            </form>


            <p class="mt-5 text-center text-sm text-slate-500">
                Already have an account?
                <a
                    href="login.php"
                    class="font-semibold text-red-700 hover:text-red-800"
                >
                    Sign in
                </a>
            </p>

        </div>


        <p class="mt-6 text-center text-xs text-slate-400">
            New accounts are created with staff-level access.
        </p>


    </div>


</body>

</html>
