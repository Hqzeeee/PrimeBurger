"use strict";


/*
|--------------------------------------------------------------------------
| DOM Elements
|--------------------------------------------------------------------------
*/

const startButton =
    document.getElementById("start-camera");

const stopButton =
    document.getElementById("stop-camera");

const scannerMessage =
    document.getElementById("scanner-message");


const manualInput =
    document.getElementById("manual-qr");

const manualSearchButton =
    document.getElementById("manual-search");


const chooseQrImageButton =
    document.getElementById("choose-qr-image");

const qrFileInput =
    document.getElementById("qr-file-input");

const selectedFileName =
    document.getElementById("selected-file-name");


const scanAgainButton =
    document.getElementById("scan-again");


const resultEmpty =
    document.getElementById("result-empty");

const resultError =
    document.getElementById("result-error");

const resultProduct =
    document.getElementById("result-product");


const errorMessage =
    document.getElementById("error-message");


const productName =
    document.getElementById("product-name");

const productCategory =
    document.getElementById("product-category");

const productQr =
    document.getElementById("product-qr");

const productQuantity =
    document.getElementById("product-quantity");

const productReorder =
    document.getElementById("product-reorder");

const productExpiry =
    document.getElementById("product-expiry");

const productStatus =
    document.getElementById("product-status");


/*
|--------------------------------------------------------------------------
| QR Scanner Instance
|--------------------------------------------------------------------------
*/

const html5QrCode =
    new Html5Qrcode("reader");


let scannerRunning = false;

let scanLocked = false;


/*
|--------------------------------------------------------------------------
| Start Camera
|--------------------------------------------------------------------------
*/

async function startCamera() {

    if (scannerRunning) {
        return;
    }


    resetFileSelection();

    scanLocked = false;


    scannerMessage.textContent =
        "Requesting camera permission...";


    startButton.disabled = true;


    try {

        await html5QrCode.start(

            {
                facingMode: "environment"
            },

            {
                fps: 10,

                qrbox: {
                    width: 250,
                    height: 250
                },

                aspectRatio: 1
            },

            handleSuccessfulCameraScan,

            () => {

                /*
                 * Normal decode failures are ignored.
                 * The scanner continuously checks
                 * the camera frames.
                 */

            }

        );


        scannerRunning = true;


        startButton.disabled = true;

        stopButton.disabled = false;


        scannerMessage.textContent =
            "Camera active. Position the QR code inside the scanner.";


    } catch (error) {

        console.error(
            "Unable to start camera:",
            error
        );


        scannerRunning = false;


        startButton.disabled = false;

        stopButton.disabled = true;


        scannerMessage.textContent =
            "Unable to access the camera. Please allow camera permission and try again.";

    }

}


/*
|--------------------------------------------------------------------------
| Successful Camera Scan
|--------------------------------------------------------------------------
*/

async function handleSuccessfulCameraScan(
    decodedText
) {

    if (scanLocked) {
        return;
    }


    const code =
        String(decodedText).trim();


    if (!code) {
        return;
    }


    scanLocked = true;


    scannerMessage.textContent =
        "QR detected. Looking up product...";


    await stopCamera();


    await lookupProduct(code);

}


/*
|--------------------------------------------------------------------------
| Stop Camera
|--------------------------------------------------------------------------
*/

async function stopCamera() {

    if (!scannerRunning) {

        startButton.disabled = false;

        stopButton.disabled = true;

        return;
    }


    try {

        await html5QrCode.stop();

    } catch (error) {

        console.error(
            "Unable to stop camera:",
            error
        );

    }


    scannerRunning = false;


    startButton.disabled = false;

    stopButton.disabled = true;

}


/*
|--------------------------------------------------------------------------
| Upload QR Image
|--------------------------------------------------------------------------
*/

async function scanUploadedQr(file) {

    if (!file) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Check File Type
    |--------------------------------------------------------------------------
    */

    if (!file.type.startsWith("image/")) {

        showError(
            "Please select a valid image file."
        );

        resetFileSelection();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Stop Camera Before File Scan
    |--------------------------------------------------------------------------
    */

    if (scannerRunning) {

        await stopCamera();

    }


    scanLocked = true;


    selectedFileName.textContent =
        file.name;

    selectedFileName.classList.remove(
        "hidden"
    );


    scannerMessage.textContent =
        "Reading QR code from uploaded image...";


    hideAllResults();


    try {

        /*
         * true = display uploaded image
         * inside the QR reader container.
         */

        const decodedText =
            await html5QrCode.scanFile(
                file,
                true
            );


        const code =
            String(decodedText).trim();


        if (!code) {

            throw new Error(
                "QR code was empty."
            );

        }


        scannerMessage.textContent =
            "QR detected from uploaded image.";


        await lookupProduct(code);


    } catch (error) {

        console.error(
            "Unable to scan uploaded QR image:",
            error
        );


        scannerMessage.textContent =
            "No readable QR code was detected in the uploaded image.";


        showError(
            "No readable QR code was found in this image. Try another image with a clearer QR code."
        );

    }

}


/*
|--------------------------------------------------------------------------
| Product Lookup
|--------------------------------------------------------------------------
*/

async function lookupProduct(code) {

    const cleanedCode =
        String(code).trim();


    if (!cleanedCode) {

        showError(
            "A QR code value is required."
        );

        return;

    }


    hideAllResults();


    scannerMessage.textContent =
        `Scanned QR: ${cleanedCode}`;


    try {

        const response =
            await fetch(
                "api/qr-lookup.php?code="
                +
                encodeURIComponent(cleanedCode),
                {
                    method: "GET",

                    headers: {
                        "Accept":
                            "application/json"
                    },

                    cache: "no-store"
                }
            );


        const data =
            await response.json();


        if (
            !response.ok
            ||
            !data.success
        ) {

            showError(
                data.message
                ||
                "Product not found."
            );

            return;

        }


        showProduct(
            data.product
        );


    } catch (error) {

        console.error(
            "QR lookup failed:",
            error
        );


        showError(
            "Unable to communicate with the server."
        );

    }

}


/*
|--------------------------------------------------------------------------
| Show Product
|--------------------------------------------------------------------------
*/

function showProduct(product) {

    resultEmpty.classList.add(
        "hidden"
    );

    resultError.classList.add(
        "hidden"
    );

    resultProduct.classList.remove(
        "hidden"
    );


    productName.textContent =
        product.name
        ||
        "Unnamed Product";


    productCategory.textContent =
        product.category
        ||
        "No category";


    productQr.textContent =
        product.qr_code
        ||
        "—";


    productQuantity.textContent =
        `${product.quantity} ${product.unit}`;


    productReorder.textContent =
        `${product.reorder_level} ${product.unit}`;


    productExpiry.textContent =
        formatDate(
            product.expiry_date
        );


    productStatus.textContent =
        product.status;


    updateStatusStyle(
        product.status
    );

}


/*
|--------------------------------------------------------------------------
| Show Error
|--------------------------------------------------------------------------
*/

function showError(message) {

    resultEmpty.classList.add(
        "hidden"
    );

    resultProduct.classList.add(
        "hidden"
    );

    resultError.classList.remove(
        "hidden"
    );


    errorMessage.textContent =
        message;

}


/*
|--------------------------------------------------------------------------
| Reset Result
|--------------------------------------------------------------------------
*/

function hideAllResults() {

    resultEmpty.classList.remove(
        "hidden"
    );

    resultError.classList.add(
        "hidden"
    );

    resultProduct.classList.add(
        "hidden"
    );

}


/*
|--------------------------------------------------------------------------
| Status Styling
|--------------------------------------------------------------------------
*/

function updateStatusStyle(status) {

    productStatus.className =
        "w-fit rounded-full px-3 py-1.5 text-xs font-semibold";


    switch (status) {

        case "Expired":

            productStatus.classList.add(
                "bg-red-50",
                "text-red-700"
            );

            break;


        case "Low Stock":

            productStatus.classList.add(
                "bg-amber-50",
                "text-amber-700"
            );

            break;


        case "Near Expiry":

            productStatus.classList.add(
                "bg-orange-50",
                "text-orange-700"
            );

            break;


        default:

            productStatus.classList.add(
                "bg-emerald-50",
                "text-emerald-700"
            );

            break;

    }

}


/*
|--------------------------------------------------------------------------
| Date Formatting
|--------------------------------------------------------------------------
*/

function formatDate(dateString) {

    if (!dateString) {

        return "No expiration date";

    }


    const date =
        new Date(
            `${dateString}T00:00:00`
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return dateString;

    }


    return date.toLocaleDateString(
        "en-US",
        {
            year: "numeric",
            month: "short",
            day: "numeric"
        }
    );

}


/*
|--------------------------------------------------------------------------
| Reset File Selection
|--------------------------------------------------------------------------
*/

function resetFileSelection() {

    qrFileInput.value = "";


    selectedFileName.textContent = "";

    selectedFileName.classList.add(
        "hidden"
    );

}


/*
|--------------------------------------------------------------------------
| Choose Image Button
|--------------------------------------------------------------------------
*/

chooseQrImageButton.addEventListener(
    "click",
    () => {

        qrFileInput.click();

    }
);


/*
|--------------------------------------------------------------------------
| File Selected
|--------------------------------------------------------------------------
*/

qrFileInput.addEventListener(
    "change",
    async () => {

        const file =
            qrFileInput.files[0];


        if (!file) {
            return;
        }


        await scanUploadedQr(file);

    }
);


/*
|--------------------------------------------------------------------------
| Start Camera Button
|--------------------------------------------------------------------------
*/

startButton.addEventListener(
    "click",
    async () => {

        await startCamera();

    }
);


/*
|--------------------------------------------------------------------------
| Stop Camera Button
|--------------------------------------------------------------------------
*/

stopButton.addEventListener(
    "click",
    async () => {

        await stopCamera();


        scannerMessage.textContent =
            "Camera stopped.";

    }
);


/*
|--------------------------------------------------------------------------
| Manual Lookup
|--------------------------------------------------------------------------
*/

manualSearchButton.addEventListener(
    "click",
    async () => {

        const code =
            manualInput.value.trim();


        if (!code) {

            showError(
                "Enter a QR code first."
            );

            manualInput.focus();

            return;

        }


        if (scannerRunning) {

            await stopCamera();

        }


        resetFileSelection();


        await lookupProduct(
            code
        );

    }
);


/*
|--------------------------------------------------------------------------
| Press Enter For Manual Search
|--------------------------------------------------------------------------
*/

manualInput.addEventListener(
    "keydown",
    (event) => {

        if (event.key !== "Enter") {
            return;
        }


        event.preventDefault();


        manualSearchButton.click();

    }
);


/*
|--------------------------------------------------------------------------
| Scan Another Product
|--------------------------------------------------------------------------
*/

scanAgainButton.addEventListener(
    "click",
    async () => {

        hideAllResults();


        resetFileSelection();


        manualInput.value = "";


        scanLocked = false;


        scannerMessage.textContent =
            "Choose camera scanning, upload a QR image, or enter a QR code manually.";

    }
);


/*
|--------------------------------------------------------------------------
| Stop Camera When Leaving Page
|--------------------------------------------------------------------------
*/

window.addEventListener(
    "beforeunload",
    () => {

        if (!scannerRunning) {
            return;
        }


        html5QrCode
            .stop()
            .catch(() => {});

    }
);