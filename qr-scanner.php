<?php

declare(strict_types=1);

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
        content="PrimeBurger QR Product Scanner"
    >

    <title>
        QR Scanner | PrimeBurger Inventory
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


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

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

                <p class="text-xs text-zinc-400">
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
                    bg-red-700
                    px-4
                    py-3
                    text-sm
                    font-semibold
                    text-white
                "
            >
                QR Scanner
            </a>

        </nav>


        <!-- OWNER -->

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



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="min-h-screen lg:ml-64">


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
                    justify-between
                "
            >

                <div class="flex items-center gap-3">

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

                        <p class="text-sm font-bold">
                            PrimeBurger
                        </p>

                        <p class="text-xs text-slate-500">
                            QR Scanner
                        </p>

                    </div>

                </div>


                <a
                    href="dashboard.php"
                    class="
                        text-sm
                        font-semibold
                        text-red-700
                    "
                >
                    Dashboard
                </a>

            </div>

        </div>



        <div
            class="
                mx-auto
                max-w-6xl
                p-5
                md:p-8
            "
        >


            <!-- HEADER -->

            <header class="mb-8">

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
                    Inventory Tools
                </p>


                <h2
                    class="
                        text-3xl
                        font-bold
                        tracking-tight
                        md:text-4xl
                    "
                >
                    QR Scanner
                </h2>


                <p
                    class="
                        mt-2
                        max-w-2xl
                        text-sm
                        text-slate-500
                    "
                >
                    Scan with the camera, upload a QR image,
                    or enter a QR code manually.
                </p>

            </header>



            <!-- MAIN GRID -->

            <section
                class="
                    grid
                    grid-cols-1
                    gap-6
                    lg:grid-cols-2
                "
            >


                <!-- =================================================
                     SCANNER CARD
                ================================================== -->

                <div
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        p-6
                        shadow-sm
                    "
                >

                    <div class="mb-5">

                        <h3
                            class="
                                text-lg
                                font-semibold
                            "
                        >
                            Scan Product
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-slate-500
                            "
                        >
                            Choose how you want to scan the
                            product QR code.
                        </p>

                    </div>


                    <!-- =============================================
                         CAMERA
                    ============================================== -->

                    <div>

                        <div
                            class="
                                mb-3
                                flex
                                items-center
                                justify-between
                            "
                        >

                            <div>

                                <p
                                    class="
                                        text-sm
                                        font-semibold
                                        text-slate-800
                                    "
                                >
                                    Camera Scanner
                                </p>

                                <p
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-slate-500
                                    "
                                >
                                    Scan using your device camera.
                                </p>

                            </div>

                        </div>


                        <div
                            id="reader"
                            class="
                                min-h-[320px]
                                overflow-hidden
                                rounded-xl
                                border
                                border-dashed
                                border-slate-300
                                bg-slate-50
                            "
                        >
                        </div>


                        <div
                            id="scanner-message"
                            class="
                                mt-4
                                rounded-xl
                                bg-slate-50
                                px-4
                                py-3
                                text-sm
                                text-slate-600
                            "
                        >
                            Camera is currently off.
                        </div>


                        <div
                            class="
                                mt-4
                                flex
                                flex-wrap
                                gap-3
                            "
                        >

                            <button
                                id="start-camera"
                                type="button"
                                class="
                                    rounded-xl
                                    bg-red-700
                                    px-5
                                    py-3
                                    text-sm
                                    font-semibold
                                    text-white
                                    transition
                                    hover:bg-red-800
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                "
                            >
                                Start Camera
                            </button>


                            <button
                                id="stop-camera"
                                type="button"
                                disabled
                                class="
                                    rounded-xl
                                    border
                                    border-slate-300
                                    bg-white
                                    px-5
                                    py-3
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                    transition
                                    hover:bg-slate-50
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                "
                            >
                                Stop Camera
                            </button>

                        </div>

                    </div>



                    <!-- =============================================
                         UPLOAD QR
                    ============================================== -->

                    <div
                        class="
                            mt-7
                            border-t
                            border-slate-200
                            pt-6
                        "
                    >

                        <div>

                            <p
                                class="
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                Upload QR Image
                            </p>

                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-slate-500
                                "
                            >
                                Select a photo or screenshot
                                containing a QR code.
                            </p>

                        </div>


                        <div
                            class="
                                mt-4
                                rounded-xl
                                border
                                border-dashed
                                border-slate-300
                                bg-slate-50
                                p-5
                                text-center
                            "
                        >

                            <div
                                class="
                                    mx-auto
                                    mb-3
                                    flex
                                    h-12
                                    w-12
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-white
                                    text-lg
                                    font-bold
                                    text-slate-400
                                    shadow-sm
                                "
                            >
                                QR
                            </div>


                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                Upload a QR code image
                            </p>


                            <p
                                class="
                                    mt-1
                                    text-xs
                                    text-slate-500
                                "
                            >
                                JPG, JPEG, PNG, or other
                                supported image formats
                            </p>


                            <input
                                id="qr-file-input"
                                type="file"
                                accept="image/*"
                                class="hidden"
                            >


                            <button
                                id="choose-qr-image"
                                type="button"
                                class="
                                    mt-4
                                    rounded-xl
                                    border
                                    border-slate-300
                                    bg-white
                                    px-5
                                    py-2.5
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                    transition
                                    hover:bg-slate-100
                                "
                            >
                                Choose QR Image
                            </button>


                            <p
                                id="selected-file-name"
                                class="
                                    mt-3
                                    hidden
                                    break-all
                                    text-xs
                                    text-slate-500
                                "
                            >
                            </p>

                        </div>

                    </div>



                    <!-- =============================================
                         MANUAL SEARCH
                    ============================================== -->

                    <div
                        class="
                            mt-7
                            border-t
                            border-slate-200
                            pt-6
                        "
                    >

                        <label
                            for="manual-qr"
                            class="
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Manual QR Lookup
                        </label>


                        <p
                            class="
                                mt-1
                                text-xs
                                text-slate-500
                            "
                        >
                            Enter the QR value manually
                            if scanning is unavailable.
                        </p>


                        <div
                            class="
                                mt-3
                                flex
                                flex-col
                                gap-2
                                sm:flex-row
                            "
                        >

                            <input
                                id="manual-qr"
                                type="text"
                                autocomplete="off"
                                placeholder="Example: PB-PATTY-001"
                                class="
                                    min-w-0
                                    flex-1
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


                            <button
                                id="manual-search"
                                type="button"
                                class="
                                    rounded-xl
                                    bg-slate-900
                                    px-5
                                    py-3
                                    text-sm
                                    font-semibold
                                    text-white
                                    transition
                                    hover:bg-slate-800
                                "
                            >
                                Search
                            </button>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     RESULT CARD
                ================================================== -->

                <div
                    class="
                        rounded-2xl
                        border
                        border-slate-200
                        bg-white
                        p-6
                        shadow-sm
                    "
                >

                    <div>

                        <h3
                            class="
                                text-lg
                                font-semibold
                            "
                        >
                            Product Information
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-slate-500
                            "
                        >
                            Scanned product information
                            will appear here.
                        </p>

                    </div>


                    <!-- EMPTY -->

                    <div
                        id="result-empty"
                        class="
                            flex
                            min-h-[380px]
                            flex-col
                            items-center
                            justify-center
                            text-center
                        "
                    >

                        <div
                            class="
                                mb-4
                                flex
                                h-16
                                w-16
                                items-center
                                justify-center
                                rounded-2xl
                                bg-slate-100
                                font-bold
                                text-slate-400
                            "
                        >
                            QR
                        </div>

                        <p
                            class="
                                font-semibold
                                text-slate-700
                            "
                        >
                            No product scanned yet
                        </p>

                        <p
                            class="
                                mt-2
                                max-w-xs
                                text-sm
                                text-slate-500
                            "
                        >
                            Scan using the camera,
                            upload a QR image,
                            or enter a QR code manually.
                        </p>

                    </div>



                    <!-- ERROR -->

                    <div
                        id="result-error"
                        class="
                            mt-6
                            hidden
                            rounded-xl
                            border
                            border-red-200
                            bg-red-50
                            p-5
                        "
                    >

                        <p
                            class="
                                font-semibold
                                text-red-700
                            "
                        >
                            Unable to Find Product
                        </p>

                        <p
                            id="error-message"
                            class="
                                mt-2
                                text-sm
                                text-red-600
                            "
                        >
                        </p>

                    </div>



                    <!-- PRODUCT RESULT -->

                    <div
                        id="result-product"
                        class="
                            mt-6
                            hidden
                        "
                    >

                        <div
                            class="
                                border-b
                                border-slate-200
                                pb-5
                            "
                        >

                            <div
                                class="
                                    flex
                                    flex-col
                                    gap-3
                                    sm:flex-row
                                    sm:items-start
                                    sm:justify-between
                                "
                            >

                                <div>

                                    <p
                                        id="product-name"
                                        class="
                                            text-2xl
                                            font-bold
                                        "
                                    >
                                    </p>

                                    <p
                                        id="product-category"
                                        class="
                                            mt-1
                                            text-sm
                                            text-slate-500
                                        "
                                    >
                                    </p>

                                </div>


                                <span
                                    id="product-status"
                                    class="
                                        w-fit
                                        rounded-full
                                        px-3
                                        py-1.5
                                        text-xs
                                        font-semibold
                                    "
                                >
                                </span>

                            </div>

                        </div>


                        <dl
                            class="
                                mt-6
                                grid
                                grid-cols-1
                                gap-6
                                sm:grid-cols-2
                            "
                        >


                            <div>

                                <dt
                                    class="
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                    "
                                >
                                    QR Code
                                </dt>

                                <dd
                                    id="product-qr"
                                    class="
                                        mt-1
                                        break-all
                                        text-sm
                                        font-medium
                                    "
                                >
                                </dd>

                            </div>


                            <div>

                                <dt
                                    class="
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                    "
                                >
                                    Quantity
                                </dt>

                                <dd
                                    id="product-quantity"
                                    class="
                                        mt-1
                                        text-sm
                                        font-medium
                                    "
                                >
                                </dd>

                            </div>


                            <div>

                                <dt
                                    class="
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                    "
                                >
                                    Reorder Level
                                </dt>

                                <dd
                                    id="product-reorder"
                                    class="
                                        mt-1
                                        text-sm
                                        font-medium
                                    "
                                >
                                </dd>

                            </div>


                            <div>

                                <dt
                                    class="
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                    "
                                >
                                    Expiration Date
                                </dt>

                                <dd
                                    id="product-expiry"
                                    class="
                                        mt-1
                                        text-sm
                                        font-medium
                                    "
                                >
                                </dd>

                            </div>

                        </dl>


                        <button
                            id="scan-again"
                            type="button"
                            class="
                                mt-8
                                w-full
                                rounded-xl
                                border
                                border-slate-300
                                bg-white
                                px-5
                                py-3
                                text-sm
                                font-semibold
                                text-slate-700
                                transition
                                hover:bg-slate-50
                            "
                        >
                            Scan Another Product
                        </button>

                    </div>

                </div>

            </section>

        </div>

    </main>

</div>


<script src="assets/js/html5-qrcode.min.js"></script>

<script src="assets/js/qr-scanner.js"></script>


</body>

</html>