<?php

class Import
{
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO imports (user_id, type, filename, imported_at) VALUES (:user_id, :type, :filename, NOW())');
        $stmt->execute($data);
        return (int)Database::connection()->lastInsertId();
    }
}
