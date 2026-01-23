<div class="page-header">
    <h2>Contas</h2>
    <a class="btn-primary" href="<?= base_url('accounts/create') ?>">Nova conta</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Tipo</th>
            <th>Saldo inicial</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($accounts as $account): ?>
            <tr>
                <td><?= e($account['name']) ?></td>
                <td><?= e($account['type']) ?></td>
                <td>R$ <?= number_format($account['initial_balance'], 2, ',', '.') ?></td>
                <td>
                    <a href="<?= base_url('accounts/edit?id=' . $account['id']) ?>" class="link">Editar</a>
                    <form method="post" action="<?= base_url('accounts/delete') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($account['id']) ?>">
                        <button class="link danger" type="submit">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
