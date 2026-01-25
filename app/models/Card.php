<?php

class Card
{
    public static function allByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM cards WHERE user_id = :user_id ORDER BY name');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM cards WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $card = $stmt->fetch();
        return $card ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO cards (user_id, name, brand, limit_total, close_day, due_day) VALUES (:user_id, :name, :brand, :limit_total, :close_day, :due_day)'
        );
        $stmt->execute($data);
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, int $userId, array $data): void
    {
        $data['id'] = $id;
        $data['user_id'] = $userId;
        $stmt = Database::connection()->prepare(
            'UPDATE cards SET name = :name, brand = :brand, limit_total = :limit_total, close_day = :close_day, due_day = :due_day WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute($data);
    }

    public static function delete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM cards WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public static function currentInvoiceAmount(int $cardId, string $month): float
    {
        $stmt = Database::connection()->prepare(
            'SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE card_id = :card_id AND invoice_month = :invoice_month'
        );
        $stmt->execute(['card_id' => $cardId, 'invoice_month' => $month]);
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0);
    }
}
