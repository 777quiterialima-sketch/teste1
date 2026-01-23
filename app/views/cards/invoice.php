<div class="page-header">
    <h2>Fatura: <?= e($card['name']) ?> (<?= e($month) ?>)</h2>
    <a class="btn-outline" href="<?= base_url('cards') ?>">Voltar</a>
</div>

<section class="card">
    <h3>Total da fatura: R$ <?= number_format($total, 2, ',', '.') ?></h3>
    <p>Status: <strong><?= e($invoice['status']) ?></strong></p>
    <?php if ($invoice['status'] !== 'paga'): ?>
        <form method="post" action="<?= base_url('cards/pay-invoice') ?>" class="form inline">
            <?= csrf_field() ?>
            <input type="hidden" name="card_id" value="<?= e($card['id']) ?>">
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <label>Conta para pagamento</label>
            <select name="account_id" required>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= e($account['id']) ?>"><?= e($account['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-primary" type="submit">Pagar fatura</button>
        </form>
    <?php endif; ?>
</section>

<section class="card">
    <h3>Compras do ciclo</h3>
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
</section>
