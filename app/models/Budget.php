<?php

class Budget
{
    public static function listByUser(int $userId, string $month): array
    {
        $stmt = Database::connection()->prepare('SELECT b.*, c.name as category_name FROM budgets b LEFT JOIN categories c ON c.id = b.category_id WHERE b.user_id = :user_id AND b.month = :month');
        $stmt->execute(['user_id' => $userId, 'month' => $month]);
        return $stmt->fetchAll();
    }

    public static function upsert(int $userId, ?int $categoryId, string $month, float $limit): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO budgets (user_id, category_id, month, limit_amount)
             VALUES (:user_id, :category_id, :month, :limit_amount)
             ON DUPLICATE KEY UPDATE limit_amount = :limit_amount'
        );
        $stmt->execute([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'month' => $month,
            'limit_amount' => $limit,
        ]);
    }
}
