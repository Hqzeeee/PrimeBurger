<?php

declare(strict_types=1);


require_once __DIR__ . '/config/database.php';


/*
|--------------------------------------------------------------------------
| Redirect Already Logged In User
|--------------------------------------------------------------------------
*/

if (isLoggedIn()) {

    redirectTo(
        'dashboard.php'
    );

}


/*
|--------------------------------------------------------------------------
| Initial Values
|--------------------------------------------------------------------------
*/

$errors = [];

$username = '';


/*
|--------------------------------------------------------------------------
| Process Login
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD']
    ===
    'POST'
) {


    /*
    |--------------------------------------------------------------------------
    | Get Input
    |--------------------------------------------------------------------------
    */

    $username =
        trim(
            (string)
            (
                $_POST['username']
                ??
                ''
            )
        );


    $password =
        (string)
        (
            $_POST['password']
            ??
            ''
        );


    $csrfToken =
        isset(
            $_POST['csrf_token']
        )
        ?
        (string)
        $_POST['csrf_token']
        :
        null;



    /*
    |--------------------------------------------------------------------------
    | Validate CSRF
    |--------------------------------------------------------------------------
    */

    if (
        !verifyCsrfToken(
            $csrfToken
        )
    ) {

        $errors[] =
            'Your session expired. Please try again.';

    }



    /*
    |--------------------------------------------------------------------------
    | Validate Username
    |--------------------------------------------------------------------------
    */

    if (
        $username === ''
    ) {

        $errors[] =
            'Username is required.';

    }



    /*
    |--------------------------------------------------------------------------
    | Validate Password
    |--------------------------------------------------------------------------
    */

    if (
        $password === ''
    ) {

        $errors[] =
            'Password is required.';

    }



    /*
    |--------------------------------------------------------------------------
    | Authenticate
    |--------------------------------------------------------------------------
    */

    if (
        $errors === []
    ) {


        $statement =
            $pdo->prepare(
                "
                    SELECT

                        user_id,

                        name,

                        username,

                        email,

                        password_hash,

                        role,

                        is_active,

                        last_login_at

                    FROM users

                    WHERE
                        username = :username

                    LIMIT 1
                "
            );


        $statement->execute([

            'username' =>
                $username,

        ]);


        $user =
            $statement->fetch();



        /*
        |--------------------------------------------------------------------------
        | Check Account
        |--------------------------------------------------------------------------
        */

        $validLogin =

            $user

            &&

            (int)
            $user['is_active']
            ===
            1

            &&

            password_verify(

                $password,

                (string)
                $user['password_hash']

            );


        if (!$validLogin) {

            $errors[] =
                'Invalid username or password.';

        } else {


            /*
            |--------------------------------------------------------------------------
            | Create Session
            |--------------------------------------------------------------------------
            */

            loginUser(
                $user
            );



            /*
            |--------------------------------------------------------------------------
            | Update Last Login
            |--------------------------------------------------------------------------
            */

            $updateLastLogin =
                $pdo->prepare(
                    "
                        UPDATE users

                        SET
                            last_login_at = NOW()

                        WHERE
                            user_id = :user_id
                    "
                );


            $updateLastLogin->execute([

                'user_id' =>
                    (int)
                    $user['user_id'],

            ]);



            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            redirectTo(
                'dashboard.php'
            );

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

    <title>
        Log In | <?= htmlspecialchars(
            APP_NAME,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>


    <link
        rel="stylesheet"
        href="assets/css/output.css"
    >

</head>


<body
    class="
        min-h-screen
        bg-slate-100
        text-slate-900
        antialiased
    "
>


<div
    class="
        flex
        min-h-screen
        items-center
        justify-center
        px-4
        py-10
    "
>


    <div
        class="
            grid
            w-full
            max-w-5xl
            overflow-hidden
            rounded-3xl
            bg-white
            shadow-xl

            lg:grid-cols-2
        "
    >


        <!-- =====================================================
             LEFT BRAND PANEL
        ====================================================== -->

        <section
            class="
                hidden
                bg-zinc-950
                p-12
                text-white

                lg:flex
                lg:flex-col
                lg:justify-between
            "
        >


            <div>


                <!-- SAME LOGO AS DASHBOARD -->

                <div
                    class="
                        flex
                        h-20
                        w-20
                        items-center
                        justify-center
                        overflow-hidden
                        rounded-2xl
                    "
                >

                    <img
                        src="<?= htmlspecialchars(
                            APP_LOGO,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"

                        alt="<?= htmlspecialchars(
                            APP_NAME,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?> Logo"

                        class="
                            h-full
                            w-full
                            object-contain
                        "
                    >

                </div>


                <h1
                    class="
                        mt-8
                        text-4xl
                        font-bold
                        tracking-tight
                    "
                >
                    <?= htmlspecialchars(
                        APP_NAME,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h1>


                <p
                    class="
                        mt-2
                        text-sm
                        text-zinc-400
                    "
                >
                    <?= htmlspecialchars(
                        APP_SUBTITLE,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

            </div>


            <div>

                <p
                    class="
                        text-lg
                        font-semibold
                    "
                >
                    Manage inventory efficiently.
                </p>


                <p
                    class="
                        mt-2
                        max-w-sm
                        text-sm
                        leading-6
                        text-zinc-400
                    "
                >
                    Secure access for authorized
                    PrimeBurger Admin and Staff accounts.
                </p>

            </div>

        </section>



        <!-- =====================================================
             LOGIN FORM
        ====================================================== -->

        <main
            class="
                p-7
                sm:p-10
                lg:p-12
            "
        >


            <div
                class="
                    mx-auto
                    max-w-md
                "
            >


                <!-- MOBILE LOGO -->

                <div
                    class="
                        mb-7
                        flex
                        items-center
                        gap-3
                        lg:hidden
                    "
                >

                    <div
                        class="
                            h-14
                            w-14
                            overflow-hidden
                            rounded-xl
                        "
                    >

                        <img
                            src="<?= htmlspecialchars(
                                APP_LOGO,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"

                            alt="<?= htmlspecialchars(
                                APP_NAME,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?> Logo"

                            class="
                                h-full
                                w-full
                                object-contain
                            "
                        >

                    </div>


                    <div>

                        <p
                            class="
                                font-bold
                                text-slate-900
                            "
                        >
                            <?= htmlspecialchars(
                                APP_NAME,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>


                        <p
                            class="
                                text-xs
                                text-slate-500
                            "
                        >
                            <?= htmlspecialchars(
                                APP_SUBTITLE,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </div>

                </div>



                <p
                    class="
                        text-xs
                        font-bold
                        uppercase
                        tracking-[0.18em]
                        text-red-700
                    "
                >
                    Welcome Back
                </p>


                <h2
                    class="
                        mt-2
                        text-3xl
                        font-bold
                        tracking-tight
                    "
                >
                    Log in to your account
                </h2>


                <p
                    class="
                        mt-2
                        text-sm
                        text-slate-500
                    "
                >
                    Enter your username and password
                    to continue.
                </p>



                <!-- =================================================
                     ERRORS
                ================================================== -->

                <?php if (
                    $errors !== []
                ): ?>

                    <div
                        class="
                            mt-6
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

                        <ul
                            class="
                                list-disc
                                space-y-1
                                pl-5
                            "
                        >

                            <?php foreach (
                                $errors
                                as
                                $error
                            ): ?>

                                <li>

                                    <?= htmlspecialchars(
                                        $error,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>



                <!-- =================================================
                     LOGIN FORM
                ================================================== -->

                <form
                    method="POST"

                    class="
                        mt-7
                        space-y-5
                    "

                    autocomplete="on"
                >


                    <input
                        type="hidden"

                        name="csrf_token"

                        value="<?= htmlspecialchars(
                            csrfToken(),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >



                    <!-- USERNAME -->

                    <div>

                        <label
                            for="username"

                            class="
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Username
                        </label>


                        <input
                            id="username"

                            name="username"

                            type="text"

                            required

                            autofocus

                            maxlength="50"

                            autocomplete="username"

                            value="<?= htmlspecialchars(
                                $username,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"

                            placeholder="Enter username"

                            class="
                                mt-2
                                w-full
                                rounded-xl
                                border
                                border-slate-300
                                bg-white
                                px-4
                                py-3
                                text-sm
                                outline-none
                                transition

                                focus:border-red-600
                                focus:ring-2
                                focus:ring-red-100
                            "
                        >

                    </div>



                    <!-- PASSWORD -->

                    <div>

                        <label
                            for="password"

                            class="
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Password
                        </label>


                        <input
                            id="password"

                            name="password"

                            type="password"

                            required

                            autocomplete="current-password"

                            placeholder="Enter password"

                            class="
                                mt-2
                                w-full
                                rounded-xl
                                border
                                border-slate-300
                                bg-white
                                px-4
                                py-3
                                text-sm
                                outline-none
                                transition

                                focus:border-red-600
                                focus:ring-2
                                focus:ring-red-100
                            "
                        >

                    </div>



                    <!-- LOGIN BUTTON -->

                    <button
                        type="submit"

                        class="
                            w-full
                            rounded-xl
                            bg-red-700
                            px-5
                            py-3
                            text-sm
                            font-semibold
                            text-white
                            transition

                            hover:bg-red-800
                        "
                    >
                        Log In
                    </button>

                </form>



                <!-- SIGNUP -->

                <p
                    class="
                        mt-6
                        text-center
                        text-sm
                        text-slate-500
                    "
                >

                    Don't have an account?

                    <a
                        href="signup.php"

                        class="
                            font-semibold
                            text-red-700
                            hover:text-red-800
                        "
                    >
                        Create Staff Account
                    </a>

                </p>

            </div>

        </main>

    </div>

</div>


</body>

</html>