<?php
require_once __DIR__ . '/../core/Model.php';

class ProductModel extends Model
{
    protected string $table = 'products';

    private const BASE_SELECT = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN suppliers s ON s.id = p.supplier_id";

    public function findWithRelations(int $id): array|false
    {
        $stmt = $this->query(self::BASE_SELECT . ' WHERE p.id = ? LIMIT 1', [$id]);
        return $stmt->fetch();
    }

    public function findByQrCode(string $qrCode): array|false
    {
        $stmt = $this->query(self::BASE_SELECT . ' WHERE p.qr_code = ? LIMIT 1', [$qrCode]);
        return $stmt->fetch();
    }

    /**
     * Search + filter product list.
     * $filters keys: keyword, category_id, supplier_id, expiry_status (green|yellow|red), stock_status (low)
     */
    public function search(array $filters = []): array
    {
        $sql = self::BASE_SELECT . " WHERE p.status = 'active'";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= ' AND (p.name LIKE ? OR p.product_code LIKE ? OR p.qr_code LIKE ?)';
            $kw = '%' . $filters['keyword'] . '%';
            array_push($params, $kw, $kw, $kw);
        }
        if (!empty($filters['category_id'])) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['supplier_id'])) {
            $sql .= ' AND p.supplier_id = ?';
            $params[] = $filters['supplier_id'];
        }
        if (!empty($filters['stock_status']) && $filters['stock_status'] === 'low') {
            $sql .= ' AND p.quantity <= p.minimum_stock_level';
        }
        if (!empty($filters['expiry_status'])) {
            if ($filters['expiry_status'] === 'red') {
                $sql .= ' AND p.expiration_date IS NOT NULL AND p.expiration_date < CURDATE()';
            } elseif ($filters['expiry_status'] === 'yellow') {
                $sql .= ' AND p.expiration_date IS NOT NULL AND p.expiration_date >= CURDATE()
                          AND p.expiration_date <= DATE_ADD(CURDATE(), INTERVAL ' . EXPIRY_WARNING_DAYS . ' DAY)';
            } elseif ($filters['expiry_status'] === 'green') {
                $sql .= ' AND (p.expiration_date IS NULL OR p.expiration_date > DATE_ADD(CURDATE(), INTERVAL ' . EXPIRY_WARNING_DAYS . ' DAY))';
            }
        }

        $sql .= ' ORDER BY p.name ASC';

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function allActive(): array
    {
        return $this->search([]);
    }

    public function create(array $data, ?int $userId): int
    {
        $stmt = $this->query(
            'INSERT INTO products
             (product_code, name, category_id, supplier_id, unit, quantity, minimum_stock_level,
              cost_price, selling_price, date_received, expiration_date, qr_code, status, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['product_code'],
                $data['name'],
                $data['category_id'] ?: null,
                $data['supplier_id'] ?: null,
                $data['unit'],
                $data['quantity'],
                $data['minimum_stock_level'],
                $data['cost_price'],
                $data['selling_price'],
                $data['date_received'] ?: null,
                $data['expiration_date'] ?: null,
                $data['qr_code'],
                'active',
                $userId,
            ]
        );
        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->query(
            'UPDATE products SET
                name = ?, category_id = ?, supplier_id = ?, unit = ?,
                minimum_stock_level = ?, cost_price = ?, selling_price = ?,
                date_received = ?, expiration_date = ?
             WHERE id = ?',
            [
                $data['name'],
                $data['category_id'] ?: null,
                $data['supplier_id'] ?: null,
                $data['unit'],
                $data['minimum_stock_level'],
                $data['cost_price'],
                $data['selling_price'],
                $data['date_received'] ?: null,
                $data['expiration_date'] ?: null,
                $id,
            ]
        );
        return $stmt->rowCount() >= 0;
    }

    public function archive(int $id): bool
    {
        $stmt = $this->query("UPDATE products SET status = 'archived' WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function productCodeExists(string $code): bool
    {
        $stmt = $this->query('SELECT id FROM products WHERE product_code = ?', [$code]);
        return (bool)$stmt->fetch();
    }

    public function generateProductCode(): string
    {
        $stmt = $this->query("SELECT MAX(id) AS max_id FROM products");
        $next = ((int)$stmt->fetch()['max_id']) + 1;
        return 'PB-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function generateQrCode(string $productCode): string
    {
        return 'QR-' . str_replace('-', '', $productCode) . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    public function adjustQuantity(int $id, float $delta): void
    {
        $this->query('UPDATE products SET quantity = quantity + ? WHERE id = ?', [$delta, $id]);
    }

    /** Classify expiry status: green (safe), yellow (expiring soon), red (expired), or null (no expiry date). */
    public static function expiryStatus(?string $expirationDate): ?string
    {
        if (!$expirationDate) {
            return null;
        }
        $today = new DateTime('today');
        $exp = new DateTime($expirationDate);
        $diff = (int)$today->diff($exp)->format('%r%a');

        if ($diff < 0) {
            return 'red';
        }
        if ($diff <= EXPIRY_WARNING_DAYS) {
            return 'yellow';
        }
        return 'green';
    }

    // ---- Dashboard aggregates ----------------------------------------------

    public function countTotalProducts(): int
    {
        return (int)$this->query("SELECT COUNT(*) c FROM products WHERE status = 'active'")->fetch()['c'];
    }

    public function sumTotalQuantity(): float
    {
        return (float)$this->query("SELECT COALESCE(SUM(quantity),0) s FROM products WHERE status = 'active'")->fetch()['s'];
    }

    public function countLowStock(): int
    {
        return (int)$this->query("SELECT COUNT(*) c FROM products WHERE status = 'active' AND quantity <= minimum_stock_level")->fetch()['c'];
    }

    public function countExpiringSoon(): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) c FROM products WHERE status = 'active' AND expiration_date IS NOT NULL
             AND expiration_date >= CURDATE() AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)",
            [EXPIRY_WARNING_DAYS]
        );
        return (int)$stmt->fetch()['c'];
    }

    public function countExpired(): int
    {
        return (int)$this->query("SELECT COUNT(*) c FROM products WHERE status = 'active' AND expiration_date IS NOT NULL AND expiration_date < CURDATE()")->fetch()['c'];
    }

    public function totalInventoryValue(): float
    {
        return (float)$this->query("SELECT COALESCE(SUM(quantity * cost_price),0) v FROM products WHERE status = 'active'")->fetch()['v'];
    }

    public function estimatedExpiredLoss(): float
    {
        $stmt = $this->query(
            "SELECT COALESCE(SUM(quantity * cost_price),0) v FROM products
             WHERE status = 'active' AND expiration_date IS NOT NULL AND expiration_date < CURDATE()"
        );
        return (float)$stmt->fetch()['v'];
    }

    public function recentlyAdded(int $limit = 5): array
    {
        $stmt = $this->query(self::BASE_SELECT . " WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT " . (int)$limit);
        return $stmt->fetchAll();
    }
}
