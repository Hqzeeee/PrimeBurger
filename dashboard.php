<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();

$productModel = new ProductModel();
$txnModel = new InventoryTransactionModel();
$notifService = new NotificationService();

$totalProducts   = $productModel->countTotalProducts();
$totalQuantity   = $productModel->sumTotalQuantity();
$lowStock        = $productModel->countLowStock();
$expiringSoon    = $productModel->countExpiringSoon();
$expired         = $productModel->countExpired();
$inventoryValue  = $productModel->totalInventoryValue();
$estimatedLoss   = $productModel->estimatedExpiredLoss();

$recentIn  = $txnModel->recent(5, 'in');
$recentOut = $txnModel->recent(5, 'out');
$recentProducts = $productModel->recentlyAdded(5);
$recentNotifs = $notifService->recent(6);

// Data for the "stock movement" mini chart (last 7 days, in vs out).
$db = Database::getConnection();
$stmt = $db->prepare(
    "SELECT transaction_date d,
            SUM(CASE WHEN type='in' THEN quantity ELSE 0 END) stock_in,
            SUM(CASE WHEN type='out' THEN quantity ELSE 0 END) stock_out
     FROM inventory_transactions
     WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY transaction_date ORDER BY transaction_date ASC"
);
$stmt->execute();
$movement = $stmt->fetchAll();
$movementMap = [];
foreach ($movement as $m) { $movementMap[$m['d']] = $m; }
$chartLabels = []; $chartIn = []; $chartOut = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} day"));
    $chartLabels[] = date('M j', strtotime($d));
    $chartIn[] = (float)($movementMap[$d]['stock_in'] ?? 0);
    $chartOut[] = (float)($movementMap[$d]['stock_out'] ?? 0);
}

// Expiry monitoring breakdown (green/yellow/red) among active products.
$expiryBreakdown = [
    'green'  => $totalProducts - $lowStock >= 0 ? null : null, // placeholder not used
];
$greenCount = $db->query("SELECT COUNT(*) c FROM products WHERE status='active' AND (expiration_date IS NULL OR expiration_date > DATE_ADD(CURDATE(), INTERVAL " . EXPIRY_WARNING_DAYS . " DAY))")->fetch()['c'];

$pageTitle = 'Dashboard';
require __DIR__ . '/app/views/layouts/header.php';
?>
<?php Flash::render(); ?>

<div class="stat-grid">
  <div class="stat-card accent-primary">
    <div class="stat-icon">📦</div>
    <div class="stat-value"><?= number_format($totalProducts) ?></div>
    <div class="stat-label">Total Products</div>
  </div>
  <div class="stat-card accent-primary">
    <div class="stat-icon">🧮</div>
    <div class="stat-value"><?= number_format($totalQuantity, 0) ?></div>
    <div class="stat-label">Total Inventory Quantity</div>
  </div>
  <div class="stat-card accent-yellow">
    <div class="stat-icon">⚠️</div>
    <div class="stat-value"><?= number_format($lowStock) ?></div>
    <div class="stat-label">Low Stock Items</div>
  </div>
  <div class="stat-card accent-yellow">
    <div class="stat-icon">⏳</div>
    <div class="stat-value"><?= number_format($expiringSoon) ?></div>
    <div class="stat-label">Expiring Soon</div>
  </div>
  <div class="stat-card accent-red">
    <div class="stat-icon">⛔</div>
    <div class="stat-value"><?= number_format($expired) ?></div>
    <div class="stat-label">Expired Products</div>
  </div>
  <?php if (Auth::isOwner()): ?>
  <div class="stat-card accent-green">
    <div class="stat-icon">💰</div>
    <div class="stat-value">₱<?= number_format($inventoryValue, 2) ?></div>
    <div class="stat-label">Total Inventory Value</div>
  </div>
  <?php endif; ?>
</div>

<div class="grid-2">
  <div class="card mb-3">
    <div class="card-header">
      <h3>Stock Movement — Last 7 Days</h3>
    </div>
    <div class="card-body">
      <canvas id="movementChart" height="110"></canvas>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h3>Expiry Monitoring</h3></div>
    <div class="card-body">
      <canvas id="expiryChart" height="150"></canvas>
      <div class="mt-0" style="margin-top:14px;font-size:12.5px;">
        <div class="flex items-center gap-2 mb-2"><span class="dot dot-green"></span> Safe — <?= $greenCount ?></div>
        <div class="flex items-center gap-2 mb-2"><span class="dot dot-yellow"></span> Expiring soon — <?= $expiringSoon ?></div>
        <div class="flex items-center gap-2"><span class="dot dot-red"></span> Expired — <?= $expired ?></div>
      </div>
      <?php if (Auth::isOwner() && $estimatedLoss > 0): ?>
        <div class="alert alert-error" style="margin-top:14px;">
          Estimated loss from expired stock: <strong>₱<?= number_format($estimatedLoss, 2) ?></strong>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="card mb-3">
    <div class="card-header">
      <h3>Recent Activity</h3>
      <a href="<?= APP_URL ?>/stock.php" class="btn btn-outline btn-sm">View Stock Log</a>
    </div>
    <div class="card-body">
      <div class="grid-cols-2">
        <div>
          <div class="muted mb-2" style="font-weight:600;">Latest Stock-In</div>
          <?php if (!$recentIn): ?><p class="muted">No stock-in records yet.</p><?php endif; ?>
          <?php foreach ($recentIn as $t): ?>
            <div class="flex justify-between" style="padding:6px 0;border-bottom:1px solid var(--color-border);font-size:13px;">
              <span><?= Validator::e($t['product_name']) ?></span>
              <span class="badge badge-green">+<?= rtrim(rtrim(number_format($t['quantity'], 2), '0'), '.') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <div>
          <div class="muted mb-2" style="font-weight:600;">Latest Stock-Out</div>
          <?php if (!$recentOut): ?><p class="muted">No stock-out records yet.</p><?php endif; ?>
          <?php foreach ($recentOut as $t): ?>
            <div class="flex justify-between" style="padding:6px 0;border-bottom:1px solid var(--color-border);font-size:13px;">
              <span><?= Validator::e($t['product_name']) ?></span>
              <span class="badge badge-red">-<?= rtrim(rtrim(number_format($t['quantity'], 2), '0'), '.') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="muted mb-2" style="font-weight:600;margin-top:16px;">Recently Added Products</div>
      <?php foreach ($recentProducts as $p): ?>
        <div class="flex justify-between" style="padding:6px 0;border-bottom:1px solid var(--color-border);font-size:13px;">
          <span><?= Validator::e($p['name']) ?> <span class="muted">(<?= Validator::e($p['product_code']) ?>)</span></span>
          <span class="muted"><?= date('M j, Y', strtotime($p['created_at'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">
      <h3>Notifications</h3>
      <a href="<?= APP_URL ?>/notifications.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body">
      <?php if (!$recentNotifs): ?>
        <div class="empty-state"><div class="icon">✅</div>All clear — no active alerts.</div>
      <?php endif; ?>
      <?php foreach ($recentNotifs as $n):
          $badge = $n['type'] === 'expired' ? 'badge-red' : ($n['type'] === 'expiring' ? 'badge-yellow' : 'badge-gray');
          $icon = $n['type'] === 'expired' ? '⛔' : ($n['type'] === 'expiring' ? '⏳' : '⚠️');
      ?>
        <div class="flex items-center gap-2" style="padding:8px 0;border-bottom:1px solid var(--color-border);">
          <span><?= $icon ?></span>
          <div style="flex:1;">
            <div style="font-size:13px;"><?= Validator::e($n['message']) ?></div>
            <div class="muted" style="font-size:11.5px;"><?= date('M j, Y g:ia', strtotime($n['created_at'])) ?></div>
          </div>
          <span class="badge <?= $badge ?>"><?= Validator::e($n['type']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php
$extraScript = "<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js'></script>
<script>
new Chart(document.getElementById('movementChart'), {
  type: 'line',
  data: {
    labels: " . json_encode($chartLabels) . ",
    datasets: [
      { label: 'Stock In', data: " . json_encode($chartIn) . ", borderColor: '#1e9e5a', backgroundColor:'rgba(30,158,90,.12)', tension:.3, fill:true },
      { label: 'Stock Out', data: " . json_encode($chartOut) . ", borderColor: '#d9332b', backgroundColor:'rgba(217,51,43,.10)', tension:.3, fill:true }
    ]
  },
  options: { responsive:true, plugins:{legend:{position:'bottom'}} }
});
new Chart(document.getElementById('expiryChart'), {
  type: 'doughnut',
  data: {
    labels: ['Safe', 'Expiring Soon', 'Expired'],
    datasets: [{ data: [" . (int)$greenCount . "," . (int)$expiringSoon . "," . (int)$expired . "], backgroundColor: ['#1e9e5a','#e6b800','#d9332b'] }]
  },
  options: { plugins:{legend:{display:false}} }
});
</script>";
require __DIR__ . '/app/views/layouts/footer.php';
