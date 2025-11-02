<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$url = trim($_POST['url'] ?? '');
$store = trim($_POST['store'] ?? '');
$pattern = trim($_POST['price_pattern'] ?? '');

$availableStores = [
    'casasbahia' => 'Casas Bahia',
];

$errors = [];

if ($name === '') {
    $errors[] = 'Informe um nome para o produto.';
}

if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
    $errors[] = 'Informe uma URL válida para o produto.';
}

if (!array_key_exists($store, $availableStores)) {
    $errors[] = 'Selecione uma loja válida.';
}

if ($store !== 'casasbahia') {
    if ($pattern === '') {
        $errors[] = 'Informe uma expressão regular para localizar o preço.';
    }

    if ($pattern !== '' && @preg_match($pattern, '') === false) {
        $errors[] = 'A expressão regular informada não é válida.';
    }
} else {
    $pattern = '';
}

if (!empty($errors)) {
    session_start();
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_values'] = ['name' => $name, 'url' => $url, 'price_pattern' => $pattern, 'store' => $store];
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('INSERT INTO products (name, url, price_pattern, store) VALUES (:name, :url, :pattern, :store)');
$stmt->execute([
    ':name' => $name,
    ':url' => $url,
    ':pattern' => $pattern,
    ':store' => $store,
]);

header('Location: index.php');
exit;
