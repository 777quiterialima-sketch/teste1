<?php
$databasePath = __DIR__ . '/data/database.sqlite';
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        url TEXT NOT NULL,
        price_pattern TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS price_history (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        price REAL NOT NULL,
        currency TEXT DEFAULT "R$",
        fetched_at TEXT NOT NULL,
        FOREIGN KEY(product_id) REFERENCES products(id)
    )');
} catch (PDOException $e) {
    die('Erro ao iniciar o banco de dados: ' . htmlspecialchars($e->getMessage()));
}
