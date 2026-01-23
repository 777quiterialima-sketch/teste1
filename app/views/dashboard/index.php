<div class="dashboard-grid">
    <section class="card welcome-card">
        <h2>Boa tarde, <?= e($user['name'] ?? '') ?>!</h2>
        <p>Resumo do mês atual</p>
        <div class="metrics">
            <div>
                <span>Receita mensal</span>
                <strong class="text-success">R$ <?= number_format($income, 2, ',', '.') ?></strong>
            </div>
            <div>
                <span>Despesa mensal</span>
                <strong class="text-danger">R$ <?= number_format($expense, 2, ',', '.') ?></strong>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h3>Conexões ativas</h3>
            <a href="<?= base_url('connections') ?>" class="btn-outline">+ Adicionar</a>
        </div>
        <p>Gerencie suas conexões e importações de extrato.</p>
    </section>
</div>

<div class="dashboard-columns">
    <div>
        <section class="card">
            <h3>Saldo geral</h3>
            <p class="balance">R$ <?= number_format($totalBalance, 2, ',', '.') ?></p>
        </section>

        <section class="card">
            <h3>Minhas contas</h3>
            <ul class="list">
                <?php foreach ($accounts as $account): ?>
                    <li>
                        <span class="dot" style="background: <?= e($account['color'] ?: '#2ecc71') ?>"></span>
                        <?= e($account['name']) ?>
                        <strong>R$ <?= number_format($account['balance'], 2, ',', '.') ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="card">
            <h3>Alertas de limite</h3>
            <?php if (!$budgetUsage): ?>
                <p>Defina limites para acompanhar seus gastos.</p>
            <?php else: ?>
                <?php foreach ($budgetUsage as $budget): ?>
                    <div class="budget-item">
                        <div class="budget-header">
                            <span><?= e($budget['category_name']) ?></span>
                            <span>R$ <?= number_format($budget['spent'], 2, ',', '.') ?> / <?= number_format($budget['limit_amount'], 2, ',', '.') ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= $budget['percent'] >= 100 ? 'danger' : ($budget['percent'] >= 80 ? 'warning' : '') ?>" style="width: <?= $budget['percent'] ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>

    <div>
        <section class="card">
            <h3>Faturas do mês</h3>
            <ul class="list">
                <?php foreach ($cards as $card): ?>
                    <li>
                        <?= e($card['name']) ?>
                        <strong>R$ <?= number_format($card['invoice_total'], 2, ',', '.') ?></strong>
                        <span class="badge">Disponível R$ <?= number_format($card['available'], 2, ',', '.') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="card">
            <h3>Meus cartões</h3>
            <ul class="list">
                <?php foreach ($cards as $card): ?>
                    <li>
                        <?= e($card['name']) ?>
                        <strong>Limite R$ <?= number_format($card['limit_total'], 2, ',', '.') ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</div>
