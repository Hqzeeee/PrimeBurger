<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/controllers/StockController.php';
Auth::requireLogin();

$productModel = new ProductModel();
$txnModel = new InventoryTransactionModel();
$controller = new StockController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $msg] = $controller->record($_POST);
    Flash::set($ok ? 'success' : 'error', $msg);
    header('Location: ' . APP_URL . '/stock.php');
    exit;
}

$filters = [
    'keyword'   => trim($_GET['q'] ?? ''),
    'type'      => $_GET['type'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? '',
];
$history  = $txnModel->history($filters);
$products = $productModel->allActive();

$pageTitle = 'Stock Management';
require __DIR__ . '/app/views/layouts/header.php';
?>
<?php Flash::render(); ?>

<div class="grid-2">
  <div class="card mb-3">
    <div class="card-header"><h3>Transaction History</h3></div>
    <div class="card-body">
      <form method="GET" class="filter-bar">
        <div class="form-group"><label>Search</label><input type="search" name="q" placeholder="Product name/code" value="<?= Validator::e($filters['keyword']) ?>"></div>
        <div class="form-group">
          <label>Type</label>
          <select name="type">
            <option value="">All</option>
            <option value="in" <?= $filters['type']==='in'?'selected':'' ?>>Stock In</option>
            <option value="out" <?= $filters['type']==='out'?'selected':'' ?>>Stock Out</option>
          </select>
        </div>
        <div class="form-group"><label>From</label><input type="date" name="date_from" value="<?= Validator::e($filters['date_from']) ?>"></div>
        <div class="form-group"><label>To</label><input type="date" name="date_to" value="<?= Validator::e($filters['date_to']) ?>"></div>
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="<?= APP_URL ?>/stock.php" class="btn btn-outline">Reset</a>
      </form>

      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>Reason</th><th>By</th></tr></thead>
          <tbody>
            <?php if (!$history): ?><tr><td colspan="7"><div class="empty-state"><div class="icon">🔄</div>No transactions recorded yet.</div></td></tr><?php endif; ?>
            <?php foreach ($history as $t): ?>
            <tr>
              <td>#<?= $t['id'] ?></td>
              <td><?= date('M j, Y', strtotime($t['transaction_date'])) ?></td>
              <td><?= Validator::e($t['product_name']) ?> <span class="muted">(<?= Validator::e($t['product_code']) ?>)</span></td>
              <td><?php if ($t['type']==='in'): ?><span class="badge badge-green">Stock In</span><?php else: ?><span class="badge badge-red">Stock Out</span><?php endif; ?></td>
              <td><?= rtrim(rtrim(number_format($t['quantity'], 2), '0'), '.') ?></td>
              <td><?= Validator::e($t['reason'] ?: '—') ?></td>
              <td><?= Validator::e($t['user_name'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div>
    <div class="card mb-3">
      <div class="card-header"><h3>Record Stock In</h3></div>
      <div class="card-body">
        <form method="POST">
          <?= Csrf::field() ?>
          <input type="hidden" name="type" value="in">
          <div class="form-group">
            <label>Product *</label>
            <select name="product_id" required>
              <option value="">Select product…</option>
              <?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>"><?= Validator::e($p['name']) ?> (<?= Validator::e($p['product_code']) ?>)</option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Quantity *</label><input type="number" step="0.01" min="0.01" name="quantity" required></div>
            <div class="form-group"><label>Date *</label><input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></div>
          </div>
          <div class="form-group"><label>Supplier / Reason</label><input type="text" name="reason" placeholder="e.g. Weekly delivery from supplier"></div>
          <button type="submit" class="btn btn-primary btn-block">Record Stock In</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Record Stock Out</h3></div>
      <div class="card-body">
        <form method="POST">
          <?= Csrf::field() ?>
          <input type="hidden" name="type" value="out">
          <div class="form-group">
            <label>Product *</label>
            <select name="product_id" required>
              <option value="">Select product…</option>
              <?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>"><?= Validator::e($p['name']) ?> (<?= rtrim(rtrim(number_format($p['quantity'],2),'0'),'.') ?> <?= Validator::e($p['unit']) ?> available)</option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Quantity *</label><input type="number" step="0.01" min="0.01" name="quantity" required></div>
            <div class="form-group"><label>Date *</label><input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></div>
          </div>
          <div class="form-group"><label>Reason *</label><input type="text" name="reason" placeholder="e.g. Daily sales usage, spoilage" required></div>
          <button type="submit" class="btn btn-primary btn-block">Record Stock Out</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/app/views/layouts/footer.php'; ?>
