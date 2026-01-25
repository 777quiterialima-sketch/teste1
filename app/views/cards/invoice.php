<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Fatura: <?= e($card['name']) ?></h1>
    <div>
        <form method="get" class="inline">
            <input type="month" name="month" value="<?= e($month) ?>">
            <button class="btn-outline" type="submit">Ir</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h3>Total do mês</h3>
        <span class="badge">R$ <?= number_format($total, 2, ',', '.') ?></span>
    </div>
    <p>Status: <strong><?= e($invoice['status']) ?></strong></p>
    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['date']) ?></td>
                    <td><?= e($item['description']) ?></td>
                    <td>R$ <?= number_format($item['amount'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($invoice['status'] !== 'paid'): ?>
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="pay">
            <label>Conta para pagamento
                <select name="account_id" required>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= e($account['id']) ?>"><?= e($account['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn-primary" type="submit">Pagar fatura</button>
        </form>
    <?php endif; ?>
</div>
<?php require base_path('app/views/layouts/footer.php'); ?>
