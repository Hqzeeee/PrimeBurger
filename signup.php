<?php

declare(strict_types=1);


require_once
    __DIR__
    .
    '/config/database.php';


/*
|--------------------------------------------------------------------------
| Redirect Logged In Users
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

$fullName = '';

$username = '';


/*
|--------------------------------------------------------------------------
| Process Signup
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD']
    ===
    'POST'
) {


    /*
    |--------------------------------------------------------------------------
    | Inputs
    |--------------------------------------------------------------------------
    */

    $fullName =
        trim(
            (string)
            (
                $_POST['full_name']
                ??
                ''
            )
        );


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


    $passwordConfirmation =
        (string)
        (
            $_POST['password_confirmation']
            ??
            ''
        );


    $csrf =
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
    | IMPORTANT:
    |
    | Public Signup Can Only Create STAFF Accounts
    |--------------------------------------------------------------------------
    |
    | We DO NOT read a role from $_POST.
    |
    | Even if someone manually sends:
    |
    | role=admin
    |
    | it will still be stored as STAFF.
    |
    */

    $role = 'staff';



    /*
    |--------------------------------------------------------------------------
    | CSRF Validation
    |--------------------------------------------------------------------------
    */

    if (
        !verifyCsrfToken(
            $csrf
        )
    ) {

        $errors[] =
            'Your session expired. Please try again.';

    }



    /*
    |--------------------------------------------------------------------------
    | Full Name Validation
    |--------------------------------------------------------------------------
    */

    if (
        $fullName === ''
        ||
        mb_strlen(
            $fullName
        )
        >
        100
    ) {

        $errors[] =
            'Please enter a valid full name.';

    }



    /*
    |--------------------------------------------------------------------------
    | Username Validation
    |--------------------------------------------------------------------------
    */

    if (
        !preg_match(
            '/^[A-Za-z0-9_]{3,50}$/',
            $username
        )
    ) {

        $errors[] =
            'Username must contain 3 to 50 characters using letters, numbers, or underscores only.';

    }



    /*
    |--------------------------------------------------------------------------
    | Password Validation
    |--------------------------------------------------------------------------
    */

    if (
        strlen(
            $password
        )
        <
        8
    ) {

        $errors[] =
            'Password must contain at least 8 characters.';

    }


    if (
        $password
        !==
        $passwordConfirmation
    ) {

        $errors[] =
            'Password confirmation does not match.';

    }



    /*
    |--------------------------------------------------------------------------
    | Check Existing Username
    |--------------------------------------------------------------------------
    */

    if ($errors === []) {


        $check =
            $pdo->prepare(
                "
                    SELECT
                        user_id

                    FROM users

                    WHERE
                        username = :username

                    LIMIT 1
                "
            );


        $check->execute([

            'username' =>
                $username,

        ]);


        if ($check->fetch()) {

            $errors[] =
                'That username is already registered.';

        }

    }



    /*
    |--------------------------------------------------------------------------
    | Create STAFF Account
    |--------------------------------------------------------------------------
    */

    if ($errors === []) {


        /*
        |--------------------------------------------------------------------------
        | Hash Password
        |--------------------------------------------------------------------------
        */

        $passwordHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        /*
        |--------------------------------------------------------------------------
        | Insert Account
        |--------------------------------------------------------------------------
        */

        $statement =
            $pdo->prepare(
                "
                    INSERT INTO users (

                        full_name,

                        username,

                        password_hash,

                        role,

                        is_active

                    )

                    VALUES (

                        :full_name,

                        :username,

                        :password_hash,

                        :role,

                        1

                    )
                "
            );


        $statement->execute([

            'full_name' =>
                $fullName,

            'username' =>
                $username,

            'password_hash' =>
                $passwordHash,

            /*
            |--------------------------------------------------------------------------
            | ALWAYS STAFF
            |--------------------------------------------------------------------------
            */

            'role' =>
                $role,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Automatically Login New Staff Account
        |--------------------------------------------------------------------------
        */

        $newUser = [

            'user_id' =>
                (int)
                $pdo->lastInsertId(),

            'full_name' =>
                $fullName,

            'username' =>
                $username,

            'role' =>
                'staff',

        ];


        loginUser(
            $newUser
        );


        /*
        |--------------------------------------------------------------------------
        | Redirect To Dashboard
        |--------------------------------------------------------------------------
        */

        redirectTo(
            'dashboard.php'
        );

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
        Sign Up | <?= htmlspecialchars(
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
             BRAND SIDE
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
                        h-20
                        w-20
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
                    Staff Account Registration
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
                    Accounts registered through this
                    page are automatically assigned
                    the Staff role.
                </p>

            </div>

        </section>



        <!-- =====================================================
             SIGNUP FORM
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

                        <p class="font-bold">

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
                    Staff Registration
                </p>


                <h2
                    class="
                        mt-2
                        text-3xl
                        font-bold
                        tracking-tight
                    "
                >
                    Create an account
                </h2>


                <p
                    class="
                        mt-2
                        text-sm
                        text-slate-500
                    "
                >
                    Register a new PrimeBurger
                    inventory staff account.
                </p>



                <!-- ROLE INFORMATION -->

                <div
                    class="
                        mt-5
                        flex
                        items-center
                        justify-between
                        rounded-xl
                        border
                        border-slate-200
                        bg-slate-50
                        px-4
                        py-3
                    "
                >

                    <div>

                        <p
                            class="
                                text-xs
                                font-medium
                                text-slate-500
                            "
                        >
                            Account Role
                        </p>

                        <p
                            class="
                                mt-0.5
                                text-sm
                                font-semibold
                                text-slate-800
                            "
                        >
                            Staff
                        </p>

                    </div>


                    <span
                        class="
                            rounded-full
                            bg-blue-50
                            px-3
                            py-1
                            text-xs
                            font-semibold
                            text-blue-700
                        "
                    >
                        Default
                    </span>

                </div>



                <!-- ERRORS -->

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



                <!-- FORM -->

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



                    <!-- FULL NAME -->

                    <div>

                        <label
                            for="full_name"

                            class="
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Full Name
                        </label>


                        <input
                            id="full_name"

                            name="full_name"

                            type="text"

                            required

                            maxlength="100"

                            autocomplete="name"

                            value="<?= htmlspecialchars(
                                $fullName,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"

                            placeholder="Enter full name"

                            class="
                                mt-2
                                w-full
                                rounded-xl
                                border
                                border-slate-300
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

                            maxlength="50"

                            autocomplete="username"

                            value="<?= htmlspecialchars(
                                $username,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"

                            placeholder="Choose a username"

                            class="
                                mt-2
                                w-full
                                rounded-xl
                                border
                                border-slate-300
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

                            minlength="8"

                            autocomplete="new-password"

                            placeholder="Minimum 8 characters"

                            class="
                                mt-2
                                w-full
                                rounded-xl
                                border
                                border-slate-300
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



                    <!-- CONFIRM PASSWORD -->

                    <div>

                        <label
                            for="password_confirmation"

                            class="
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Confirm Password
                        </label>


                        <input
                            id="password_confirmation"

                            name="password_confirmation"

                            type="password"

                            required

                            minlength="8"

                            autocomplete="new-password"

                            placeholder="Repeat password"

                            class="
                                mt-2
                                w-full
                                rounded-xl
                                border
                                border-slate-300
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
                        Create Staff Account
                    </button>

                </form>



                <p
                    class="
                        mt-6
                        text-center
                        text-sm
                        text-slate-500
                    "
                >

                    Already have an account?

                    <a
                        href="login.php"

                        class="
                            font-semibold
                            text-red-700
                            hover:text-red-800
                        "
                    >
                        Log In
                    </a>

                </p>

            </div>

        </main>

    </div>

</div>


</body>

</html>