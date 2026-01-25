<?php

class Category
{
    public static function allByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM categories WHERE user_id = :user_id OR is_default = 1 ORDER BY name');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO categories (user_id, name, type) VALUES (:user_id, :name, :type)');
        $stmt->execute($data);
        return (int)Database::connection()->lastInsertId();
    }
}
