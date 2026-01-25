<?php

class Account
{
    public static function allByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM accounts WHERE user_id = :user_id ORDER BY name');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM accounts WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $account = $stmt->fetch();
        return $account ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO accounts (user_id, name, type, initial_balance, color) VALUES (:user_id, :name, :type, :initial_balance, :color)'
        );
        $stmt->execute($data);
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, int $userId, array $data): void
    {
        $data['id'] = $id;
        $data['user_id'] = $userId;
        $stmt = Database::connection()->prepare(
            'UPDATE accounts SET name = :name, type = :type, initial_balance = :initial_balance, color = :color WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute($data);
    }

    public static function delete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM accounts WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public static function balance(int $accountId): float
    {
        $sql = "SELECT
                COALESCE((SELECT initial_balance FROM accounts WHERE id = :account_id), 0)
                + COALESCE(SUM(CASE
                    WHEN type = 'income' AND account_id = :account_id THEN amount
                    WHEN type = 'expense' AND account_id = :account_id THEN -amount
                    WHEN type = 'transfer' AND account_id = :account_id THEN -amount
                    WHEN type = 'transfer' AND account_dest_id = :account_id THEN amount
                    ELSE 0
                END),0) AS balance
            FROM transactions
            WHERE account_id = :account_id OR account_dest_id = :account_id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['account_id' => $accountId]);
        $row = $stmt->fetch();
        return (float)($row['balance'] ?? 0);
    }

    public static function totalBalanceByUser(int $userId): float
    {
        $accounts = self::allByUser($userId);
        $total = 0;
        foreach ($accounts as $account) {
            $total += self::balance((int)$account['id']);
        }
        return $total;
    }
}
