<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1><?= $card ? 'Editar cartão' : 'Novo cartão' ?></h1>
</div>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <label>Nome
        <input type="text" name="name" value="<?= e($card['name'] ?? '') ?>" required>
    </label>
    <label>Bandeira
        <input type="text" name="brand" value="<?= e($card['brand'] ?? '') ?>" required>
    </label>
    <label>Limite total
        <input type="number" step="0.01" name="limit_total" value="<?= e($card['limit_total'] ?? 0) ?>">
    </label>
    <label>Dia de fechamento
        <input type="number" name="close_day" value="<?= e($card['close_day'] ?? 5) ?>">
    </label>
    <label>Dia de vencimento
        <input type="number" name="due_day" value="<?= e($card['due_day'] ?? 15) ?>">
    </label>
    <button class="btn-primary" type="submit">Salvar</button>
</form>
<?php require base_path('app/views/layouts/footer.php'); ?>
