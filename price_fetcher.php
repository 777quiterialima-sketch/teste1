<?php
require_once __DIR__ . '/db.php';

function fetchPriceForProduct(array $product, PDO $pdo): array
{
    $download = downloadProductPage($product['url']);

    if (!($download['success'] ?? false)) {
        return $download;
    }

    $html = $download['html'];

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

    $encoding = mb_detect_encoding($html, ['UTF-8', 'ISO-8859-1', 'WINDOWS-1252'], true) ?: 'UTF-8';
    $normalizedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', $encoding);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $previousLibxmlSetting = libxml_use_internal_errors(true);
    $dom->loadHTML($normalizedHtml);
    libxml_clear_errors();
    libxml_use_internal_errors($previousLibxmlSetting);

    $xpath = new DOMXPath($dom);
    $nodeList = $xpath->query('//*[@id="product-price"]');
    $node = $nodeList !== false ? $nodeList->item(0) : null;

    if ($node) {
        $rawPrice = trim(preg_replace('/\s+/u', ' ', $node->textContent));

        if ($rawPrice === '' && $node->hasAttribute('data-price')) {
            $rawPrice = trim($node->getAttribute('data-price'));
        }

        if ($rawPrice === '' && $node->hasAttribute('content')) {
            $rawPrice = trim($node->getAttribute('content'));
        }

        if ($rawPrice !== '') {
            return [
                'success' => true,
                'rawPrice' => $rawPrice,
            ];
        }
    }

    $metaCandidates = [
        "//meta[@property='og:price:amount']",
        "//meta[@itemprop='price']",
        "//meta[@name='twitter:data1']",
    ];

    foreach ($metaCandidates as $metaQuery) {
        $metaList = $xpath->query($metaQuery);
        if ($metaList !== false) {
            foreach ($metaList as $metaNode) {
                $content = trim($metaNode->getAttribute('content'));
                if ($content === '' && $metaNode->hasAttribute('value')) {
                    $content = trim($metaNode->getAttribute('value'));
                }

                if ($content !== '') {
                    return [
                        'success' => true,
                        'rawPrice' => $content,
                    ];
                }
            }
        }
    }

    $regexCandidates = [
        '/id="product-price"[^>]*data-price="([^"]+)"/i',
        "/id='product-price'[^>]*data-price='([^']+)'/i",
        '/id="product-price"[^>]*content="([^"]+)"/i',
        "/id='product-price'[^>]*content='([^']+)'/i",
        '/id="product-price"[^>]*>([^<]+)/i',
        "/id='product-price'[^>]*>([^<]+)/i",
    ];

    foreach ($regexCandidates as $pattern) {
        if (preg_match($pattern, $html, $matches) && trim($matches[1]) !== '') {
            return [
                'success' => true,
                'rawPrice' => trim($matches[1]),
            ];
        }
    }

    $jsonLdPrice = extractPriceFromJsonLd($xpath);
    if ($jsonLdPrice !== null) {
        return [
            'success' => true,
            'rawPrice' => $jsonLdPrice,
        ];
    }

    $statePrice = extractPriceFromEmbeddedState($html);
    if ($statePrice !== null) {
        return [
            'success' => true,
            'rawPrice' => $statePrice,
        ];
    }

    return [
        'success' => false,
        'message' => 'Não foi possível localizar o preço na página da Casas Bahia.'
    ];
}

function extractPriceFromJsonLd(DOMXPath $xpath): ?string
{
    $scripts = $xpath->query('//script[@type="application/ld+json"]');
    if ($scripts === false) {
        return null;
    }

    foreach ($scripts as $script) {
        $json = trim($script->textContent);
        if ($json === '') {
            continue;
        }

        $decoded = json_decode($json, true);
        if ($decoded === null) {
            $decoded = json_decode(preg_replace('/,[\s\r\n]*}/', '}', $json), true);
        }

        if ($decoded === null) {
            continue;
        }

        $price = findPriceInMixedData($decoded);
        if ($price !== null) {
            return $price;
        }
    }

    return null;
}

function extractPriceFromEmbeddedState(string $html): ?string
{
    $patterns = [
        '/"price"\s*:\s*"?([0-9]+[.,][0-9]{2})"?/i',
        '/"sellingPrice"\s*:\s*"?([0-9]+[.,][0-9]{2})"?/i',
        '/"salesPrice"\s*:\s*"?([0-9]+[.,][0-9]{2})"?/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            return $matches[1];
        }
    }

    return null;
}

function findPriceInMixedData($data): ?string
{
    if (is_array($data)) {
        if (isset($data['price']) && $data['price'] !== '') {
            return (string) $data['price'];
        }

        if (isset($data['offers'])) {
            $offers = $data['offers'];
            if (is_array($offers) && isset($offers['price']) && $offers['price'] !== '') {
                return (string) $offers['price'];
            }

            if (is_array($offers)) {
                foreach ($offers as $offer) {
                    $price = findPriceInMixedData($offer);
                    if ($price !== null) {
                        return $price;
                    }
                }
            }
        }

        foreach ($data as $value) {
            $price = findPriceInMixedData($value);
            if ($price !== null) {
                return $price;
            }
        }
    }

    if (is_object($data)) {
        return findPriceInMixedData((array) $data);
    }

    return null;
}

function downloadProductPage(string $url): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118 Safari/537.36',
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);

        $html = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($html === false || $statusCode >= 400) {
            return [
                'success' => false,
                'message' => 'Não foi possível acessar a página do produto.' . ($error ? ' Detalhes: ' . $error : ''),
            ];
        }

        return [
            'success' => true,
            'html' => $html,
        ];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ],
    ]);

    $html = @file_get_contents($url, false, $context);

    if ($html === false) {
        return [
            'success' => false,
            'message' => 'Não foi possível acessar a página do produto.'
        ];
    }

    return [
        'success' => true,
        'html' => $html,
    ];
}
