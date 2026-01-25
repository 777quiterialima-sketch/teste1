<?php require base_path('app/views/layouts/header.php'); ?>

<section class="dashboard-hero">
    <div class="card hero-card">
        <div>
            <h2>Boa tarde, <?= e($user['name']) ?>!</h2>
            <p>Resumo de <?= e($month) ?></p>
        </div>
        <div class="hero-metrics">
            <div>
                <span>Receita mensal</span>
                <strong class="text-green">R$ <?= number_format($totals['income'] ?? 0, 2, ',', '.') ?></strong>
            </div>
            <div>
                <span>Despesa mensal</span>
                <strong class="text-red">R$ <?= number_format($totals['expense'] ?? 0, 2, ',', '.') ?></strong>
            </div>
        </div>
    </div>
    <div class="card hero-card">
        <div class="card-header">
            <h3>Conexões ativas</h3>
            <a class="btn-outline" href="<?= url('connections') ?>">+</a>
        </div>
        <p>Mantenha suas contas sempre atualizadas com conexões manuais e importações.</p>
    </div>
</section>

<section class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3>Saldo geral</h3>
            <span class="badge">R$ <?= number_format($totalBalance, 2, ',', '.') ?></span>
        </div>
        <div class="list">
            <?php foreach ($accounts as $account): ?>
                <div class="list-item">
                    <div>
                        <span class="dot" style="background: <?= e($account['color'] ?? '#2ebd59') ?>"></span>
                        <?= e($account['name']) ?>
                        <small><?= e($account['type']) ?></small>
                    </div>
                    <strong>R$ <?= number_format($account['balance'], 2, ',', '.') ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="btn-link" href="<?= url('accounts') ?>">Gerenciar contas</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Faturas do mês</h3>
        </div>
        <div class="list">
            <?php foreach ($cards as $item): ?>
                <div class="list-item">
                    <div>
                        <strong><?= e($item['card']['name']) ?></strong>
                        <small><?= e($item['card']['brand']) ?></small>
                    </div>
                    <div class="text-right">
                        <span>Disponível</span>
                        <strong>R$ <?= number_format($item['available'], 2, ',', '.') ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="btn-link" href="<?= url('cards') ?>">Gerenciar cartões</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Limites de gastos</h3>
        </div>
        <?php if (empty($budgetList)): ?>
            <p>Nenhum limite configurado para este mês.</p>
        <?php else: ?>
            <?php foreach ($budgetList as $budget): ?>
                <?php
                    $spent = 0;
                    foreach ($expensesByCategory as $expense) {
                        if ($budget['category_name'] && $expense['name'] === $budget['category_name']) {
                            $spent = $expense['total'];
                        }
                        if ($budget['category_id'] === null) {
                            $spent = array_sum(array_column($expensesByCategory, 'total'));
                        }
                    }
                    $percent = $budget['limit_amount'] > 0 ? min(100, ($spent / $budget['limit_amount']) * 100) : 0;
                ?>
                <div class="budget-item">
                    <div class="budget-info">
                        <strong><?= e($budget['category_name'] ?? 'Total mensal') ?></strong>
                        <span>R$ <?= number_format($spent, 2, ',', '.') ?> / R$ <?= number_format($budget['limit_amount'], 2, ',', '.') ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar <?= $percent > 80 ? 'danger' : '' ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a class="btn-link" href="<?= url('budgets') ?>">Configurar limites</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Maiores gastos por categoria</h3>
        </div>
        <div class="list">
            <?php foreach ($expensesByCategory as $expense): ?>
                <div class="list-item">
                    <span><?= e($expense['name']) ?></span>
                    <strong class="text-red">R$ <?= number_format($expense['total'], 2, ',', '.') ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="btn-link" href="<?= url('reports') ?>">Ver relatório</a>
    </div>
</section>

<?php require base_path('app/views/layouts/footer.php'); ?>
