<form method="post" action="<?= base_url($card ? 'cards/update' : 'cards/store') ?>" class="form">
    <?= csrf_field() ?>
    <?php if ($card): ?>
        <input type="hidden" name="id" value="<?= e($card['id']) ?>">
    <?php endif; ?>

    <label>Nome</label>
    <input type="text" name="name" value="<?= e($card['name'] ?? '') ?>" required>

    <label>Bandeira</label>
    <input type="text" name="brand" value="<?= e($card['brand'] ?? '') ?>" required>

    <label>Limite total</label>
    <input type="number" step="0.01" name="limit_total" value="<?= e($card['limit_total'] ?? 0) ?>" required>

    <label>Dia de fechamento</label>
    <input type="number" name="closing_day" value="<?= e($card['closing_day'] ?? 1) ?>" required>

    <label>Dia de vencimento</label>
    <input type="number" name="due_day" value="<?= e($card['due_day'] ?? 10) ?>" required>

    <button class="btn-primary" type="submit">Salvar</button>
</form>
