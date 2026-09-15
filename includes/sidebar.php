<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';


$activePage =
    $activePage ?? '';

$pageTitle =
    $pageTitle ?? APP_NAME;


/*
|--------------------------------------------------------------------------
| Escape Helper
|--------------------------------------------------------------------------
*/

function sidebarEscape(
    string $value
): string {

    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );

}

?>


<!-- =========================================================
     DESKTOP SIDEBAR
========================================================== -->

<aside
    class="
        fixed
        inset-y-0
        left-0
        z-40
        hidden
        w-64
        flex-col
        bg-zinc-950
        lg:flex
    "
>


    <!-- =====================================================
         BRAND
    ====================================================== -->

    <div
        class="
            flex
            h-20
            items-center
            gap-3
            border-b
            border-zinc-800
            px-5
        "
    >

        <div
            class="
                flex
                h-12
                w-12
                shrink-0
                items-center
                justify-center
                overflow-hidden
                rounded-xl
            "
        >

            <img
                src="<?= sidebarEscape(APP_LOGO) ?>"
                alt="<?= sidebarEscape(APP_NAME) ?> Logo"
                class="
                    h-full
                    w-full
                    object-contain
                "
            >

        </div>


        <div class="min-w-0">

            <h1
                class="
                    truncate
                    text-base
                    font-bold
                    text-white
                "
            >
                <?= sidebarEscape(APP_NAME) ?>
            </h1>


            <p
                class="
                    truncate
                    text-xs
                    text-zinc-400
                "
            >
                <?= sidebarEscape(APP_SUBTITLE) ?>
            </p>

        </div>

    </div>



    <!-- =====================================================
         NAVIGATION
    ====================================================== -->

    <nav
        class="
            flex
            flex-1
            flex-col
            gap-1.5
            overflow-y-auto
            p-4
        "
    >

        <?php foreach (NAV_ITEMS as $item): ?>

            <?php

            $isActive =
                $activePage === $item['key'];

            ?>

            <a
                href="<?= sidebarEscape($item['url']) ?>"

                class="
                    rounded-xl
                    px-4
                    py-3
                    text-sm
                    transition

                    <?php if ($isActive): ?>

                        bg-red-700
                        font-semibold
                        text-white

                    <?php else: ?>

                        font-medium
                        text-zinc-400
                        hover:bg-zinc-900
                        hover:text-white

                    <?php endif; ?>
                "
            >

                <?= sidebarEscape($item['label']) ?>

            </a>

        <?php endforeach; ?>

    </nav>



    <!-- =====================================================
         USER PROFILE + LOGOUT
    ====================================================== -->

    <div
        class="
            border-t
            border-zinc-800
            p-4
        "
    >


        <!-- PROFILE -->

        <div
            class="
                rounded-xl
                bg-zinc-900
                p-4
            "
        >

            <div
                class="
                    flex
                    items-center
                    gap-3
                "
            >

                <div
                    class="
                        flex
                        h-9
                        w-9
                        shrink-0
                        items-center
                        justify-center
                        rounded-full
                        bg-red-700
                        text-sm
                        font-bold
                        text-white
                    "
                >
                    O
                </div>


                <div class="min-w-0">

                    <p
                        class="
                            truncate
                            text-sm
                            font-semibold
                            text-white
                        "
                    >
                        PrimeBurger Owner
                    </p>


                    <p
                        class="
                            mt-0.5
                            truncate
                            text-xs
                            text-zinc-500
                        "
                    >
                        Administrator
                    </p>

                </div>

            </div>

        </div>



        <!-- LOGOUT -->

        <a
            href="<?= BASE_URL ?>/logout.php"

            class="
                mt-3
                flex
                w-full
                items-center
                justify-center
                rounded-xl
                border
                border-zinc-800
                px-4
                py-3
                text-sm
                font-semibold
                text-zinc-400
                transition

                hover:border-red-900
                hover:bg-red-950
                hover:text-red-400
            "
        >
            Log Out
        </a>

    </div>


</aside>



<!-- =========================================================
     MOBILE HEADER
========================================================== -->

<header
    class="
        sticky
        top-0
        z-40
        border-b
        border-slate-200
        bg-white
        lg:hidden
    "
>

    <div
        class="
            flex
            items-center
            justify-between
            px-4
            py-3
        "
    >

        <a
            href="<?= BASE_URL ?>/dashboard.php"

            class="
                flex
                min-w-0
                items-center
                gap-3
                no-underline
            "
        >

            <div
                class="
                    flex
                    h-10
                    w-10
                    shrink-0
                    items-center
                    justify-center
                    overflow-hidden
                    rounded-lg
                "
            >

                <img
                    src="<?= sidebarEscape(APP_LOGO) ?>"
                    alt="<?= sidebarEscape(APP_NAME) ?> Logo"

                    class="
                        h-full
                        w-full
                        object-contain
                    "
                >

            </div>


            <div class="min-w-0">

                <p
                    class="
                        truncate
                        text-sm
                        font-bold
                        text-slate-900
                    "
                >
                    <?= sidebarEscape(APP_NAME) ?>
                </p>


                <p
                    class="
                        truncate
                        text-xs
                        text-slate-500
                    "
                >
                    <?= sidebarEscape($pageTitle) ?>
                </p>

            </div>

        </a>



        <!-- MOBILE MENU -->

        <details class="relative">

            <summary
                class="
                    cursor-pointer
                    list-none
                    rounded-lg
                    border
                    border-slate-200
                    bg-white
                    px-3
                    py-2
                    text-sm
                    font-semibold
                    text-slate-700
                "
            >
                Menu
            </summary>


            <div
                class="
                    absolute
                    right-0
                    mt-2
                    w-52
                    overflow-hidden
                    rounded-xl
                    border
                    border-slate-200
                    bg-white
                    p-2
                    shadow-lg
                "
            >

                <?php foreach (NAV_ITEMS as $item): ?>

                    <?php

                    $isActive =
                        $activePage === $item['key'];

                    ?>

                    <a
                        href="<?= sidebarEscape($item['url']) ?>"

                        class="
                            block
                            rounded-lg
                            px-3
                            py-2.5
                            text-sm

                            <?php if ($isActive): ?>

                                bg-red-50
                                font-semibold
                                text-red-700

                            <?php else: ?>

                                text-slate-600
                                hover:bg-slate-50
                                hover:text-slate-900

                            <?php endif; ?>
                        "
                    >

                        <?= sidebarEscape($item['label']) ?>

                    </a>

                <?php endforeach; ?>



                <!-- MOBILE LOGOUT -->

                <div
                    class="
                        my-2
                        border-t
                        border-slate-200
                    "
                >
                </div>


                <a
                    href="<?= BASE_URL ?>/logout.php"

                    class="
                        block
                        rounded-lg
                        px-3
                        py-2.5
                        text-sm
                        font-semibold
                        text-red-700
                        hover:bg-red-50
                    "
                >
                    Log Out
                </a>

            </div>

        </details>

    </div>

</header>