<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Limites de gastos</h1>
    <form method="get" class="inline">
        <input type="month" name="month" value="<?= e($month) ?>">
        <button class="btn-outline" type="submit">Ir</button>
    </form>
</div>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <label>Limite total mensal
        <?php
            $totalBudget = null;
            foreach ($budgets as $budget) {
                if ($budget['category_id'] === null) {
                    $totalBudget = $budget['limit_amount'];
                }
            }
        ?>
        <input type="number" step="0.01" name="total_limit" value="<?= e($totalBudget ?? 0) ?>">
    </label>
    <h3>Limites por categoria</h3>
    <div class="grid-2">
        <?php foreach ($categories as $category): ?>
            <?php
                $value = '';
                foreach ($budgets as $budget) {
                    if ($budget['category_id'] == $category['id']) {
                        $value = $budget['limit_amount'];
                    }
                }
            ?>
            <label><?= e($category['name']) ?>
                <input type="number" step="0.01" name="category_limit[<?= e($category['id']) ?>]" value="<?= e($value) ?>">
            </label>
        <?php endforeach; ?>
    </div>
    <button class="btn-primary" type="submit">Salvar limites</button>
</form>
<?php require base_path('app/views/layouts/footer.php'); ?>
