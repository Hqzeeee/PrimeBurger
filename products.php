<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/controllers/ProductController.php';
Auth::requireLogin();

$productModel  = new ProductModel();
$categoryModel = new CategoryModel();
$supplierModel = new SupplierModel();
$controller    = new ProductController();

$formErrors = [];
$openModal  = null; // 'add' | 'edit'
$editProduct = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        [$ok, $msg, $formErrors] = $controller->store($_POST);
        if ($ok) {
            Flash::set('success', $msg);
            header('Location: ' . APP_URL . '/products.php');
            exit;
        }
        $openModal = 'add';
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        [$ok, $msg, $formErrors] = $controller->update($id, $_POST);
        if ($ok) {
            Flash::set('success', $msg);
            header('Location: ' . APP_URL . '/products.php');
            exit;
        }
        $openModal = 'edit';
        $editProduct = $productModel->findWithRelations($id);
    } elseif ($action === 'archive' && Auth::isOwner()) {
        $controller->archive((int)($_POST['id'] ?? 0));
        Flash::set('success', 'Product archived.');
        header('Location: ' . APP_URL . '/products.php');
        exit;
    }
}

if (isset($_GET['edit']) && !$editProduct) {
    $editProduct = $productModel->findWithRelations((int)$_GET['edit']);
    if ($editProduct) $openModal = 'edit';
}

$filters = [
    'keyword'      => trim($_GET['q'] ?? ''),
    'category_id'  => $_GET['category_id'] ?? '',
    'supplier_id'  => $_GET['supplier_id'] ?? '',
    'stock_status' => $_GET['stock_status'] ?? '',
    'expiry_status'=> $_GET['expiry_status'] ?? '',
];
$products   = $productModel->search($filters);
$categories = $categoryModel->all('name ASC');
$suppliers  = $supplierModel->all('name ASC');

$pageTitle = 'Products';
require __DIR__ . '/app/views/layouts/header.php';
?>
<?php Flash::render(); ?>

<div class="flex justify-between items-center mb-3">
  <form method="GET" class="filter-bar mb-0" style="flex:1;">
    <div class="form-group">
      <label>Search</label>
      <input type="search" name="q" placeholder="Name, code, or QR code" value="<?= Validator::e($filters['keyword']) ?>">
    </div>
    <div class="form-group">
      <label>Category</label>
      <select name="category_id">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (string)$filters['category_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= Validator::e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Stock Status</label>
      <select name="stock_status">
        <option value="">All</option>
        <option value="low" <?= $filters['stock_status']==='low'?'selected':'' ?>>Low Stock Only</option>
      </select>
    </div>
    <div class="form-group">
      <label>Expiry Status</label>
      <select name="expiry_status">
        <option value="">All</option>
        <option value="green" <?= $filters['expiry_status']==='green'?'selected':'' ?>>🟢 Safe</option>
        <option value="yellow" <?= $filters['expiry_status']==='yellow'?'selected':'' ?>>🟡 Expiring Soon</option>
        <option value="red" <?= $filters['expiry_status']==='red'?'selected':'' ?>>🔴 Expired</option>
      </select>
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <a href="<?= APP_URL ?>/products.php" class="btn btn-outline">Reset</a>
  </form>
  <button class="btn btn-primary" style="margin-left:12px;" data-modal-open="productModal" onclick="resetProductForm()">+ Add Product</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Code</th><th>Product</th><th>Category</th><th>Supplier</th>
          <th>Qty</th><th>Cost</th><th>Selling</th><th>Expiration</th><th>Status</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$products): ?>
          <tr><td colspan="10"><div class="empty-state"><div class="icon">📦</div>No products found. Try adjusting your filters or add a new product.</div></td></tr>
        <?php endif; ?>
        <?php foreach ($products as $p):
            $expiry = ProductModel::expiryStatus($p['expiration_date']);
            $lowStock = $p['quantity'] <= $p['minimum_stock_level'];
        ?>
        <tr>
          <td><?= Validator::e($p['product_code']) ?></td>
          <td><strong><?= Validator::e($p['name']) ?></strong></td>
          <td><?= Validator::e($p['category_name'] ?? '—') ?></td>
          <td><?= Validator::e($p['supplier_name'] ?? '—') ?></td>
          <td>
            <?= rtrim(rtrim(number_format($p['quantity'], 2), '0'), '.') ?> <?= Validator::e($p['unit']) ?>
            <?php if ($lowStock): ?><br><span class="badge badge-yellow">Low</span><?php endif; ?>
          </td>
          <td>₱<?= number_format($p['cost_price'], 2) ?></td>
          <td>₱<?= number_format($p['selling_price'], 2) ?></td>
          <td>
            <?php if ($p['expiration_date']): ?>
              <?= date('M j, Y', strtotime($p['expiration_date'])) ?><br>
              <?php if ($expiry === 'red'): ?><span class="badge badge-red">Expired</span>
              <?php elseif ($expiry === 'yellow'): ?><span class="badge badge-yellow">Expiring Soon</span>
              <?php else: ?><span class="badge badge-green">Safe</span><?php endif; ?>
            <?php else: ?><span class="muted">No expiry</span><?php endif; ?>
          </td>
          <td><span class="badge badge-gray"><?= Validator::e($p['status']) ?></span></td>
          <td>
            <div class="flex gap-2">
              <button class="btn btn-outline btn-sm" data-modal-open="viewModal-<?= $p['id'] ?>">View</button>
              <a href="<?= APP_URL ?>/products.php?edit=<?= $p['id'] ?>#productModal" class="btn btn-outline btn-sm" onclick="document.getElementById('productModal').classList.add('open')">Edit</a>
              <?php if (Auth::isOwner()): ?>
              <form method="POST" data-confirm="Archive this product? It will be hidden from active inventory.">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="archive">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Archive</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>

        <!-- View / QR modal -->
        <div class="modal-backdrop" id="viewModal-<?= $p['id'] ?>">
          <div class="modal">
            <div class="modal-header"><h3><?= Validator::e($p['name']) ?></h3><button class="modal-close" data-modal-close>✕</button></div>
            <div class="modal-body">
              <div class="flex gap-3 items-center mb-3">
                <div class="qr-box">
                  <div id="qr-<?= $p['id'] ?>"></div>
                  <div class="code"><?= Validator::e($p['qr_code']) ?></div>
                </div>
                <div style="font-size:13px;">
                  <div><strong>Code:</strong> <?= Validator::e($p['product_code']) ?></div>
                  <div><strong>Category:</strong> <?= Validator::e($p['category_name'] ?? '—') ?></div>
                  <div><strong>Supplier:</strong> <?= Validator::e($p['supplier_name'] ?? '—') ?></div>
                  <div><strong>Quantity:</strong> <?= rtrim(rtrim(number_format($p['quantity'], 2), '0'), '.') ?> <?= Validator::e($p['unit']) ?></div>
                  <div><strong>Min. Stock:</strong> <?= rtrim(rtrim(number_format($p['minimum_stock_level'], 2), '0'), '.') ?></div>
                  <div><strong>Received:</strong> <?= $p['date_received'] ? date('M j, Y', strtotime($p['date_received'])) : '—' ?></div>
                  <div><strong>Expires:</strong> <?= $p['expiration_date'] ? date('M j, Y', strtotime($p['expiration_date'])) : '—' ?></div>
                </div>
              </div>
              <button class="btn btn-outline btn-sm" onclick="printQr('<?= $p['id'] ?>', '<?= Validator::e(addslashes($p['name'])) ?>', '<?= Validator::e($p['product_code']) ?>')">🖨️ Print QR Label</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Product Modal -->
<div class="modal-backdrop <?= $openModal ? 'open' : '' ?>" id="productModal">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <h3 id="productModalTitle"><?= $openModal === 'edit' ? 'Edit Product' : 'Add Product' ?></h3>
      <button class="modal-close" data-modal-close>✕</button>
    </div>
    <div class="modal-body">
      <form method="POST" id="productForm">
        <?= Csrf::field() ?>
        <input type="hidden" name="action" id="formAction" value="<?= $openModal === 'edit' ? 'update' : 'create' ?>">
        <input type="hidden" name="id" id="productId" value="<?= $editProduct['id'] ?? '' ?>">

        <div class="form-row">
          <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="name" id="f_name" required value="<?= Validator::e($editProduct['name'] ?? '') ?>">
            <?php if (!empty($formErrors['name'])): ?><div class="field-error"><?= Validator::e($formErrors['name']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label>Unit *</label>
            <input type="text" name="unit" id="f_unit" placeholder="pcs, kg, pack..." required value="<?= Validator::e($editProduct['unit'] ?? 'pcs') ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Category</label>
            <select name="category_id" id="f_category">
              <option value="">— None —</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= Validator::e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Supplier</label>
            <select name="supplier_id" id="f_supplier">
              <option value="">— None —</option>
              <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($editProduct['supplier_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= Validator::e($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group" id="qtyGroup">
            <label>Initial Quantity <span id="qtyReq">*</span></label>
            <input type="number" step="0.01" min="0" name="quantity" id="f_qty" value="<?= Validator::e((string)($editProduct['quantity'] ?? 0)) ?>">
            <div class="help-text" id="qtyHelp" style="display:none;">Use Stock Management to change quantity after creation.</div>
          </div>
          <div class="form-group">
            <label>Minimum Stock Level *</label>
            <input type="number" step="0.01" min="0" name="minimum_stock_level" required id="f_min" value="<?= Validator::e((string)($editProduct['minimum_stock_level'] ?? 0)) ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Cost Price (₱) *</label>
            <input type="number" step="0.01" min="0" name="cost_price" required id="f_cost" value="<?= Validator::e((string)($editProduct['cost_price'] ?? 0)) ?>">
          </div>
          <div class="form-group">
            <label>Selling Price (₱)</label>
            <input type="number" step="0.01" min="0" name="selling_price" id="f_sell" value="<?= Validator::e((string)($editProduct['selling_price'] ?? 0)) ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Date Received</label>
            <input type="date" name="date_received" id="f_received" value="<?= Validator::e($editProduct['date_received'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Expiration Date</label>
            <input type="date" name="expiration_date" id="f_expiry" value="<?= Validator::e($editProduct['expiration_date'] ?? '') ?>">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Save Product</button>
      </form>
    </div>
  </div>
</div>

<?php
$extraScript = "<script src='https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js'></script>
<script>
" . implode("\n", array_map(function($p) {
    return "new QRCode(document.getElementById('qr-{$p['id']}'), { text: " . json_encode($p['qr_code']) . ", width:110, height:110 });";
}, $products)) . "

function resetProductForm() {
  document.getElementById('productModalTitle').innerText = 'Add Product';
  document.getElementById('formAction').value = 'create';
  document.getElementById('productForm').reset();
  document.getElementById('productId').value = '';
  document.getElementById('qtyGroup').style.display = '';
  document.getElementById('qtyReq').style.display = '';
  document.getElementById('qtyHelp').style.display = 'none';
  document.getElementById('f_qty').removeAttribute('readonly');
}
function printQr(id, name, code) {
  const svg = document.getElementById('qr-' + id).innerHTML;
  const w = window.open('', '_blank', 'width=380,height=420');
  w.document.write('<html><head><title>QR Label</title><style>body{font-family:Arial;text-align:center;padding:24px;}h3{margin:6px 0;}p{margin:2px 0;font-size:12px;color:#555;}</style></head><body>' + svg + '<h3>' + name + '</h3><p>' + code + '</p></body></html>');
  w.document.close(); w.focus(); w.print();
}
" . ($openModal === 'edit' && $editProduct ? "
document.getElementById('qtyGroup').style.display='none';
" : "") . "
</script>";
require __DIR__ . '/app/views/layouts/footer.php';
