<?php
class BankConnection
{
    public static function all(int $userId): array
    {
        $stmt = db()->prepare('SELECT bc.*, a.name AS account_name FROM bank_connections bc LEFT JOIN accounts a ON a.id = bc.account_id WHERE bc.user_id = ? ORDER BY bc.created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, int $accountId, string $name, string $type): void
    {
        $stmt = db()->prepare('INSERT INTO bank_connections (user_id, account_id, name, type, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $accountId, $name, $type]);
    }

    public static function delete(int $userId, int $id): void
    {
        $stmt = db()->prepare('DELETE FROM bank_connections WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }
}
