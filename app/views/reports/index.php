<div class="page-header">
    <h2>Relatórios</h2>
</div>

<form method="get" action="<?= base_url('reports') ?>" class="filter-bar">
    <input type="date" name="start_date" value="<?= e($start) ?>">
    <input type="date" name="end_date" value="<?= e($end) ?>">
    <button class="btn-outline" type="submit">Atualizar</button>
</form>

<div class="reports-grid">
    <section class="card">
        <h3>Despesas por categoria</h3>
        <div class="chart-list" data-chart="category">
            <?php foreach ($categoryTotals as $row): ?>
                <div class="chart-row" data-label="<?= e($row['name'] ?? 'Sem categoria') ?>" data-value="<?= e($row['total']) ?>">
                    <span><?= e($row['name'] ?? 'Sem categoria') ?></span>
                    <strong>R$ <?= number_format($row['total'], 2, ',', '.') ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chart-bars" id="category-bars"></div>
    </section>

    <section class="card">
        <h3>Receitas vs Despesas (últimos 6 meses)</h3>
        <div class="chart-bars" id="monthly-bars">
            <?php foreach ($monthlyTotals as $row): ?>
                <div class="bar-group" data-label="<?= e($row['ym']) ?>" data-receita="<?= e($row['receita']) ?>" data-despesa="<?= e($row['despesa']) ?>">
                    <div class="bar receita"></div>
                    <div class="bar despesa"></div>
                    <span><?= e($row['ym']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<section class="card">
    <h3>Resumo do período</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categoryTotals as $row): ?>
                <tr>
                    <td><?= e($row['name'] ?? 'Sem categoria') ?></td>
                    <td>R$ <?= number_format($row['total'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
