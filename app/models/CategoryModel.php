<?php
require_once __DIR__ . '/../core/Model.php';

class CategoryModel extends Model
{
    protected string $table = 'categories';

    public function create(string $name, ?string $description): int
    {
        $stmt = $this->query('INSERT INTO categories (name, description) VALUES (?, ?)', [$name, $description]);
        return (int)$this->lastInsertId();
    }

    public function nameExists(string $name): bool
    {
        $stmt = $this->query('SELECT id FROM categories WHERE name = ?', [$name]);
        return (bool)$stmt->fetch();
    }
}
