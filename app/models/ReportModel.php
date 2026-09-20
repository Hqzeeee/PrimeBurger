<?php
require_once __DIR__ . '/../core/Model.php';

/**
 * ReportModel
 * Read-only aggregate queries backing the Reports module. Shared by
 * reports.php (on-screen) and export.php (CSV / Excel / print).
 */
class ReportModel extends Model
{
    protected string $table = 'products';

    public function inventoryReport(string $dateFrom = '', string $dateTo = '', string $keyword = ''): array
    {
        $sql = "SELECT p.product_code, p.name, c.name AS category_name, s.name AS supplier_name,
                       p.quantity, p.unit, p.cost_price, p.selling_price,
                       (p.quantity * p.cost_price) AS inventory_value,
                       p.expiration_date, p.date_received, p.minimum_stock_level
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN suppliers s ON s.id = p.supplier_id
                WHERE p.status = 'active'";
        $params = [];
        if ($dateFrom) { $sql .= ' AND p.date_received >= ?'; $params[] = $dateFrom; }
        if ($dateTo)   { $sql .= ' AND p.date_received <= ?'; $params[] = $dateTo; }
        if ($keyword)  { $sql .= ' AND (p.name LIKE ? OR p.product_code LIKE ?)'; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }
        $sql .= ' ORDER BY p.name ASC';
        return $this->query($sql, $params)->fetchAll();
    }

    public function stockMovementReport(string $dateFrom = '', string $dateTo = '', string $keyword = ''): array
    {
        $sql = "SELECT t.transaction_date, p.product_code, p.name, t.type, t.quantity, t.reason, u.full_name AS user_name
                FROM inventory_transactions t
                LEFT JOIN products p ON p.id = t.product_id
                LEFT JOIN users u ON u.id = t.user_id
                WHERE 1=1";
        $params = [];
        if ($dateFrom) { $sql .= ' AND t.transaction_date >= ?'; $params[] = $dateFrom; }
        if ($dateTo)   { $sql .= ' AND t.transaction_date <= ?'; $params[] = $dateTo; }
        if ($keyword)  { $sql .= ' AND (p.name LIKE ? OR p.product_code LIKE ?)'; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }
        $sql .= ' ORDER BY t.transaction_date DESC, t.id DESC';
        return $this->query($sql, $params)->fetchAll();
    }

    public function expirationReport(string $dateFrom = '', string $dateTo = '', string $keyword = ''): array
    {
        $sql = "SELECT p.product_code, p.name, c.name AS category_name, p.quantity, p.unit,
                       p.expiration_date, p.cost_price, (p.quantity * p.cost_price) AS at_risk_value
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.status = 'active' AND p.expiration_date IS NOT NULL
                  AND p.expiration_date <= DATE_ADD(CURDATE(), INTERVAL " . EXPIRY_WARNING_DAYS . " DAY)";
        $params = [];
        if ($dateFrom) { $sql .= ' AND p.expiration_date >= ?'; $params[] = $dateFrom; }
        if ($dateTo)   { $sql .= ' AND p.expiration_date <= ?'; $params[] = $dateTo; }
        if ($keyword)  { $sql .= ' AND (p.name LIKE ? OR p.product_code LIKE ?)'; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }
        $sql .= ' ORDER BY p.expiration_date ASC';
        return $this->query($sql, $params)->fetchAll();
    }

    public function wasteLossReport(string $dateFrom = '', string $dateTo = '', string $keyword = ''): array
    {
        $sql = "SELECT p.product_code, p.name, c.name AS category_name, p.quantity, p.unit,
                       p.expiration_date, p.cost_price, (p.quantity * p.cost_price) AS loss_value
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.status = 'active' AND p.expiration_date IS NOT NULL AND p.expiration_date < CURDATE()";
        $params = [];
        if ($dateFrom) { $sql .= ' AND p.expiration_date >= ?'; $params[] = $dateFrom; }
        if ($dateTo)   { $sql .= ' AND p.expiration_date <= ?'; $params[] = $dateTo; }
        if ($keyword)  { $sql .= ' AND (p.name LIKE ? OR p.product_code LIKE ?)'; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }
        $sql .= ' ORDER BY p.expiration_date ASC';
        return $this->query($sql, $params)->fetchAll();
    }
}
