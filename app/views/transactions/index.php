<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Lançamentos</h1>
    <a class="btn-primary" href="<?= url('transactions/create') ?>">Novo lançamento</a>
</div>
<form class="card filters" method="get">
    <label>Tipo
        <select name="type">
            <option value="">Todos</option>
            <option value="income" <?= ($filters['type'] ?? '') === 'income' ? 'selected' : '' ?>>Receita</option>
            <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Despesa</option>
            <option value="transfer" <?= ($filters['type'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transferência</option>
        </select>
    </label>
    <label>Conta
        <select name="account_id">
            <option value="">Todas</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= e($account['id']) ?>" <?= ($filters['account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Categoria
        <select name="category_id">
            <option value="">Todas</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Período
        <div class="row">
            <input type="date" name="start_date" value="<?= e($filters['start_date'] ?? '') ?>">
            <input type="date" name="end_date" value="<?= e($filters['end_date'] ?? '') ?>">
        </div>
    </label>
    <label>Busca
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Descrição">
    </label>
    <button class="btn-primary" type="submit">Filtrar</button>
</form>
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Conta</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= e($transaction['date']) ?></td>
                    <td><?= e($transaction['description']) ?></td>
                    <td><?= e($transaction['category_name'] ?? '-') ?></td>
                    <td><?= e($transaction['account_name'] ?? '-') ?></td>
                    <td><?= e($transaction['type']) ?></td>
                    <td class="<?= $transaction['type'] === 'income' ? 'text-green' : 'text-red' ?>">
                        R$ <?= number_format($transaction['amount'], 2, ',', '.') ?>
                    </td>
                    <td>
                        <a class="btn-link" href="<?= url('transactions/edit?id=' . $transaction['id']) ?>">Editar</a>
                        <form method="post" class="inline" action="<?= url('transactions/delete') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($transaction['id']) ?>">
                            <button class="btn-link danger" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require base_path('app/views/layouts/footer.php'); ?>
