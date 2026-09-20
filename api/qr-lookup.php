<?php
require_once __DIR__ . '/../app/bootstrap.php';
header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['found' => false, 'error' => 'Not authenticated.']);
    exit;
}

$code = trim($_GET['code'] ?? '');
if ($code === '') {
    echo json_encode(['found' => false]);
    exit;
}

$productModel = new ProductModel();
$product = $productModel->findByQrCode($code);

if (!$product) {
    // Fall back to product_code match for convenience.
    $stmt = Database::getConnection()->prepare(
        "SELECT p.*, c.name AS category_name, s.name AS supplier_name
         FROM products p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN suppliers s ON s.id = p.supplier_id
         WHERE p.product_code = ? LIMIT 1"
    );
    $stmt->execute([$code]);
    $product = $stmt->fetch();
}

if (!$product) {
    echo json_encode(['found' => false]);
    exit;
}

echo json_encode([
    'found' => true,
    'product' => [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'product_code' => $product['product_code'],
        'category_name' => $product['category_name'],
        'supplier_name' => $product['supplier_name'],
        'quantity' => rtrim(rtrim(number_format($product['quantity'], 2), '0'), '.'),
        'unit' => $product['unit'],
        'cost_price' => number_format($product['cost_price'], 2),
        'expiration_date' => $product['expiration_date'],
        'expiry_status' => ProductModel::expiryStatus($product['expiration_date']),
        'low_stock' => $product['quantity'] <= $product['minimum_stock_level'],
    ],
]);
