<?php
/**
 * ActivityLogger
 * Records an audit trail entry for security-relevant and business actions
 * (logins, product changes, stock movements, user management, etc).
 */
class ActivityLogger
{
    public static function log(string $action, string $description = ''): void
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                Auth::id(),
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $e) {
            // Logging must never break the request that triggered it.
            error_log('ActivityLogger failed: ' . $e->getMessage());
        }
    }
}
