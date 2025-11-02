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

    $extraction = extractPriceFromHtml($product, $html);
    if (!($extraction['success'] ?? false)) {
        return $extraction;
    }

    $rawPrice = $extraction['rawPrice'];
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

function extractPriceFromHtml(array $product, string $html): array
{
    $store = $product['store'] ?? '';

    if ($store === 'casasbahia') {
        return extractCasasBahiaPrice($html);
    }

    $pattern = $product['price_pattern'] ?? '';

    if ($pattern === '') {
        return [
            'success' => false,
            'message' => 'Nenhuma regra de captura de preço configurada para este produto.'
        ];
    }

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

    return [
        'success' => true,
        'rawPrice' => $matches[1] ?? $matches[0],
    ];
}

function extractCasasBahiaPrice(string $html): array
{
    if (!class_exists('DOMDocument')) {
        return [
            'success' => false,
            'message' => 'Extensão DOM não disponível para processar a página da Casas Bahia.'
        ];
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $previousLibxmlSetting = libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    libxml_use_internal_errors($previousLibxmlSetting);

    $xpath = new DOMXPath($dom);
    $nodeList = $xpath->query('//*[@id="product-price"]');
    $node = $nodeList !== false ? $nodeList->item(0) : null;

    if (!$node) {
        return [
            'success' => false,
            'message' => 'Não foi possível localizar o preço na página da Casas Bahia.'
        ];
    }

    $rawPrice = trim($node->textContent);

    if ($rawPrice === '') {
        return [
            'success' => false,
            'message' => 'Preço não encontrado no elemento com id "product-price".'
        ];
    }

    return [
        'success' => true,
        'rawPrice' => $rawPrice,
    ];
}
