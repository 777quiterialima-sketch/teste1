<?php
class Budget
{
    public static function all(int $userId, string $month): array
    {
        $stmt = db()->prepare('SELECT b.*, c.name AS category_name FROM budgets b LEFT JOIN categories c ON c.id = b.category_id WHERE b.user_id = ? AND b.month = ?');
        $stmt->execute([$userId, $month]);
        return $stmt->fetchAll();
    }

    public static function upsert(int $userId, ?int $categoryId, string $month, float $limit): void
    {
        $stmt = db()->prepare('SELECT id FROM budgets WHERE user_id = ? AND category_id <=> ? AND month = ?');
        $stmt->execute([$userId, $categoryId, $month]);
        $existing = $stmt->fetch();
        if ($existing) {
            $update = db()->prepare('UPDATE budgets SET limit_amount = ? WHERE id = ?');
            $update->execute([$limit, $existing['id']]);
            return;
        }
        $insert = db()->prepare('INSERT INTO budgets (user_id, category_id, month, limit_amount) VALUES (?, ?, ?, ?)');
        $insert->execute([$userId, $categoryId, $month, $limit]);
    }
}
