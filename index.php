<?php
session_start();
require_once __DIR__ . '/db.php';

$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType = $_SESSION['flash_type'] ?? 'info';
$formErrors = $_SESSION['form_errors'] ?? [];
$formValues = $_SESSION['form_values'] ?? [];
unset($_SESSION['flash_message'], $_SESSION['flash_type'], $_SESSION['form_errors'], $_SESSION['form_values']);

$products = $pdo->query('SELECT * FROM products ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

function getLatestPrices(PDO $pdo, int $productId): array
{
    $stmt = $pdo->prepare('SELECT price, currency, fetched_at FROM price_history WHERE product_id = :product_id ORDER BY fetched_at DESC LIMIT 2');
    $stmt->execute([':product_id' => $productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor de preços</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        header { background: #2b6cb0; color: #fff; padding: 1.5rem; }
        main { padding: 1.5rem; max-width: 1000px; margin: 0 auto; }
        h1 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; background: #fff; }
        th, td { padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #edf2f7; }
        tr:hover td { background: #f7fafc; }
        .actions { display: flex; gap: 0.5rem; }
        .btn { display: inline-block; padding: 0.5rem 0.75rem; border-radius: 4px; text-decoration: none; color: #fff; background: #2b6cb0; font-size: 0.9rem; }
        .btn-secondary { background: #4a5568; }
        form { margin-top: 2rem; background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        label { display: block; margin-bottom: 0.25rem; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 0.5rem; border: 1px solid #cbd5e0; border-radius: 4px; margin-bottom: 1rem; font-size: 1rem; }
        input[type="submit"] { background: #2b6cb0; color: #fff; border: none; padding: 0.75rem 1.25rem; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        input[type="submit"]:hover { background: #2c5282; }
        .empty-state { background: #fff; padding: 1.5rem; border-radius: 8px; margin-top: 1.5rem; }
        .status-up { color: #2f855a; font-weight: bold; }
        .status-down { color: #c53030; font-weight: bold; }
        .status-flat { color: #718096; font-weight: bold; }
        .small { font-size: 0.85rem; color: #4a5568; }
        .alert { border: 1px solid #fed7d7; background: #fff5f5; padding: 1rem; border-radius: 6px; color: #c53030; margin-bottom: 1.5rem; }
        ul { margin: 0 0 1rem 1.5rem; }
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { display: none; }
            td { padding-left: 50%; position: relative; }
            td::before { position: absolute; top: 0; left: 1rem; width: 45%; padding-right: 1rem; font-weight: bold; color: #4a5568; }
            td:nth-of-type(1)::before { content: 'Produto'; }
            td:nth-of-type(2)::before { content: 'Preço atual'; }
            td:nth-of-type(3)::before { content: 'Variação'; }
            td:nth-of-type(4)::before { content: 'Última atualização'; }
            td:nth-of-type(5)::before { content: 'Ações'; }
        }
    </style>
</head>
<body>
<header>
    <h1>Monitor de preços</h1>
    <p>Cadastre produtos, busque os preços automaticamente e acompanhe a evolução com facilidade.</p>
</header>
<main>
    <?php if ($flashMessage): ?>
        <div class="alert" style="border-color: <?php echo $flashType === 'success' ? '#9ae6b4' : '#fed7d7'; ?>; background: <?php echo $flashType === 'success' ? '#f0fff4' : '#fff5f5'; ?>; color: <?php echo $flashType === 'success' ? '#2f855a' : '#c53030'; ?>;">
            <?php echo htmlspecialchars($flashMessage); ?>
        </div>
    <?php endif; ?>
    <?php if (count($products) === 0): ?>
        <div class="empty-state">
            <h2>Nenhum produto cadastrado ainda</h2>
            <p>Utilize o formulário abaixo para adicionar o primeiro produto e acompanhe o histórico automaticamente.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço atual</th>
                    <th>Variação</th>
                    <th>Última atualização</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php $prices = getLatestPrices($pdo, (int) $product['id']); ?>
                    <?php
                        $current = $prices[0] ?? null;
                        $previous = $prices[1] ?? null;
                        $statusClass = 'status-flat';
                        $statusText = 'Sem variação';
                        if ($current && $previous) {
                            if ($current['price'] > $previous['price']) {
                                $statusClass = 'status-up';
                                $statusText = 'Subiu ' . number_format($current['price'] - $previous['price'], 2, ',', '.');
                            } elseif ($current['price'] < $previous['price']) {
                                $statusClass = 'status-down';
                                $statusText = 'Caiu ' . number_format($previous['price'] - $current['price'], 2, ',', '.');
                            }
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                            <a class="small" href="<?php echo htmlspecialchars($product['url']); ?>" target="_blank" rel="noopener">Abrir página</a>
                        </td>
                        <td>
                            <?php if ($current): ?>
                                <strong><?php echo htmlspecialchars($current['currency']); ?> <?php echo number_format($current['price'], 2, ',', '.'); ?></strong>
                            <?php else: ?>
                                <span class="small">Sem dados</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </td>
                        <td>
                            <?php if ($current): ?>
                                <span class="small">Atualizado em <?php echo (new DateTime($current['fetched_at']))->format('d/m/Y H:i'); ?></span>
                            <?php else: ?>
                                <span class="small">Aguardando primeira atualização</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a class="btn" href="fetch_price.php?product_id=<?php echo (int) $product['id']; ?>">Atualizar agora</a>
                                <a class="btn-secondary btn" href="view_history.php?product_id=<?php echo (int) $product['id']; ?>">Ver histórico</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <form action="save_product.php" method="post">
        <h2>Adicionar novo produto</h2>
        <?php if (!empty($formErrors)): ?>
            <div class="alert">
                <strong>Não foi possível salvar:</strong>
                <ul>
                    <?php foreach ($formErrors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <label for="name">Nome do produto</label>
        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($formValues['name'] ?? ''); ?>">

        <label for="url">URL da página do produto</label>
        <input type="text" id="url" name="url" required placeholder="https://exemplo.com/produto" value="<?php echo htmlspecialchars($formValues['url'] ?? ''); ?>">

        <label for="pattern">Expressão regular para localizar o preço <span class="small">(use um grupo de captura se precisar limpar o valor)</span></label>
        <textarea id="pattern" name="price_pattern" rows="3" required placeholder="/R\\$\s*([0-9.,]+)/"><?php echo htmlspecialchars($formValues['price_pattern'] ?? ''); ?></textarea>

        <input type="submit" value="Cadastrar produto">
    </form>
</main>
</body>
</html>
