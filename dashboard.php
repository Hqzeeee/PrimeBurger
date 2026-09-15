<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';


/*
|--------------------------------------------------------------------------
| Page Information
|--------------------------------------------------------------------------
*/

$activePage = 'dashboard';

$pageTitle = 'Dashboard';


/*
|--------------------------------------------------------------------------
| Dashboard Configuration
|--------------------------------------------------------------------------
*/

const NEAR_EXPIRY_DAYS = 7;


/*
|--------------------------------------------------------------------------
| Get Dashboard Count
|--------------------------------------------------------------------------
*/

function getDashboardCount(
    PDO $pdo,
    string $query
): int {

    try {

        $statement =
            $pdo->query($query);

        return (int)
            $statement->fetchColumn();

    } catch (PDOException $exception) {

        return 0;

    }

}


/*
|--------------------------------------------------------------------------
| Total Products
|--------------------------------------------------------------------------
*/

$totalProducts =
    getDashboardCount(
        $pdo,
        "
            SELECT COUNT(*)
            FROM products
        "
    );


/*
|--------------------------------------------------------------------------
| Available Stock
|--------------------------------------------------------------------------
*/

$totalStock =
    getDashboardCount(
        $pdo,
        "
            SELECT
                COALESCE(
                    SUM(quantity),
                    0
                )

            FROM products
        "
    );


/*
|--------------------------------------------------------------------------
| Low Stock
|--------------------------------------------------------------------------
*/

$lowStock =
    getDashboardCount(
        $pdo,
        "
            SELECT COUNT(*)

            FROM products

            WHERE reorder_level > 0

            AND quantity <= reorder_level
        "
    );


/*
|--------------------------------------------------------------------------
| Expired Products
|--------------------------------------------------------------------------
*/

$expiredProducts =
    getDashboardCount(
        $pdo,
        "
            SELECT COUNT(*)

            FROM products

            WHERE expiry_date IS NOT NULL

            AND expiry_date < CURDATE()
        "
    );


/*
|--------------------------------------------------------------------------
| Near Expiry
|--------------------------------------------------------------------------
*/

$nearExpiry =
    getDashboardCount(
        $pdo,
        "
            SELECT COUNT(*)

            FROM products

            WHERE expiry_date IS NOT NULL

            AND expiry_date >= CURDATE()

            AND expiry_date <= DATE_ADD(
                CURDATE(),
                INTERVAL "
                .
                NEAR_EXPIRY_DAYS
                .
                " DAY
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
        category,
        qr_code,
        quantity,
        unit,
        expiry_date,
        reorder_level

    FROM products

    WHERE

        (
            reorder_level > 0

            AND quantity <= reorder_level
        )

        OR expiry_date < CURDATE()

        OR (

            expiry_date >= CURDATE()

            AND expiry_date <= DATE_ADD(
                CURDATE(),
                INTERVAL "
                .
                NEAR_EXPIRY_DAYS
                .
                " DAY
            )

        )

    ORDER BY

        CASE

            WHEN expiry_date < CURDATE()
                THEN 1

            WHEN
                reorder_level > 0
                AND quantity <= reorder_level
                THEN 2

            ELSE 3

        END,

        expiry_date ASC,

        name ASC

    LIMIT 8

";


try {

    $statement =
        $pdo->query($attentionSql);

    $attentionProducts =
        $statement->fetchAll();

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

    $statement =
        $pdo->query($activitySql);

    $recentActivities =
        $statement->fetchAll();

} catch (PDOException $exception) {

    $recentActivities = [];

}


/*
|--------------------------------------------------------------------------
| Date Functions
|--------------------------------------------------------------------------
*/

function dashboardDate(
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


function dashboardActivityDate(
    string $date
): string {

    $timestamp =
        strtotime($date);


    if (
        date(
            'Y-m-d',
            $timestamp
        )
        ===
        date('Y-m-d')
    ) {

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

    <title>
        Dashboard | PrimeBurger Inventory
    </title>


    <link
        rel="stylesheet"
        href="assets/css/output.css"
    >

</head>


<body
    class="
        bg-slate-50
        text-slate-900
        antialiased
    "
>


<div class="min-h-screen">


    <?php

    require __DIR__ . '/includes/sidebar.php';

    ?>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main
        class="
            min-h-screen
            lg:ml-64
        "
    >


        <div
            class="
                mx-auto
                max-w-[1600px]
                p-5
                md:p-8
            "
        >


            <!-- HEADER -->

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
                            "
                        >
                            Owner
                        </p>


                        <p
                            class="
                                text-xs
                                text-slate-500
                            "
                        >
                            Administrator
                        </p>

                    </div>

                </div>

            </header>



            <!-- =================================================
                 SUMMARY CARDS
            ================================================== -->

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
                        "
                    >
                        <?= number_format(
                            $totalProducts
                        ) ?>
                    </p>


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



            <!-- =================================================
                 DASHBOARD LOWER SECTION
            ================================================== -->

            <section
                class="
                    grid
                    grid-cols-1
                    gap-6
                    xl:grid-cols-3
                "
            >


                <!-- PRODUCTS REQUIRING ATTENTION -->

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
                            border-b
                            border-slate-200
                            px-6
                            py-5
                        "
                    >

                        <h3 class="font-semibold">
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



                    <?php if (
                        empty($attentionProducts)
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
                                    text-2xl
                                "
                            >
                                📦
                            </div>


                            <h4
                                class="
                                    text-sm
                                    font-semibold
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
                                Low-stock, near-expiry
                                and expired products will
                                automatically appear here.
                            </p>

                        </div>


                    <?php else: ?>


                        <div class="overflow-x-auto">


                            <table class="min-w-full">


                                <thead
                                    class="
                                        bg-slate-50
                                        text-left
                                    "
                                >

                                <tr>

                                    <th
                                        class="
                                            px-6
                                            py-3
                                            text-xs
                                            uppercase
                                            text-slate-500
                                        "
                                    >
                                        Product
                                    </th>


                                    <th
                                        class="
                                            px-6
                                            py-3
                                            text-xs
                                            uppercase
                                            text-slate-500
                                        "
                                    >
                                        Quantity
                                    </th>


                                    <th
                                        class="
                                            px-6
                                            py-3
                                            text-xs
                                            uppercase
                                            text-slate-500
                                        "
                                    >
                                        Expiration
                                    </th>


                                    <th
                                        class="
                                            px-6
                                            py-3
                                            text-xs
                                            uppercase
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


                                    if (
                                        $expiryDate
                                        &&
                                        strtotime($expiryDate)
                                        <
                                        strtotime(date('Y-m-d'))
                                    ) {

                                        $status =
                                            'Expired';

                                        $statusClass =
                                            'bg-red-50 text-red-700';

                                    } elseif (
                                        $reorderLevel > 0
                                        &&
                                        $quantity <= $reorderLevel
                                    ) {

                                        $status =
                                            'Low Stock';

                                        $statusClass =
                                            'bg-amber-50 text-amber-700';

                                    } else {

                                        $status =
                                            'Near Expiry';

                                        $statusClass =
                                            'bg-orange-50 text-orange-700';

                                    }

                                    ?>


                                    <tr
                                        class="
                                            hover:bg-slate-50
                                        "
                                    >

                                        <td
                                            class="
                                                px-6
                                                py-4
                                                text-sm
                                                font-semibold
                                            "
                                        >

                                            <?= htmlspecialchars(
                                                $product['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </td>


                                        <td
                                            class="
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
                                                px-6
                                                py-4
                                                text-sm
                                                text-slate-600
                                            "
                                        >

                                            <?= dashboardDate(
                                                $expiryDate
                                            ) ?>

                                        </td>


                                        <td
                                            class="
                                                px-6
                                                py-4
                                            "
                                        >

                                            <span
                                                class="
                                                    rounded-full
                                                    px-2.5
                                                    py-1
                                                    text-xs
                                                    font-semibold

                                                    <?= $statusClass ?>
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



                <!-- RECENT ACTIVITY -->

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

                        <h3 class="font-semibold">
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
                                p-6
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
                                "
                            >
                                ↻
                            </div>


                            <p class="font-semibold">
                                No inventory activity yet
                            </p>


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

                                if (
                                    $activity['log_type']
                                    ===
                                    'stock_in'
                                ) {

                                    $symbol = '+';

                                    $activityName =
                                        'Stock In';

                                    $iconClass =
                                        'bg-emerald-50 text-emerald-700';

                                } elseif (
                                    $activity['log_type']
                                    ===
                                    'stock_out'
                                ) {

                                    $symbol = '−';

                                    $activityName =
                                        'Stock Out';

                                    $iconClass =
                                        'bg-red-50 text-red-700';

                                } else {

                                    $symbol = '±';

                                    $activityName =
                                        'Adjustment';

                                    $iconClass =
                                        'bg-blue-50 text-blue-700';

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

                                            <?= $iconClass ?>
                                        "
                                    >
                                        <?= $symbol ?>
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
                                            "
                                        >

                                            <?= htmlspecialchars(
                                                $activity['product_name'],
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

                                            <?= $activityName ?>

                                            ·

                                            <?= number_format(
                                                (int)
                                                $activity['quantity']
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

                                        <?= dashboardActivityDate(
                                            $activity['created_at']
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