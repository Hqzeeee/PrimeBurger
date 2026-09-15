<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| Get scanned QR code
|--------------------------------------------------------------------------
*/

$qrCode = trim($_GET['code'] ?? '');


if ($qrCode === '') {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'QR code is required.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Find product
|--------------------------------------------------------------------------
*/

try {

    $statement = $pdo->prepare("
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

        WHERE qr_code = :qr_code

        LIMIT 1
    ");


    $statement->execute([
        'qr_code' => $qrCode
    ]);


    $product = $statement->fetch();


    /*
    |--------------------------------------------------------------------------
    | Product not found
    |--------------------------------------------------------------------------
    */

    if (!$product) {

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'No product matches this QR code.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Determine inventory status
    |--------------------------------------------------------------------------
    */

    $quantity =
        (int) $product['quantity'];

    $reorderLevel =
        (int) $product['reorder_level'];


    $status = 'In Stock';


    if (!empty($product['expiry_date'])) {

        $today =
            new DateTimeImmutable('today');

        $expiry =
            new DateTimeImmutable(
                $product['expiry_date']
            );


        if ($expiry < $today) {

            $status = 'Expired';

        } else {

            $nearExpiryDate =
                $today->modify('+7 days');


            if ($expiry <= $nearExpiryDate) {

                $status = 'Near Expiry';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Low stock takes priority if product isn't expired
    |--------------------------------------------------------------------------
    */

    if (
        $status !== 'Expired'
        &&
        $quantity <= $reorderLevel
    ) {

        $status = 'Low Stock';
    }


    /*
    |--------------------------------------------------------------------------
    | Successful response
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'success' => true,

        'product' => [
            'product_id' =>
                (int) $product['product_id'],

            'name' =>
                $product['name'],

            'category' =>
                $product['category'],

            'qr_code' =>
                $product['qr_code'],

            'quantity' =>
                $quantity,

            'unit' =>
                $product['unit'],

            'expiry_date' =>
                $product['expiry_date'],

            'reorder_level' =>
                $reorderLevel,

            'status' =>
                $status
        ]
    ]);


} catch (PDOException $exception) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Unable to retrieve the product.'
    ]);
}