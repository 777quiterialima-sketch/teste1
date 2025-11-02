<?php
require_once __DIR__ . '/db.php';

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
if ($productId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit;
}

$historyStmt = $pdo->prepare('SELECT price, currency, fetched_at FROM price_history WHERE product_id = :product_id ORDER BY fetched_at DESC');
$historyStmt->execute([':product_id' => $productId]);
$history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de preços - <?php echo htmlspecialchars($product['name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        header { background: #2b6cb0; color: #fff; padding: 1.5rem; }
        main { padding: 1.5rem; max-width: 900px; margin: 0 auto; }
        a { color: #2b6cb0; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; background: #fff; }
        th, td { padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #edf2f7; }
        tr:hover td { background: #f7fafc; }
        .chart { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-top: 1.5rem; }
        canvas { width: 100%; height: 300px; }
        .back-link { display: inline-block; margin-top: 1rem; }
    </style>
</head>
<body>
<header>
    <h1>Histórico de preços</h1>
    <p><?php echo htmlspecialchars($product['name']); ?></p>
</header>
<main>
    <a class="back-link" href="index.php">&larr; Voltar</a>

    <?php if (empty($history)): ?>
        <div class="chart">
            <p>Sem dados ainda. Clique em "Atualizar agora" na página inicial para gerar o primeiro registro.</p>
        </div>
    <?php else: ?>
        <div class="chart">
            <canvas id="priceChart"></canvas>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Preço</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $entry): ?>
                    <tr>
                        <td><?php echo (new DateTime($entry['fetched_at']))->format('d/m/Y H:i'); ?></td>
                        <td><?php echo htmlspecialchars($entry['currency']); ?> <?php echo number_format($entry['price'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php if (!empty($history)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('priceChart').getContext('2d');
    const data = {
        labels: <?php echo json_encode(array_reverse(array_map(fn($entry) => (new DateTime($entry['fetched_at']))->format('d/m H:i'), $history))); ?>,
        datasets: [{
            label: 'Preço',
            data: <?php echo json_encode(array_reverse(array_map(fn($entry) => $entry['price'], $history))); ?>,
            fill: false,
            borderColor: '#2b6cb0',
            backgroundColor: '#2b6cb0',
            tension: 0.1
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data,
        options: {
            responsive: true,
            scales: {
                y: {
                    ticks: {
                        callback(value) {
                            return '<?php echo addslashes($history[0]['currency']); ?> ' + Number(value).toFixed(2).replace('.', ',');
                        }
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>
</body>
</html>
