<?php
/**
 * NotificationService
 * Scans the product catalog for low-stock, expiring-soon, and expired
 * items and keeps the `notifications` table in sync. Called once per
 * dashboard/notifications page load, so no external cron job is required
 * (PB-02: automatic notifications for expiring/expired/low-stock items).
 */
class NotificationService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function sync(): void
    {
        $this->syncLowStock();
        $this->syncExpiring();
        $this->syncExpired();
    }

    private function alreadyNotified(string $type, int $productId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM notifications WHERE type = ? AND product_id = ? AND is_read = 0 LIMIT 1'
        );
        $stmt->execute([$type, $productId]);
        return (bool)$stmt->fetch();
    }

    private function insert(string $type, int $productId, string $message): void
    {
        if ($this->alreadyNotified($type, $productId)) {
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (type, product_id, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$type, $productId, $message]);
    }

    private function syncLowStock(): void
    {
        $stmt = $this->db->query(
            "SELECT id, name FROM products
             WHERE status = 'active' AND quantity <= minimum_stock_level"
        );
        foreach ($stmt->fetchAll() as $p) {
            $this->insert('low_stock', (int)$p['id'], "{$p['name']} is at or below the minimum stock level.");
        }
    }

    private function syncExpiring(): void
    {
        $stmt = $this->db->prepare(
            "SELECT id, name FROM products
             WHERE status = 'active' AND expiration_date IS NOT NULL
             AND expiration_date >= CURDATE()
             AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)"
        );
        $stmt->execute([EXPIRY_WARNING_DAYS]);
        foreach ($stmt->fetchAll() as $p) {
            $this->insert('expiring', (int)$p['id'], "{$p['name']} is nearing its expiration date.");
        }
    }

    private function syncExpired(): void
    {
        $stmt = $this->db->query(
            "SELECT id, name FROM products
             WHERE status = 'active' AND expiration_date IS NOT NULL AND expiration_date < CURDATE()"
        );
        foreach ($stmt->fetchAll() as $p) {
            $this->insert('expired', (int)$p['id'], "{$p['name']} has expired.");
        }
    }

    public function unreadCount(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) c FROM notifications WHERE is_read = 0');
        return (int)$stmt->fetch()['c'];
    }

    public function recent(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            'SELECT n.*, p.name AS product_name, p.product_code
             FROM notifications n LEFT JOIN products p ON p.id = n.product_id
             ORDER BY n.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markRead(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function markAllRead(): void
    {
        $this->db->exec('UPDATE notifications SET is_read = 1 WHERE is_read = 0');
    }
}
