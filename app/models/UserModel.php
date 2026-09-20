<?php
require_once __DIR__ . '/../core/Model.php';

class UserModel extends Model
{
    protected string $table = 'users';

    public function findByUsernameOrEmail(string $usernameOrEmail): array|false
    {
        $stmt = $this->query(
            'SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1',
            [$usernameOrEmail, $usernameOrEmail]
        );
        return $stmt->fetch();
    }

    public function usernameOrEmailExists(string $username, string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE (username = ? OR email = ?)';
        $params = [$username, $email];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->query($sql, $params);
        return (bool)$stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->query(
            'INSERT INTO users (username, email, password_hash, full_name, role, status)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['full_name'],
                $data['role'],
                $data['status'] ?? 'active',
            ]
        );
        return (int)$this->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->query(
            'UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, status = ? WHERE id = ?',
            [$data['username'], $data['email'], $data['full_name'], $data['role'], $data['status'], $id]
        );
        return $stmt->rowCount() >= 0;
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $this->query('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $id]);
    }

    public function changePassword(int $id, string $newPassword): void
    {
        $this->updatePasswordHash($id, password_hash($newPassword, PASSWORD_DEFAULT));
    }

    public function touchLastLogin(int $id): void
    {
        $this->query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }

    public function verifyCurrentPassword(int $id, string $password): bool
    {
        $user = $this->find($id);
        return $user && password_verify($password, $user['password_hash']);
    }
}
