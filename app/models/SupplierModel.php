<?php
require_once __DIR__ . '/../core/Model.php';

class SupplierModel extends Model
{
    protected string $table = 'suppliers';

    public function create(array $data): int
    {
        $stmt = $this->query(
            'INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?,?,?,?,?)',
            [$data['name'], $data['contact_person'] ?: null, $data['phone'] ?: null, $data['email'] ?: null, $data['address'] ?: null]
        );
        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->query(
            'UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=? WHERE id=?',
            [$data['name'], $data['contact_person'] ?: null, $data['phone'] ?: null, $data['email'] ?: null, $data['address'] ?: null, $id]
        );
        return $stmt->rowCount() >= 0;
    }
}
