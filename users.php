<?php
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/controllers/UserController.php';
Auth::requireRole(['owner']);

$userModel = new UserModel();
$controller = new UserController();
$openModal = null;
$editUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        [$ok, $msg] = $controller->store($_POST);
        Flash::set($ok ? 'success' : 'error', $msg);
        if (!$ok) $openModal = 'add';
        else { header('Location: ' . APP_URL . '/users.php'); exit; }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        [$ok, $msg] = $controller->update($id, $_POST);
        Flash::set($ok ? 'success' : 'error', $msg);
        if (!$ok) { $openModal = 'edit'; $editUser = $userModel->find($id); }
        else { header('Location: ' . APP_URL . '/users.php'); exit; }
    }
}

if (isset($_GET['edit']) && !$editUser) {
    $editUser = $userModel->find((int)$_GET['edit']);
    if ($editUser) $openModal = 'edit';
}

$users = $userModel->all('full_name ASC');

$pageTitle = 'Users';
require __DIR__ . '/app/views/layouts/header.php';
?>
<?php Flash::render(); ?>

<div class="flex justify-between items-center mb-3">
  <p class="muted mb-0">Manage owner/admin and inventory staff accounts.</p>
  <button class="btn btn-primary" data-modal-open="userModal" onclick="resetUserForm()">+ Add User</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Full Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= Validator::e($u['full_name']) ?></td>
          <td><?= Validator::e($u['username']) ?></td>
          <td><?= Validator::e($u['email']) ?></td>
          <td><span class="badge <?= $u['role']==='owner'?'badge-green':'badge-gray' ?>"><?= Validator::e($u['role']) ?></span></td>
          <td><span class="badge <?= $u['status']==='active'?'badge-green':'badge-red' ?>"><?= Validator::e($u['status']) ?></span></td>
          <td><?= $u['last_login_at'] ? date('M j, Y g:ia', strtotime($u['last_login_at'])) : 'Never' ?></td>
          <td><a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/users.php?edit=<?= $u['id'] ?>" onclick="document.getElementById('userModal').classList.add('open')">Edit</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-backdrop <?= $openModal ? 'open' : '' ?>" id="userModal">
  <div class="modal">
    <div class="modal-header"><h3 id="userModalTitle"><?= $openModal==='edit'?'Edit User':'Add User' ?></h3><button class="modal-close" data-modal-close>✕</button></div>
    <div class="modal-body">
      <form method="POST">
        <?= Csrf::field() ?>
        <input type="hidden" name="action" value="<?= $openModal==='edit'?'update':'create' ?>">
        <input type="hidden" name="id" value="<?= $editUser['id'] ?? '' ?>">
        <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required value="<?= Validator::e($editUser['full_name'] ?? '') ?>"></div>
        <div class="form-row">
          <div class="form-group"><label>Username *</label><input type="text" name="username" required value="<?= Validator::e($editUser['username'] ?? '') ?>"></div>
          <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?= Validator::e($editUser['email'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
              <option value="staff" <?= ($editUser['role'] ?? '')==='staff'?'selected':'' ?>>Inventory Staff</option>
              <option value="owner" <?= ($editUser['role'] ?? '')==='owner'?'selected':'' ?>>Owner / Admin</option>
            </select>
          </div>
          <?php if ($openModal === 'edit'): ?>
          <div class="form-group">
            <label>Status *</label>
            <select name="status" required>
              <option value="active" <?= ($editUser['status'] ?? '')==='active'?'selected':'' ?>>Active</option>
              <option value="disabled" <?= ($editUser['status'] ?? '')==='disabled'?'selected':'' ?>>Disabled</option>
            </select>
          </div>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label><?= $openModal==='edit' ? 'New Password (leave blank to keep current)' : 'Password *' ?></label>
          <input type="password" name="password" <?= $openModal==='edit' ? '' : 'required' ?> minlength="8">
          <div class="help-text">At least 8 characters.</div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save User</button>
      </form>
    </div>
  </div>
</div>

<?php
$extraScript = "<script>
function resetUserForm(){
  document.getElementById('userModalTitle').innerText='Add User';
  document.querySelector('#userModal form').reset();
  document.querySelector('#userModal input[name=action]').value='create';
  document.querySelector('#userModal input[name=id]').value='';
}
</script>";
require __DIR__ . '/app/views/layouts/footer.php';
