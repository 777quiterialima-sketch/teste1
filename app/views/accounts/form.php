<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1><?= $account ? 'Editar conta' : 'Nova conta' ?></h1>
</div>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <label>Nome
        <input type="text" name="name" value="<?= e($account['name'] ?? '') ?>" required>
    </label>
    <label>Tipo
        <select name="type">
            <?php foreach (['corrente', 'poupanca', 'carteira'] as $type): ?>
                <option value="<?= $type ?>" <?= ($account['type'] ?? '') === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Saldo inicial
        <input type="number" step="0.01" name="initial_balance" value="<?= e($account['initial_balance'] ?? 0) ?>">
    </label>
    <label>Cor
        <input type="color" name="color" value="<?= e($account['color'] ?? '#2ebd59') ?>">
    </label>
    <button class="btn-primary" type="submit">Salvar</button>
</form>
<?php require base_path('app/views/layouts/footer.php'); ?>
