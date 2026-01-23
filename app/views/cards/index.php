<div class="page-header">
    <h2>Cartões</h2>
    <a class="btn-primary" href="<?= base_url('cards/create') ?>">Novo cartão</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Bandeira</th>
            <th>Limite total</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cards as $card): ?>
            <tr>
                <td><?= e($card['name']) ?></td>
                <td><?= e($card['brand']) ?></td>
                <td>R$ <?= number_format($card['limit_total'], 2, ',', '.') ?></td>
                <td>
                    <a href="<?= base_url('cards/edit?id=' . $card['id']) ?>" class="link">Editar</a>
                    <a href="<?= base_url('cards/' . $card['id'] . '/invoice?month=' . date('Y-m')) ?>" class="link">Ver fatura</a>
                    <form method="post" action="<?= base_url('cards/delete') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($card['id']) ?>">
                        <button class="link danger" type="submit">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
