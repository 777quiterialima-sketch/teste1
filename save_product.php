<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$url = trim($_POST['url'] ?? '');
$pattern = trim($_POST['price_pattern'] ?? '');

$errors = [];

if ($name === '') {
    $errors[] = 'Informe um nome para o produto.';
}

if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
    $errors[] = 'Informe uma URL válida para o produto.';
}

if ($pattern === '') {
    $errors[] = 'Informe uma expressão regular para localizar o preço.';
}

if (@preg_match($pattern, '') === false) {
    $errors[] = 'A expressão regular informada não é válida.';
}

if (!empty($errors)) {
    session_start();
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_values'] = ['name' => $name, 'url' => $url, 'price_pattern' => $pattern];
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('INSERT INTO products (name, url, price_pattern) VALUES (:name, :url, :pattern)');
$stmt->execute([
    ':name' => $name,
    ':url' => $url,
    ':pattern' => $pattern,
]);

header('Location: index.php');
exit;
