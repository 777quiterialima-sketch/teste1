<?php

class BankConnection
{
    public static function allByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT bc.*, a.name as account_name FROM bank_connections bc LEFT JOIN accounts a ON a.id = bc.account_id WHERE bc.user_id = :user_id ORDER BY bc.created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO bank_connections (user_id, account_id, bank_name, type, status) VALUES (:user_id, :account_id, :bank_name, :type, :status)'
        );
        $stmt->execute($data);
        return (int)Database::connection()->lastInsertId();
    }
}
