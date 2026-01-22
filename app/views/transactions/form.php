<form method="post" action="<?= base_url($transaction ? 'transactions/update' : 'transactions/store') ?>" class="form">
    <?= csrf_field() ?>
    <?php if ($transaction): ?>
        <input type="hidden" name="id" value="<?= e($transaction['id']) ?>">
    <?php endif; ?>

    <label>Tipo</label>
    <select name="type" id="transaction-type">
        <?php foreach (['receita' => 'Receita', 'despesa' => 'Despesa', 'transferencia' => 'Transferência'] as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= ($transaction['type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Data</label>
    <input type="date" name="date" value="<?= e($transaction['date'] ?? date('Y-m-d')) ?>">

    <label>Descrição</label>
    <input type="text" name="description" value="<?= e($transaction['description'] ?? '') ?>" required>

    <label>Categoria</label>
    <select name="category_id">
        <option value="">Nenhuma</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= e($category['id']) ?>" <?= ($transaction['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Valor</label>
    <input type="number" step="0.01" name="amount" value="<?= e($transaction['amount'] ?? 0) ?>" required>

    <label>Conta origem</label>
    <select name="account_id">
        <option value="">Selecione</option>
        <?php foreach ($accounts as $account): ?>
            <option value="<?= e($account['id']) ?>" <?= ($transaction['account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Conta destino (transferência)</label>
    <select name="account_dest_id">
        <option value="">Selecione</option>
        <?php foreach ($accounts as $account): ?>
            <option value="<?= e($account['id']) ?>" <?= ($transaction['account_dest_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Forma de pagamento</label>
    <select name="payment_method">
        <?php foreach (['dinheiro' => 'Dinheiro', 'debito' => 'Débito', 'cartao' => 'Cartão', 'pix' => 'Pix'] as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= ($transaction['payment_method'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Cartão (se compra no cartão)</label>
    <select name="card_id">
        <option value="">Nenhum</option>
        <?php foreach ($cards as $card): ?>
            <option value="<?= e($card['id']) ?>" <?= ($transaction['card_id'] ?? '') == $card['id'] ? 'selected' : '' ?>><?= e($card['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Parcelas (para cartão)</label>
    <input type="number" name="installments" value="1" min="1">

    <label>Tags</label>
    <input type="text" name="tags" value="<?= e($transaction['tags'] ?? '') ?>">

    <label>Observação</label>
    <textarea name="notes"><?= e($transaction['notes'] ?? '') ?></textarea>

    <button class="btn-primary" type="submit">Salvar</button>
</form>
