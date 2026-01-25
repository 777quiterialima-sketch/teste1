<?php

class User
{
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $stmt->execute(['name' => $name, 'email' => $email, 'id' => $id]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(['password' => $password, 'id' => $id]);
    }
}
