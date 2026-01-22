<form method="post" action="<?= base_url($account ? 'accounts/update' : 'accounts/store') ?>" class="form">
    <?= csrf_field() ?>
    <?php if ($account): ?>
        <input type="hidden" name="id" value="<?= e($account['id']) ?>">
    <?php endif; ?>

    <label>Nome</label>
    <input type="text" name="name" value="<?= e($account['name'] ?? '') ?>" required>

    <label>Tipo</label>
    <select name="type">
        <?php $types = ['corrente' => 'Conta corrente', 'poupanca' => 'Poupança', 'carteira' => 'Carteira']; ?>
        <?php foreach ($types as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= ($account['type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Saldo inicial</label>
    <input type="number" step="0.01" name="initial_balance" value="<?= e($account['initial_balance'] ?? 0) ?>" required>

    <label>Cor</label>
    <input type="color" name="color" value="<?= e($account['color'] ?? '#2ecc71') ?>">

    <label>Ícone (opcional)</label>
    <input type="text" name="icon" value="<?= e($account['icon'] ?? '') ?>" placeholder="Ex: 💳">

    <button class="btn-primary" type="submit">Salvar</button>
</form>
