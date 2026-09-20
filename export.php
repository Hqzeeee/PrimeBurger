<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/models/ReportModel.php';
Auth::requireRole(['owner']);

$reportModel = new ReportModel();

$type     = $_GET['report'] ?? 'inventory';
$format   = $_GET['format'] ?? 'csv';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to'] ?? '';
$keyword  = trim($_GET['q'] ?? '');

$columns = [
    'inventory'  => ['product_code'=>'Code','name'=>'Name','category_name'=>'Category','supplier_name'=>'Supplier','quantity'=>'Quantity','unit'=>'Unit','cost_price'=>'Cost Price','selling_price'=>'Selling Price','inventory_value'=>'Inventory Value','date_received'=>'Date Received','expiration_date'=>'Expiration Date'],
    'movement'   => ['transaction_date'=>'Date','product_code'=>'Code','name'=>'Product','type'=>'Type','quantity'=>'Quantity','reason'=>'Reason','user_name'=>'Recorded By'],
    'expiration' => ['product_code'=>'Code','name'=>'Name','category_name'=>'Category','quantity'=>'Quantity','unit'=>'Unit','expiration_date'=>'Expiration Date','at_risk_value'=>'At-Risk Value'],
    'waste'      => ['product_code'=>'Code','name'=>'Name','category_name'=>'Category','quantity'=>'Quantity','unit'=>'Unit','expiration_date'=>'Expired On','loss_value'=>'Loss Value'],
];
if (!array_key_exists($type, $columns)) $type = 'inventory';

switch ($type) {
    case 'movement':   $rows = $reportModel->stockMovementReport($dateFrom, $dateTo, $keyword); break;
    case 'expiration': $rows = $reportModel->expirationReport($dateFrom, $dateTo, $keyword); break;
    case 'waste':      $rows = $reportModel->wasteLossReport($dateFrom, $dateTo, $keyword); break;
    default:           $rows = $reportModel->inventoryReport($dateFrom, $dateTo, $keyword); break;
}

$cols = $columns[$type];
$filename = 'primeburger-' . $type . '-report-' . date('Ymd-His');

ActivityLogger::log('report_export', "Exported {$type} report as {$format}");

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array_values($cols));
    foreach ($rows as $r) {
        $line = [];
        foreach (array_keys($cols) as $key) $line[] = $r[$key] ?? '';
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}

// Excel (.xls) export via an HTML table — Excel opens this natively without any library.
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel renders ₱ / special characters correctly
?>
<html>
<head><meta charset="UTF-8"></head>
<body>
<table border="1">
  <tr><th colspan="<?= count($cols) ?>" style="font-size:14px;background:#d94f2b;color:#fff;">PrimeBurger IMS — <?= Validator::e(ucfirst($type)) ?> Report (Generated <?= date('F j, Y g:ia') ?>)</th></tr>
  <tr>
    <?php foreach ($cols as $label): ?><th style="background:#f0f0f0;"><?= Validator::e($label) ?></th><?php endforeach; ?>
  </tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <?php foreach (array_keys($cols) as $key): ?>
        <td><?= Validator::e((string)($r[$key] ?? '')) ?></td>
      <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
</table>
</body>
</html>
