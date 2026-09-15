<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';


/*
|--------------------------------------------------------------------------
| Dashboard Configuration
|--------------------------------------------------------------------------
|
| The requirements specify "near expiry" monitoring but do not specify
| the exact number of days. For now, we use 7 days.
|
| This can easily be changed later.
|
*/

const NEAR_EXPIRY_DAYS = 7;


/*
|--------------------------------------------------------------------------
| Helper Function
|--------------------------------------------------------------------------
*/

function getDashboardCount(
    PDO $pdo,
    string $sql
): int {

    try {

        $statement = $pdo->query($sql);

        return (int) $statement->fetchColumn();

    } catch (PDOException $exception) {

        return 0;

    }

}


/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/


$totalProducts = getDashboardCount(
    $pdo,
    "
        SELECT COUNT(*)
        FROM products
    "
);


$totalStock = getDashboardCount(
    $pdo,
    "
        SELECT COALESCE(
            SUM(quantity),
            0
        )
        FROM products
    "
);


$lowStock = getDashboardCount(
    $pdo,
    "
        SELECT COUNT(*)
        FROM products
        WHERE quantity <= reorder_level
    "
);


$expiredProducts = getDashboardCount(
    $pdo,
    "
        SELECT COUNT(*)
        FROM products
        WHERE expiry_date IS NOT NULL
        AND expiry_date < CURDATE()
    "
);


$nearExpiry = getDashboardCount(
    $pdo,
    "
        SELECT COUNT(*)
        FROM products

        WHERE expiry_date IS NOT NULL

        AND expiry_date >= CURDATE()

        AND expiry_date <= DATE_ADD(
            CURDATE(),
            INTERVAL " . NEAR_EXPIRY_DAYS . " DAY
        )
    "
);


/*
|--------------------------------------------------------------------------
| Products Requiring Attention
|--------------------------------------------------------------------------
*/

$attentionSql = "
    SELECT
        product_id,
        name,
        quantity,
        unit,
        expiry_date,
        reorder_level

    FROM products

    WHERE

        quantity <= reorder_level

        OR expiry_date < CURDATE()

        OR (
            expiry_date >= CURDATE()

            AND expiry_date <= DATE_ADD(
                CURDATE(),
                INTERVAL " . NEAR_EXPIRY_DAYS . " DAY
            )
        )

    ORDER BY

        CASE

            WHEN expiry_date < CURDATE()
                THEN 1

            WHEN quantity <= reorder_level
                THEN 2

            ELSE 3

        END,

        expiry_date ASC,

        name ASC

    LIMIT 8
";


try {

    $attentionStatement =
        $pdo->query($attentionSql);

    $attentionProducts =
        $attentionStatement->fetchAll();

} catch (PDOException $exception) {

    $attentionProducts = [];

}


/*
|--------------------------------------------------------------------------
| Recent Inventory Activity
|--------------------------------------------------------------------------
*/

$activitySql = "
    SELECT
        inventory_logs.log_id,
        inventory_logs.log_type,
        inventory_logs.quantity,
        inventory_logs.description,
        inventory_logs.created_at,

        products.name AS product_name,
        products.unit

    FROM inventory_logs

    INNER JOIN products
        ON products.product_id =
           inventory_logs.product_id

    ORDER BY
        inventory_logs.created_at DESC

    LIMIT 6
";


try {

    $activityStatement =
        $pdo->query($activitySql);

    $recentActivities =
        $activityStatement->fetchAll();

} catch (PDOException $exception) {

    $recentActivities = [];

}


/*
|--------------------------------------------------------------------------
| Small Helpers
|--------------------------------------------------------------------------
*/

function formatDate(
    ?string $date
): string {

    if (!$date) {
        return '—';
    }

    return date(
        'M d, Y',
        strtotime($date)
    );

}


function formatActivityDate(
    string $date
): string {

    $timestamp = strtotime($date);

    $today =
        date('Y-m-d');

    $activityDate =
        date('Y-m-d', $timestamp);


    if ($activityDate === $today) {

        return date(
            'g:i A',
            $timestamp
        );

    }

    return date(
        'M d, g:i A',
        $timestamp
    );

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
        content="PrimeBurger Inventory Management Dashboard"
    >

    <title>
        Dashboard | PrimeBurger Inventory
    </title>

    <link
        rel="stylesheet"
        href="assets/css/output.css"
    >
    <link rel="stylesheet" href="assets/css/output.css">

</head>


<body
    class="
        bg-slate-50
        text-slate-900
        antialiased
    "
>


<div class="min-h-screen">


    <!-- ========================================================= -->
    <!-- SIDEBAR -->
    <!-- ========================================================= -->


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


        <!-- BRAND -->

        <div
            class="
                flex
                h-20
                items-center
                gap-3
                border-b
                border-zinc-800
                px-6
            "
        >

            <div
                class="
                    flex
                    h-11
                    w-11
                    items-center
                    justify-center
                    rounded-xl
                    bg-red-700
                    text-sm
                    font-bold
                    text-white
                "
            >
                PB
            </div>


            <div>

                <h1
                    class="
                        text-base
                        font-bold
                        text-white
                    "
                >
                    PrimeBurger
                </h1>

                <p
                    class="
                        text-xs
                        text-zinc-400
                    "
                >
                    Inventory System
                </p>

            </div>

        </div>



        <!-- NAVIGATION -->

        <nav
            class="
                flex
                flex-1
                flex-col
                gap-1.5
                p-4
            "
        >


            <a
                href="dashboard.php"
                class="
                    rounded-xl
                    bg-red-700
                    px-4
                    py-3
                    text-sm
                    font-semibold
                    text-white
                "
            >
                Dashboard
            </a>


            <a
                href="#"
                class="
                    rounded-xl
                    px-4
                    py-3
                    text-sm
                    font-medium
                    text-zinc-400
                    transition

                    hover:bg-zinc-900
                    hover:text-white
                "
            >
                Products
            </a>


            <a
                href="#"
                class="
                    rounded-xl
                    px-4
                    py-3
                    text-sm
                    font-medium
                    text-zinc-400
                    transition

                    hover:bg-zinc-900
                    hover:text-white
                "
            >
                Inventory
            </a>


            <a
                href="#"
                class="
                    rounded-xl
                    px-4
                    py-3
                    text-sm
                    font-medium
                    text-zinc-400
                    transition

                    hover:bg-zinc-900
                    hover:text-white
                "
            >
                Suppliers
            </a>


            <a
                href="#"
                class="
                    rounded-xl
                    px-4
                    py-3
                    text-sm
                    font-medium
                    text-zinc-400
                    transition

                    hover:bg-zinc-900
                    hover:text-white
                "
            >
                Reports
            </a>

            <a
                href="qr-scanner.php"
                class="
                    rounded-xl
                    px-4
                    py-3
                    text-sm
                    font-medium
                    text-zinc-400
                    transition

                    hover:bg-zinc-900
                    hover:text-white
                "
            >
                QR Scanner
            </a>


        </nav>



        <!-- SIDEBAR FOOTER -->

        <div
            class="
                border-t
                border-zinc-800
                p-4
            "
        >

            <div
                class="
                    rounded-xl
                    bg-zinc-900
                    p-4
                "
            >

                <p
                    class="
                        text-sm
                        font-semibold
                        text-white
                    "
                >
                    PrimeBurger Owner
                </p>

                <p
                    class="
                        mt-1
                        text-xs
                        text-zinc-500
                    "
                >
                    Administrator
                </p>

            </div>

        </div>


    </aside>



    <!-- ========================================================= -->
    <!-- MAIN -->
    <!-- ========================================================= -->


    <main
        class="
            min-h-screen
            lg:ml-64
        "
    >


        <!-- MOBILE HEADER -->

        <div
            class="
                border-b
                border-slate-200
                bg-white
                px-5
                py-4
                lg:hidden
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
                        h-10
                        w-10
                        items-center
                        justify-center
                        rounded-xl
                        bg-red-700
                        text-xs
                        font-bold
                        text-white
                    "
                >
                    PB
                </div>


                <div>

                    <p
                        class="
                            text-sm
                            font-bold
                        "
                    >
                        PrimeBurger
                    </p>

                    <p
                        class="
                            text-xs
                            text-slate-500
                        "
                    >
                        Inventory System
                    </p>

                </div>

            </div>

        </div>



        <div
            class="
                mx-auto
                max-w-[1600px]
                p-5
                md:p-8
            "
        >


            <!-- ================================================= -->
            <!-- PAGE HEADER -->
            <!-- ================================================= -->


            <header
                class="
                    mb-8
                    flex
                    flex-col
                    gap-5

                    md:flex-row
                    md:items-center
                    md:justify-between
                "
            >


                <div>

                    <p
                        class="
                            mb-2
                            text-xs
                            font-bold
                            uppercase
                            tracking-[0.18em]
                            text-red-700
                        "
                    >
                        Inventory Overview
                    </p>


                    <h2
                        class="
                            text-3xl
                            font-bold
                            tracking-tight
                            text-slate-900

                            md:text-4xl
                        "
                    >
                        Dashboard
                    </h2>


                    <p
                        class="
                            mt-2
                            text-sm
                            text-slate-500
                        "
                    >
                        Monitor inventory levels,
                        expiration status and recent
                        stock activity.
                    </p>

                </div>



                <!-- OWNER -->

                <div
                    class="
                        flex
                        items-center
                        gap-3
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        px-4
                        py-3
                        shadow-sm
                    "
                >

                    <div
                        class="
                            flex
                            h-11
                            w-11
                            items-center
                            justify-center
                            rounded-full
                            bg-red-700
                            font-bold
                            text-white
                        "
                    >
                        O
                    </div>


                    <div>

                        <p
                            class="
                                text-sm
                                font-semibold
                                text-slate-900
                            "
                        >
                            Owner
                        </p>


                        <p
                            class="
                                mt-0.5
                                text-xs
                                text-slate-500
                            "
                        >
                            Administrator
                        </p>

                    </div>

                </div>


            </header>



            <!-- ================================================= -->
            <!-- DASHBOARD SUMMARY -->
            <!-- ================================================= -->


            <section
                class="
                    mb-6
                    grid
                    grid-cols-1
                    gap-4

                    sm:grid-cols-2
                    xl:grid-cols-5
                "
            >


                <!-- TOTAL PRODUCTS -->

                <article
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        p-5
                        shadow-sm
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            justify-between
                        "
                    >

                        <div>

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Total Products
                            </p>


                            <p
                                class="
                                    mt-3
                                    text-3xl
                                    font-bold
                                    tracking-tight
                                "
                            >

                                <?= number_format(
                                    $totalProducts
                                ) ?>

                            </p>

                        </div>


                        <div
                            class="
                                rounded-xl
                                bg-slate-100
                                p-2.5
                                text-slate-600
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"
                                />
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="M12 22V12"/>
                            </svg>

                        </div>

                    </div>


                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                        "
                    >
                        Registered products
                    </p>

                </article>



                <!-- AVAILABLE STOCK -->

                <article
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        p-5
                        shadow-sm
                    "
                >

                    <p
                        class="
                            text-sm
                            font-medium
                            text-slate-500
                        "
                    >
                        Available Stock
                    </p>


                    <p
                        class="
                            mt-3
                            text-3xl
                            font-bold
                            tracking-tight
                        "
                    >

                        <?= number_format(
                            $totalStock
                        ) ?>

                    </p>


                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                        "
                    >
                        Total inventory quantity
                    </p>

                </article>



                <!-- LOW STOCK -->

                <article
                    class="
                        rounded-2xl
                        border
                        border-amber-200
                        bg-white
                        p-5
                        shadow-sm
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            justify-between
                        "
                    >

                        <div>

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Low Stock
                            </p>


                            <p
                                class="
                                    mt-3
                                    text-3xl
                                    font-bold
                                "
                            >

                                <?= number_format(
                                    $lowStock
                                ) ?>

                            </p>

                        </div>


                        <span
                            class="
                                rounded-full
                                bg-amber-50
                                px-2.5
                                py-1
                                text-[11px]
                                font-semibold
                                text-amber-700
                            "
                        >
                            Warning
                        </span>

                    </div>


                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                        "
                    >
                        Products needing restock
                    </p>

                </article>



                <!-- NEAR EXPIRY -->

                <article
                    class="
                        rounded-2xl
                        border
                        border-orange-200
                        bg-white
                        p-5
                        shadow-sm
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            justify-between
                        "
                    >

                        <div>

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Near Expiry
                            </p>


                            <p
                                class="
                                    mt-3
                                    text-3xl
                                    font-bold
                                "
                            >

                                <?= number_format(
                                    $nearExpiry
                                ) ?>

                            </p>

                        </div>


                        <span
                            class="
                                rounded-full
                                bg-orange-50
                                px-2.5
                                py-1
                                text-[11px]
                                font-semibold
                                text-orange-700
                            "
                        >
                            Soon
                        </span>

                    </div>


                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                        "
                    >
                        Within
                        <?= NEAR_EXPIRY_DAYS ?>
                        days
                    </p>

                </article>



                <!-- EXPIRED -->

                <article
                    class="
                        rounded-2xl
                        border
                        border-red-200
                        bg-white
                        p-5
                        shadow-sm
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            justify-between
                        "
                    >

                        <div>

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-500
                                "
                            >
                                Expired
                            </p>


                            <p
                                class="
                                    mt-3
                                    text-3xl
                                    font-bold
                                "
                            >

                                <?= number_format(
                                    $expiredProducts
                                ) ?>

                            </p>

                        </div>


                        <span
                            class="
                                rounded-full
                                bg-red-50
                                px-2.5
                                py-1
                                text-[11px]
                                font-semibold
                                text-red-700
                            "
                        >
                            Action
                        </span>

                    </div>


                    <p
                        class="
                            mt-2
                            text-xs
                            text-slate-500
                        "
                    >
                        Requires attention
                    </p>

                </article>


            </section>



            <!-- ================================================= -->
            <!-- LOWER SECTION -->
            <!-- ================================================= -->


            <section
                class="
                    grid
                    grid-cols-1
                    gap-6

                    xl:grid-cols-3
                "
            >


                <!-- ============================================= -->
                <!-- ATTENTION TABLE -->
                <!-- ============================================= -->


                <div
                    class="
                        overflow-hidden
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        shadow-sm

                        xl:col-span-2
                    "
                >


                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            border-b
                            border-slate-200
                            px-6
                            py-5
                        "
                    >

                        <div>

                            <h3
                                class="
                                    font-semibold
                                    text-slate-900
                                "
                            >
                                Products Requiring Attention
                            </h3>


                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-slate-500
                                "
                            >
                                Low-stock and expiration alerts
                            </p>

                        </div>

                    </div>



                    <?php if (
                        empty($attentionProducts)
                    ): ?>


                        <!-- EMPTY STATE -->

                        <div
                            class="
                                flex
                                min-h-72
                                flex-col
                                items-center
                                justify-center
                                px-6
                                py-12
                                text-center
                            "
                        >

                            <div
                                class="
                                    mb-4
                                    flex
                                    h-14
                                    w-14
                                    items-center
                                    justify-center
                                    rounded-2xl
                                    bg-slate-100
                                    text-slate-400
                                "
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="26"
                                    height="26"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"
                                    />
                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                    <path d="M12 22V12"/>
                                </svg>

                            </div>


                            <h4
                                class="
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                No products require attention
                            </h4>


                            <p
                                class="
                                    mt-2
                                    max-w-sm
                                    text-sm
                                    text-slate-500
                                "
                            >
                                Low-stock, near-expiry and
                                expired products will
                                automatically appear here.
                            </p>

                        </div>


                    <?php else: ?>


                        <div class="overflow-x-auto">


                            <table
                                class="
                                    min-w-full
                                    divide-y
                                    divide-slate-200
                                "
                            >


                                <thead class="bg-slate-50">


                                <tr>

                                    <th
                                        class="
                                            px-6
                                            py-3.5
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wide
                                            text-slate-500
                                        "
                                    >
                                        Product
                                    </th>


                                    <th
                                        class="
                                            px-6
                                            py-3.5
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wide
                                            text-slate-500
                                        "
                                    >
                                        Quantity
                                    </th>


                                    <th
                                        class="
                                            px-6
                                            py-3.5
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wide
                                            text-slate-500
                                        "
                                    >
                                        Expiration
                                    </th>


                                    <th
                                        class="
                                            px-6
                                            py-3.5
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wide
                                            text-slate-500
                                        "
                                    >
                                        Status
                                    </th>

                                </tr>


                                </thead>



                                <tbody
                                    class="
                                        divide-y
                                        divide-slate-100
                                    "
                                >


                                <?php foreach (
                                    $attentionProducts
                                    as $product
                                ): ?>


                                    <?php

                                    $quantity =
                                        (int)
                                        $product['quantity'];

                                    $reorderLevel =
                                        (int)
                                        $product['reorder_level'];

                                    $expiryDate =
                                        $product['expiry_date'];


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Determine Product Status
                                    |--------------------------------------------------------------------------
                                    */

                                    if (
                                        $expiryDate !== null
                                        &&
                                        strtotime($expiryDate)
                                        < strtotime(
                                            date('Y-m-d')
                                        )
                                    ) {

                                        $status =
                                            'Expired';

                                        $statusClasses =
                                            'bg-red-50 text-red-700';

                                    } elseif (
                                        $quantity
                                        <=
                                        $reorderLevel
                                    ) {

                                        $status =
                                            'Low Stock';

                                        $statusClasses =
                                            'bg-amber-50 text-amber-700';

                                    } else {

                                        $status =
                                            'Near Expiry';

                                        $statusClasses =
                                            'bg-orange-50 text-orange-700';

                                    }

                                    ?>


                                    <tr
                                        class="
                                            transition
                                            hover:bg-slate-50
                                        "
                                    >


                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-6
                                                py-4
                                            "
                                        >

                                            <p
                                                class="
                                                    text-sm
                                                    font-semibold
                                                    text-slate-900
                                                "
                                            >

                                                <?= htmlspecialchars(
                                                    $product['name'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </p>

                                        </td>



                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-6
                                                py-4
                                                text-sm
                                                text-slate-600
                                            "
                                        >

                                            <?= number_format(
                                                $quantity
                                            ) ?>

                                            <?= htmlspecialchars(
                                                $product['unit'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </td>



                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-6
                                                py-4
                                                text-sm
                                                text-slate-600
                                            "
                                        >

                                            <?= formatDate(
                                                $expiryDate
                                            ) ?>

                                        </td>



                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-6
                                                py-4
                                            "
                                        >

                                            <span
                                                class="
                                                    inline-flex
                                                    rounded-full
                                                    px-2.5
                                                    py-1
                                                    text-xs
                                                    font-semibold

                                                    <?= $statusClasses ?>
                                                "
                                            >

                                                <?= $status ?>

                                            </span>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php endif; ?>


                </div>



                <!-- ============================================= -->
                <!-- RECENT ACTIVITY -->
                <!-- ============================================= -->


                <div
                    class="
                        overflow-hidden
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        shadow-sm
                    "
                >


                    <div
                        class="
                            border-b
                            border-slate-200
                            px-6
                            py-5
                        "
                    >

                        <h3
                            class="
                                font-semibold
                                text-slate-900
                            "
                        >
                            Recent Activity
                        </h3>


                        <p
                            class="
                                mt-1
                                text-xs
                                text-slate-500
                            "
                        >
                            Latest inventory updates
                        </p>

                    </div>



                    <?php if (
                        empty($recentActivities)
                    ): ?>


                        <div
                            class="
                                flex
                                min-h-72
                                flex-col
                                items-center
                                justify-center
                                px-6
                                py-12
                                text-center
                            "
                        >

                            <div
                                class="
                                    mb-4
                                    flex
                                    h-14
                                    w-14
                                    items-center
                                    justify-center
                                    rounded-2xl
                                    bg-slate-100
                                    text-slate-400
                                "
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="26"
                                    height="26"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M12 8v4l3 3"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                    />
                                </svg>

                            </div>


                            <h4
                                class="
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                No inventory activity yet
                            </h4>


                            <p
                                class="
                                    mt-2
                                    text-sm
                                    text-slate-500
                                "
                            >
                                Stock movements will
                                appear here.
                            </p>

                        </div>


                    <?php else: ?>


                        <div
                            class="
                                divide-y
                                divide-slate-100
                                px-5
                            "
                        >


                            <?php foreach (
                                $recentActivities
                                as $activity
                            ): ?>


                                <?php

                                $isStockIn =
                                    $activity['log_type']
                                    === 'stock_in';

                                $isStockOut =
                                    $activity['log_type']
                                    === 'stock_out';


                                if ($isStockIn) {

                                    $icon = '+';

                                    $iconClasses =
                                        'bg-emerald-50 text-emerald-700';

                                    $activityLabel =
                                        'Stock In';

                                } elseif ($isStockOut) {

                                    $icon = '−';

                                    $iconClasses =
                                        'bg-red-50 text-red-700';

                                    $activityLabel =
                                        'Stock Out';

                                } else {

                                    $icon = '±';

                                    $iconClasses =
                                        'bg-blue-50 text-blue-700';

                                    $activityLabel =
                                        'Adjustment';

                                }

                                ?>


                                <div
                                    class="
                                        flex
                                        items-center
                                        gap-3
                                        py-4
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
                                            rounded-lg
                                            font-bold

                                            <?= $iconClasses ?>
                                        "
                                    >

                                        <?= $icon ?>

                                    </div>



                                    <div
                                        class="
                                            min-w-0
                                            flex-1
                                        "
                                    >

                                        <p
                                            class="
                                                truncate
                                                text-sm
                                                font-semibold
                                                text-slate-900
                                            "
                                        >

                                            <?= htmlspecialchars(
                                                $activity[
                                                    'product_name'
                                                ],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </p>


                                        <p
                                            class="
                                                mt-0.5
                                                text-xs
                                                text-slate-500
                                            "
                                        >

                                            <?= $activityLabel ?>

                                            ·

                                            <?= number_format(
                                                (int)
                                                $activity[
                                                    'quantity'
                                                ]
                                            ) ?>

                                            <?= htmlspecialchars(
                                                $activity['unit'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </p>

                                    </div>



                                    <time
                                        class="
                                            whitespace-nowrap
                                            text-[11px]
                                            text-slate-400
                                        "
                                    >

                                        <?= formatActivityDate(
                                            $activity[
                                                'created_at'
                                            ]
                                        ) ?>

                                    </time>


                                </div>


                            <?php endforeach; ?>


                        </div>


                    <?php endif; ?>


                </div>


            </section>


        </div>


    </main>


</div>


</body>

</html>