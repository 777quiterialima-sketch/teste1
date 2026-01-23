<?php
class Account
{
    public static function all(int $userId): array
    {
        $stmt = db()->prepare('SELECT * FROM accounts WHERE user_id = ? ORDER BY name');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function find(int $userId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM accounts WHERE user_id = ? AND id = ?');
        $stmt->execute([$userId, $id]);
        $account = $stmt->fetch();
        return $account ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $stmt = db()->prepare('INSERT INTO accounts (user_id, name, type, initial_balance, color, icon, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $userId,
            $data['name'],
            $data['type'],
            $data['initial_balance'],
            $data['color'],
            $data['icon'],
        ]);
        return (int)db()->lastInsertId();
    }

    public static function update(int $userId, int $id, array $data): void
    {
        $stmt = db()->prepare('UPDATE accounts SET name = ?, type = ?, initial_balance = ?, color = ?, icon = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([
            $data['name'],
            $data['type'],
            $data['initial_balance'],
            $data['color'],
            $data['icon'],
            $id,
            $userId,
        ]);
    }

    public static function delete(int $userId, int $id): void
    {
        $stmt = db()->prepare('DELETE FROM accounts WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public static function balance(int $userId, int $accountId): float
    {
        $stmt = db()->prepare(
            "SELECT
                (SELECT initial_balance FROM accounts WHERE id = ? AND user_id = ?) +
                (SELECT COALESCE(SUM(CASE
                    WHEN type = 'receita' THEN amount
                    WHEN type = 'despesa' THEN -amount
                    WHEN type = 'transferencia' AND account_id = ? THEN -amount
                    WHEN type = 'transferencia' AND account_dest_id = ? THEN amount
                    ELSE 0 END), 0)
                FROM transactions
                WHERE user_id = ? AND (account_id = ? OR account_dest_id = ?)) AS balance"
        );
        $stmt->execute([$accountId, $userId, $accountId, $accountId, $userId, $accountId, $accountId]);
        $row = $stmt->fetch();
        return $row ? (float)$row['balance'] : 0.0;
    }

    public static function totalBalance(int $userId): float
    {
        $accounts = self::all($userId);
        $total = 0.0;
        foreach ($accounts as $account) {
            $total += self::balance($userId, (int)$account['id']);
        }
        return $total;
    }
}
