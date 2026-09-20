<?php
/**
 * Base Model
 * Every model extends this for a shared PDO handle and small query helpers.
 * All queries use prepared statements with bound parameters.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function find(int $id): array|false
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1", [$id]);
        return $stmt->fetch();
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        $stmt = $this->query("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}
