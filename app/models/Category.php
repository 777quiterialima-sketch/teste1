<?php
class Category
{
    public static function all(int $userId): array
    {
        $stmt = db()->prepare('SELECT * FROM categories WHERE user_id = ? OR user_id IS NULL ORDER BY name');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $name, string $type): int
    {
        $stmt = db()->prepare('INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $name, $type]);
        return (int)db()->lastInsertId();
    }

    public static function update(int $userId, int $id, string $name, string $type): void
    {
        $stmt = db()->prepare('UPDATE categories SET name = ?, type = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$name, $type, $id, $userId]);
    }

    public static function delete(int $userId, int $id): void
    {
        $stmt = db()->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }
}
