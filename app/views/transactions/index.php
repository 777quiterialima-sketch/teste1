<div class="page-header">
    <h2>Lançamentos</h2>
    <a class="btn-primary" href="<?= base_url('transactions/create') ?>">Novo lançamento</a>
</div>

<form method="get" action="<?= base_url('transactions') ?>" class="filter-bar">
    <input type="date" name="start_date" value="<?= e($filters['start_date'] ?? '') ?>">
    <input type="date" name="end_date" value="<?= e($filters['end_date'] ?? '') ?>">
    <select name="type">
        <option value="">Tipo</option>
        <?php foreach (['receita', 'despesa', 'transferencia'] as $type): ?>
            <option value="<?= e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="account_id">
        <option value="">Conta</option>
        <?php foreach ($accounts as $account): ?>
            <option value="<?= e($account['id']) ?>" <?= ($filters['account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="category_id">
        <option value="">Categoria</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= e($category['id']) ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="search" placeholder="Buscar" value="<?= e($filters['search'] ?? '') ?>">
    <button class="btn-outline" type="submit">Filtrar</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Data</th>
            <th>Descrição</th>
            <th>Tipo</th>
            <th>Conta</th>
            <th>Valor</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td><?= e($transaction['date']) ?></td>
                <td><?= e($transaction['description']) ?></td>
                <td><?= e($transaction['type']) ?></td>
                <td><?= e($transaction['account_name'] ?? $transaction['dest_name'] ?? '-') ?></td>
                <td>R$ <?= number_format($transaction['amount'], 2, ',', '.') ?></td>
                <td>
                    <a href="<?= base_url('transactions/edit?id=' . $transaction['id']) ?>" class="link">Editar</a>
                    <form method="post" action="<?= base_url('transactions/delete') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($transaction['id']) ?>">
                        <button class="link danger" type="submit">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php $query = array_merge($filters, ['page' => $i]); ?>
            <a class="page-link <?= $page === $i ? 'active' : '' ?>" href="<?= base_url('transactions?' . http_build_query($query)) ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
