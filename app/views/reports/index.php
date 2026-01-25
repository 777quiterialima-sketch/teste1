<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Relatórios</h1>
    <form method="get" class="inline">
        <input type="month" name="month" value="<?= e($month) ?>">
        <button class="btn-outline" type="submit">Filtrar</button>
    </form>
</div>
<div class="dashboard-grid">
    <div class="card">
        <h3>Despesas por categoria</h3>
        <canvas id="categoryChart" height="200"></canvas>
        <ul class="list">
            <?php foreach ($expensesByCategory as $expense): ?>
                <li class="list-item">
                    <span><?= e($expense['name']) ?></span>
                    <strong>R$ <?= number_format($expense['total'], 2, ',', '.') ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card">
        <h3>Receitas vs Despesas</h3>
        <canvas id="incomeExpenseChart" height="200"></canvas>
        <table class="table">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th>Receitas</th>
                    <th>Despesas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incomeExpense as $row): ?>
                    <tr>
                        <td><?= e($row['month']) ?></td>
                        <td>R$ <?= number_format($row['income'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($row['expense'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    window.reportData = {
        categories: <?= json_encode($expensesByCategory) ?>,
        incomeExpense: <?= json_encode($incomeExpense) ?>,
    };
</script>
<script src="<?= url('assets/js/charts.js') ?>"></script>
<?php require base_path('app/views/layouts/footer.php'); ?>
