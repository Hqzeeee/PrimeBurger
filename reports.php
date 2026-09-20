<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/models/ReportModel.php';
Auth::requireRole(['owner']);

$reportModel = new ReportModel();

$type     = $_GET['report'] ?? 'inventory';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to'] ?? '';
$keyword  = trim($_GET['q'] ?? '');

$titles = [
    'inventory' => 'Inventory Report',
    'movement'  => 'Stock Movement Report',
    'expiration'=> 'Expiration Report',
    'waste'     => 'Waste / Loss Report',
];
if (!array_key_exists($type, $titles)) $type = 'inventory';

switch ($type) {
    case 'movement':   $rows = $reportModel->stockMovementReport($dateFrom, $dateTo, $keyword); break;
    case 'expiration': $rows = $reportModel->expirationReport($dateFrom, $dateTo, $keyword); break;
    case 'waste':      $rows = $reportModel->wasteLossReport($dateFrom, $dateTo, $keyword); break;
    default:           $rows = $reportModel->inventoryReport($dateFrom, $dateTo, $keyword); break;
}

$exportQuery = http_build_query(['report' => $type, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'q' => $keyword]);

$pageTitle = 'Reports';
require __DIR__ . '/app/views/layouts/header.php';
?>
<?php Flash::render(); ?>

<div class="flex gap-2 mb-3 no-print" style="flex-wrap:wrap;">
  <?php foreach ($titles as $key => $label): ?>
    <a class="btn <?= $type === $key ? 'btn-primary' : 'btn-outline' ?> btn-sm" href="<?= APP_URL ?>/reports.php?report=<?= $key ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<div class="card mb-3 no-print">
  <div class="card-body">
    <form method="GET" class="filter-bar mb-0">
      <input type="hidden" name="report" value="<?= Validator::e($type) ?>">
      <div class="form-group"><label>Search</label><input type="search" name="q" placeholder="Product name/code" value="<?= Validator::e($keyword) ?>"></div>
      <div class="form-group"><label>Date From</label><input type="date" name="date_from" value="<?= Validator::e($dateFrom) ?>"></div>
      <div class="form-group"><label>Date To</label><input type="date" name="date_to" value="<?= Validator::e($dateTo) ?>"></div>
      <button type="submit" class="btn btn-outline">Apply</button>
      <a href="<?= APP_URL ?>/reports.php?report=<?= $type ?>" class="btn btn-outline">Reset</a>
      <span style="flex:1;"></span>
      <a class="btn btn-outline" href="<?= APP_URL ?>/export.php?format=csv&<?= $exportQuery ?>">⬇ CSV</a>
      <a class="btn btn-outline" href="<?= APP_URL ?>/export.php?format=xlsx&<?= $exportQuery ?>">⬇ Excel</a>
      <button type="button" class="btn btn-outline" onclick="window.print()">🖨️ Print / Save PDF</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3><?= $titles[$type] ?></h3></div>
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <?php if ($type === 'inventory'): ?>
          <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Supplier</th><th>Qty</th><th>Cost</th><th>Selling</th><th>Value</th><th>Received</th><th>Expiry</th></tr></thead>
          <tbody>
            <?php $total = 0; foreach ($rows as $r): $total += $r['inventory_value']; ?>
              <tr>
                <td><?= Validator::e($r['product_code']) ?></td><td><?= Validator::e($r['name']) ?></td>
                <td><?= Validator::e($r['category_name'] ?? '—') ?></td><td><?= Validator::e($r['supplier_name'] ?? '—') ?></td>
                <td><?= rtrim(rtrim(number_format($r['quantity'],2),'0'),'.') ?> <?= Validator::e($r['unit']) ?></td>
                <td>₱<?= number_format($r['cost_price'],2) ?></td><td>₱<?= number_format($r['selling_price'],2) ?></td>
                <td>₱<?= number_format($r['inventory_value'],2) ?></td>
                <td><?= $r['date_received'] ? date('M j, Y', strtotime($r['date_received'])) : '—' ?></td>
                <td><?= $r['expiration_date'] ? date('M j, Y', strtotime($r['expiration_date'])) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot><tr><td colspan="7" class="text-right"><strong>Total Inventory Value</strong></td><td colspan="2"><strong>₱<?= number_format($total,2) ?></strong></td></tr></tfoot>

        <?php elseif ($type === 'movement'): ?>
          <thead><tr><th>Date</th><th>Code</th><th>Product</th><th>Type</th><th>Qty</th><th>Reason</th><th>By</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= date('M j, Y', strtotime($r['transaction_date'])) ?></td><td><?= Validator::e($r['product_code']) ?></td><td><?= Validator::e($r['name']) ?></td>
                <td><?= $r['type']==='in' ? '<span class="badge badge-green">In</span>' : '<span class="badge badge-red">Out</span>' ?></td>
                <td><?= rtrim(rtrim(number_format($r['quantity'],2),'0'),'.') ?></td><td><?= Validator::e($r['reason'] ?: '—') ?></td><td><?= Validator::e($r['user_name'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>

        <?php elseif ($type === 'expiration'): ?>
          <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Qty</th><th>Expiry Date</th><th>Status</th><th>At-Risk Value</th></tr></thead>
          <tbody>
            <?php $total = 0; foreach ($rows as $r): $status = ProductModel::expiryStatus($r['expiration_date']); $total += $r['at_risk_value']; ?>
              <tr>
                <td><?= Validator::e($r['product_code']) ?></td><td><?= Validator::e($r['name']) ?></td><td><?= Validator::e($r['category_name'] ?? '—') ?></td>
                <td><?= rtrim(rtrim(number_format($r['quantity'],2),'0'),'.') ?> <?= Validator::e($r['unit']) ?></td>
                <td><?= date('M j, Y', strtotime($r['expiration_date'])) ?></td>
                <td><?= $status==='red' ? '<span class="badge badge-red">Expired</span>' : '<span class="badge badge-yellow">Expiring Soon</span>' ?></td>
                <td>₱<?= number_format($r['at_risk_value'],2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot><tr><td colspan="6" class="text-right"><strong>Total At-Risk Value</strong></td><td><strong>₱<?= number_format($total,2) ?></strong></td></tr></tfoot>

        <?php else: ?>
          <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Qty</th><th>Expired On</th><th>Loss Value</th></tr></thead>
          <tbody>
            <?php $total = 0; foreach ($rows as $r): $total += $r['loss_value']; ?>
              <tr>
                <td><?= Validator::e($r['product_code']) ?></td><td><?= Validator::e($r['name']) ?></td><td><?= Validator::e($r['category_name'] ?? '—') ?></td>
                <td><?= rtrim(rtrim(number_format($r['quantity'],2),'0'),'.') ?> <?= Validator::e($r['unit']) ?></td>
                <td><?= date('M j, Y', strtotime($r['expiration_date'])) ?></td>
                <td>₱<?= number_format($r['loss_value'],2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot><tr><td colspan="5" class="text-right"><strong>Total Estimated Loss</strong></td><td><strong>₱<?= number_format($total,2) ?></strong></td></tr></tfoot>
        <?php endif; ?>
      </table>
      <?php if (!$rows): ?><div class="empty-state"><div class="icon">📄</div>No records match the selected filters.</div><?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/app/views/layouts/footer.php'; ?>
