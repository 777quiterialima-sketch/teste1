<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Meus cartões</h1>
    <a class="btn-primary" href="<?= url('cards/create') ?>">Novo cartão</a>
</div>
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Cartão</th>
                <th>Bandeira</th>
                <th>Limite</th>
                <th>Fechamento</th>
                <th>Vencimento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cards as $card): ?>
                <tr>
                    <td><?= e($card['name']) ?></td>
                    <td><?= e($card['brand']) ?></td>
                    <td>R$ <?= number_format($card['limit_total'], 2, ',', '.') ?></td>
                    <td>Dia <?= e($card['close_day']) ?></td>
                    <td>Dia <?= e($card['due_day']) ?></td>
                    <td>
                        <a class="btn-link" href="<?= url('cards/edit?id=' . $card['id']) ?>">Editar</a>
                        <a class="btn-link" href="<?= url('cards/' . $card['id'] . '/invoice') ?>">Ver fatura</a>
                        <form method="post" class="inline" action="<?= url('cards/delete') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($card['id']) ?>">
                            <button class="btn-link danger" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require base_path('app/views/layouts/footer.php'); ?>
