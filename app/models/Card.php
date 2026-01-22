<?php
class Card
{
    public static function all(int $userId): array
    {
        $stmt = db()->prepare('SELECT * FROM cards WHERE user_id = ? ORDER BY name');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function find(int $userId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM cards WHERE user_id = ? AND id = ?');
        $stmt->execute([$userId, $id]);
        $card = $stmt->fetch();
        return $card ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $stmt = db()->prepare('INSERT INTO cards (user_id, name, brand, limit_total, closing_day, due_day, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $userId,
            $data['name'],
            $data['brand'],
            $data['limit_total'],
            $data['closing_day'],
            $data['due_day'],
        ]);
        return (int)db()->lastInsertId();
    }

    public static function update(int $userId, int $id, array $data): void
    {
        $stmt = db()->prepare('UPDATE cards SET name = ?, brand = ?, limit_total = ?, closing_day = ?, due_day = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([
            $data['name'],
            $data['brand'],
            $data['limit_total'],
            $data['closing_day'],
            $data['due_day'],
            $id,
            $userId,
        ]);
    }

    public static function delete(int $userId, int $id): void
    {
        $stmt = db()->prepare('DELETE FROM cards WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public static function invoiceTotal(int $userId, int $cardId, string $month): float
    {
        $stmt = db()->prepare('SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE user_id = ? AND card_id = ? AND type = "despesa" AND DATE_FORMAT(date, "%Y-%m") = ?');
        $stmt->execute([$userId, $cardId, $month]);
        $row = $stmt->fetch();
        return $row ? (float)$row['total'] : 0.0;
    }
}
