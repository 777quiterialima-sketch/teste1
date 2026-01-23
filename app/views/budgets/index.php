<div class="page-header">
    <h2>Limites de gastos</h2>
</div>

<form method="get" action="<?= base_url('budgets') ?>" class="filter-bar">
    <input type="month" name="month" value="<?= e($month) ?>">
    <button class="btn-outline" type="submit">Atualizar</button>
</form>

<section class="card">
    <h3>Novo limite</h3>
    <form method="post" action="<?= base_url('budgets/store') ?>" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="month" value="<?= e($month) ?>">
        <label>Categoria (opcional para limite total)</label>
        <select name="category_id">
            <option value="">Total mensal</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>"><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Limite mensal</label>
        <input type="number" step="0.01" name="limit_amount" required>
        <button class="btn-primary" type="submit">Salvar limite</button>
    </form>
</section>

<section class="card">
    <h3>Limites configurados</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Limite</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($budgets as $budget): ?>
                <tr>
                    <td><?= e($budget['category_name'] ?? 'Total mensal') ?></td>
                    <td>R$ <?= number_format($budget['limit_amount'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
