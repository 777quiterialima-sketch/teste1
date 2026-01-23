<?php
class ImportModel
{
    public static function create(int $userId, string $fileName, string $type, string $status): int
    {
        $stmt = db()->prepare('INSERT INTO imports (user_id, file_name, type, status, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $fileName, $type, $status]);
        return (int)db()->lastInsertId();
    }
}
