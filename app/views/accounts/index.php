<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Minhas contas</h1>
    <a class="btn-primary" href="<?= url('accounts/create') ?>">Nova conta</a>
</div>
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Conta</th>
                <th>Tipo</th>
                <th>Saldo atual</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account): ?>
                <tr>
                    <td><?= e($account['name']) ?></td>
                    <td><?= e($account['type']) ?></td>
                    <td>R$ <?= number_format($account['balance'], 2, ',', '.') ?></td>
                    <td>
                        <a class="btn-link" href="<?= url('accounts/edit?id=' . $account['id']) ?>">Editar</a>
                        <form method="post" class="inline" action="<?= url('accounts/delete') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($account['id']) ?>">
                            <button class="btn-link danger" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require base_path('app/views/layouts/footer.php'); ?>
