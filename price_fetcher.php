<?php
require_once __DIR__ . '/db.php';

function fetchPriceForProduct(array $product, PDO $pdo): array
{
    $url = $product['url'];
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118 Safari/537.36'
            ]
        ]
    ]);

    $html = @file_get_contents($url, false, $context);

    if ($html === false) {
        return [
            'success' => false,
            'message' => 'Não foi possível acessar a página do produto.'
        ];
    }

    $pattern = $product['price_pattern'];
    if (@preg_match($pattern, '') === false) {
        return [
            'success' => false,
            'message' => 'Expressão regular de preço inválida.'
        ];
    }

    if (!preg_match($pattern, $html, $matches)) {
        return [
            'success' => false,
            'message' => 'Não foi possível localizar o preço com a expressão informada.'
        ];
    }

    $rawPrice = $matches[1] ?? $matches[0];
    $normalized = str_replace(["\xC2\xA0", ' '], '', $rawPrice);
    $normalized = str_replace(',', '.', $normalized);
    $normalized = preg_replace('/[^0-9.]/', '', $normalized);

    if (substr_count($normalized, '.') > 1) {
        $parts = explode('.', $normalized);
        $decimal = array_pop($parts);
        $normalized = implode('', $parts) . '.' . $decimal;
    }

    if ($normalized === '' || !is_numeric($normalized)) {
        return [
            'success' => false,
            'message' => 'Não foi possível converter o preço encontrado.'
        ];
    }

    $price = (float) $normalized;
    $currency = 'R$';
    if (preg_match('/(R\$|US\$|€|£)/', $rawPrice, $currencyMatch)) {
        $currency = trim($currencyMatch[1]);
    }

    $statement = $pdo->prepare('INSERT INTO price_history (product_id, price, currency, fetched_at) VALUES (:product_id, :price, :currency, :fetched_at)');
    $statement->execute([
        ':product_id' => $product['id'],
        ':price' => $price,
        ':currency' => $currency,
        ':fetched_at' => (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s')
    ]);

    return [
        'success' => true,
        'message' => 'Preço atualizado com sucesso.',
        'price' => $price,
        'currency' => $currency
    ];
}
