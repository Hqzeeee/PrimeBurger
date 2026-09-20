<?php
require_once __DIR__ . '/../core/Model.php';

class InventoryTransactionModel extends Model
{
    protected string $table = 'inventory_transactions';

    private const BASE_SELECT = "SELECT t.*, p.name AS product_name, p.product_code, u.full_name AS user_name
        FROM inventory_transactions t
        LEFT JOIN products p ON p.id = t.product_id
        LEFT JOIN users u ON u.id = t.user_id";

    /**
     * Record a stock movement and atomically update the product quantity
     * plus the stock_history audit row. Wrapped in a DB transaction so a
     * failure never leaves quantity and ledger out of sync.
     */
    public function record(int $productId, string $type, float $quantity, string $reason, string $date, ?int $userId): bool
    {
        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (!$product) {
            return false;
        }

        $oldQty = (float)$product['quantity'];
        $newQty = $type === 'in' ? $oldQty + $quantity : $oldQty - $quantity;

        if ($type === 'out' && $newQty < 0) {
            throw new InvalidArgumentException('Stock-out quantity exceeds available stock.');
        }

        $this->db->beginTransaction();
        try {
            $this->query(
                'INSERT INTO inventory_transactions (product_id, type, quantity, reason, transaction_date, user_id)
                 VALUES (?,?,?,?,?,?)',
                [$productId, $type, $quantity, $reason, $date, $userId]
            );

            $this->query('UPDATE products SET quantity = ? WHERE id = ?', [$newQty, $productId]);

            $this->query(
                'INSERT INTO stock_history (product_id, old_quantity, new_quantity, change_type, changed_by)
                 VALUES (?,?,?,?,?)',
                [$productId, $oldQty, $newQty, $type, $userId]
            );

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recent(int $limit = 10, ?string $type = null): array
    {
        $sql = self::BASE_SELECT;
        $params = [];
        if ($type) {
            $sql .= ' WHERE t.type = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY t.created_at DESC LIMIT ' . (int)$limit;
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function history(array $filters = []): array
    {
        $sql = self::BASE_SELECT . ' WHERE 1=1';
        $params = [];

        if (!empty($filters['product_id'])) {
            $sql .= ' AND t.product_id = ?';
            $params[] = $filters['product_id'];
        }
        if (!empty($filters['type'])) {
            $sql .= ' AND t.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND t.transaction_date >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND t.transaction_date <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['keyword'])) {
            $sql .= ' AND (p.name LIKE ? OR p.product_code LIKE ?)';
            $kw = '%' . $filters['keyword'] . '%';
            $params[] = $kw;
            $params[] = $kw;
        }

        $sql .= ' ORDER BY t.transaction_date DESC, t.id DESC';
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
}
