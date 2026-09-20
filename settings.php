<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/controllers/UserController.php';
Auth::requireLogin();

$userController = new UserController();
$categoryModel = new CategoryModel();
$supplierModel = new SupplierModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        [$ok, $msg] = $userController->changeOwnPassword((int)Auth::id(), $_POST);
        Flash::set($ok ? 'success' : 'error', $msg);
    } elseif ($action === 'add_category' && Auth::isOwner()) {
        Csrf::verifyRequest();
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Category name is required.');
        } elseif ($categoryModel->nameExists($name)) {
            Flash::set('error', 'That category already exists.');
        } else {
            $categoryModel->create($name, trim($_POST['description'] ?? '') ?: null);
            Flash::set('success', 'Category added.');
        }
    } elseif ($action === 'add_supplier' && Auth::isOwner()) {
        Csrf::verifyRequest();
        $data = Validator::clean($_POST);
        if (empty($data['name'])) {
            Flash::set('error', 'Supplier name is required.');
        } else {
            $supplierModel->create($data);
            Flash::set('success', 'Supplier added.');
        }
    }
    header('Location: ' . APP_URL . '/settings.php');
    exit;
}

$categories = $categoryModel->all('name ASC');
$suppliers = $supplierModel->all('name ASC');

$pageTitle = 'Settings';
require __DIR__ . '/app/views/layouts/header.php';
?>
<?php Flash::render(); ?>

<div class="grid-2">
  <div class="card mb-3">
    <div class="card-header"><h3>My Account</h3></div>
    <div class="card-body">
      <p style="margin-top:0;"><strong><?= Validator::e(Auth::fullName()) ?></strong> <span class="badge badge-gray"><?= Validator::e(Auth::role()) ?></span></p>
      <form method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="action" value="change_password">
        <div class="form-group"><label>Current Password</label><input type="password" name="current_password" required></div>
        <div class="form-group"><label>New Password</label><input type="password" name="new_password" minlength="8" required></div>
        <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" minlength="8" required></div>
        <button type="submit" class="btn btn-primary">Change Password</button>
      </form>
    </div>
  </div>

  <?php if (Auth::isOwner()): ?>
  <div class="card mb-3">
    <div class="card-header"><h3>Categories</h3></div>
    <div class="card-body">
      <form method="POST" class="flex gap-2 mb-3">
        <?= Csrf::field() ?>
        <input type="hidden" name="action" value="add_category">
        <input type="text" name="name" placeholder="New category name" required style="flex:1;">
        <button type="submit" class="btn btn-outline">Add</button>
      </form>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Name</th><th>Description</th></tr></thead>
          <tbody>
            <?php foreach ($categories as $c): ?>
              <tr><td><?= Validator::e($c['name']) ?></td><td class="muted"><?= Validator::e($c['description'] ?? '') ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php if (Auth::isOwner()): ?>
<div class="card">
  <div class="card-header"><h3>Suppliers</h3></div>
  <div class="card-body">
    <form method="POST" class="form-row mb-3">
      <?= Csrf::field() ?>
      <input type="hidden" name="action" value="add_supplier">
      <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person"></div>
      <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
      <div class="form-group"><label>Email</label><input type="email" name="email"></div>
      <div class="form-group" style="grid-column:1/-1;"><label>Address</label><input type="text" name="address"></div>
      <div style="grid-column:1/-1;"><button type="submit" class="btn btn-outline">Add Supplier</button></div>
    </form>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Address</th></tr></thead>
        <tbody>
          <?php foreach ($suppliers as $s): ?>
            <tr>
              <td><?= Validator::e($s['name']) ?></td>
              <td><?= Validator::e($s['contact_person'] ?? '—') ?></td>
              <td><?= Validator::e($s['phone'] ?? '—') ?></td>
              <td><?= Validator::e($s['email'] ?? '—') ?></td>
              <td><?= Validator::e($s['address'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/app/views/layouts/footer.php'; ?>
