<?php
require_once __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();

$notifService = new NotificationService();
$notifService->sync();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verifyRequest();
    if (($_POST['action'] ?? '') === 'mark_read') {
        $notifService->markRead((int)$_POST['id']);
    } elseif (($_POST['action'] ?? '') === 'mark_all_read') {
        $notifService->markAllRead();
    }
    header('Location: ' . APP_URL . '/notifications.php');
    exit;
}

$db = Database::getConnection();
$all = $db->query(
    "SELECT n.*, p.name AS product_name, p.product_code
     FROM notifications n LEFT JOIN products p ON p.id = n.product_id
     ORDER BY n.is_read ASC, n.created_at DESC LIMIT 200"
)->fetchAll();

$pageTitle = 'Notifications';
require __DIR__ . '/app/views/layouts/header.php';
?>
<?php Flash::render(); ?>

<div class="card">
  <div class="card-header">
    <h3>All Notifications</h3>
    <form method="POST"><?= Csrf::field() ?><input type="hidden" name="action" value="mark_all_read">
      <button class="btn btn-outline btn-sm" type="submit">Mark all as read</button>
    </form>
  </div>
  <div class="card-body">
    <?php if (!$all): ?>
      <div class="empty-state"><div class="icon">✅</div>No notifications yet.</div>
    <?php endif; ?>
    <?php foreach ($all as $n):
      $badge = $n['type'] === 'expired' ? 'badge-red' : ($n['type'] === 'expiring' ? 'badge-yellow' : 'badge-gray');
      $icon = $n['type'] === 'expired' ? '⛔' : ($n['type'] === 'expiring' ? '⏳' : '⚠️');
    ?>
      <div class="flex items-center gap-3" style="padding:12px 0;border-bottom:1px solid var(--color-border); <?= $n['is_read'] ? 'opacity:.55;' : '' ?>">
        <span style="font-size:18px;"><?= $icon ?></span>
        <div style="flex:1;">
          <div><?= Validator::e($n['message']) ?> <?php if ($n['product_code']): ?><span class="muted">(<?= Validator::e($n['product_code']) ?>)</span><?php endif; ?></div>
          <div class="muted" style="font-size:11.5px;"><?= date('M j, Y g:ia', strtotime($n['created_at'])) ?></div>
        </div>
        <span class="badge <?= $badge ?>"><?= Validator::e($n['type']) ?></span>
        <?php if (!$n['is_read']): ?>
          <form method="POST"><?= Csrf::field() ?><input type="hidden" name="action" value="mark_read"><input type="hidden" name="id" value="<?= $n['id'] ?>">
            <button class="btn btn-outline btn-sm" type="submit">Mark read</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/app/views/layouts/footer.php'; ?>
