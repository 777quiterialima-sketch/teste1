<?php
require_once __DIR__ . '/price_fetcher.php';

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
if ($productId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    session_start();
    $_SESSION['flash_message'] = 'Produto não encontrado.';
    header('Location: index.php');
    exit;
}

$result = fetchPriceForProduct($product, $pdo);

session_start();
$_SESSION['flash_message'] = $result['message'] ?? 'Atualização finalizada.';
$_SESSION['flash_type'] = ($result['success'] ?? false) ? 'success' : 'error';

header('Location: index.php');
exit;
