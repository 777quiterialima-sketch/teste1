<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1><?= $transaction ? 'Editar lançamento' : 'Novo lançamento' ?></h1>
</div>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <label>Tipo
        <select name="type" id="transactionType">
            <?php foreach (['income' => 'Receita', 'expense' => 'Despesa', 'transfer' => 'Transferência'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= ($transaction['type'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Data
        <input type="date" name="date" value="<?= e($transaction['date'] ?? date('Y-m-d')) ?>">
    </label>
    <label>Descrição
        <input type="text" name="description" value="<?= e($transaction['description'] ?? '') ?>" required>
    </label>
    <label>Categoria
        <select name="category_id">
            <option value="">Sem categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>" <?= ($transaction['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Valor
        <input type="number" step="0.01" name="amount" value="<?= e($transaction['amount'] ?? 0) ?>" required>
    </label>
    <label>Conta
        <select name="account_id" id="accountOrigin">
            <option value="">Selecione</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= e($account['id']) ?>" <?= ($transaction['account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Conta destino (transferência)
        <select name="account_dest_id" id="accountDest">
            <option value="">Selecione</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= e($account['id']) ?>" <?= ($transaction['account_dest_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Forma de pagamento
        <select name="payment_method" id="paymentMethod">
            <?php foreach (['dinheiro' => 'Dinheiro', 'debito' => 'Débito', 'cartao' => 'Cartão', 'pix' => 'Pix'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= ($transaction['payment_method'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Cartão
        <select name="card_id" id="cardId">
            <option value="">Nenhum</option>
            <?php foreach ($cards as $card): ?>
                <option value="<?= e($card['id']) ?>" <?= ($transaction['card_id'] ?? '') == $card['id'] ? 'selected' : '' ?>><?= e($card['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Parcelas (cartão)
        <input type="number" name="installments" min="1" value="1">
    </label>
    <label>Tags
        <input type="text" name="tags" value="<?= e($transaction['tags'] ?? '') ?>">
    </label>
    <label>Observação
        <textarea name="notes" rows="3"><?= e($transaction['notes'] ?? '') ?></textarea>
    </label>
    <button class="btn-primary" type="submit">Salvar</button>
</form>
<?php require base_path('app/views/layouts/footer.php'); ?>
