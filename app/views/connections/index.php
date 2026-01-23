<div class="page-header">
    <h2>Conexão bancária</h2>
</div>

<div class="dashboard-columns">
    <section class="card">
        <h3>Conexões ativas</h3>
        <ul class="list">
            <?php foreach ($connections as $connection): ?>
                <li>
                    <?= e($connection['name']) ?> (<?= e($connection['type']) ?>)
                    <span class="badge"><?= e($connection['account_name'] ?? '') ?></span>
                    <form method="post" action="<?= base_url('connections/delete') ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($connection['id']) ?>">
                        <button class="link danger" type="submit">Remover</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <h4>Nova conexão manual</h4>
        <form method="post" action="<?= base_url('connections/store') ?>" class="form">
            <?= csrf_field() ?>
            <label>Nome da conexão</label>
            <input type="text" name="name" required>
            <label>Conta vinculada</label>
            <select name="account_id" required>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= e($account['id']) ?>"><?= e($account['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Tipo</label>
            <select name="type">
                <option value="manual">Manual</option>
                <option value="cartao">Cartão</option>
            </select>
            <button class="btn-primary" type="submit">Adicionar conexão</button>
        </form>
    </section>

    <section class="card">
        <h3>Importar extrato (CSV ou OFX)</h3>
        <form method="post" action="<?= base_url('connections/import') ?>" enctype="multipart/form-data" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="preview">
            <label>Arquivo</label>
            <input type="file" name="import_file" accept=".csv,.ofx" required>
            <label>Delimitador CSV</label>
            <input type="text" name="delimiter" value=";">
            <label>Coluna data (0,1,2...)</label>
            <input type="number" name="date_col" value="0">
            <label>Coluna descrição</label>
            <input type="number" name="desc_col" value="1">
            <label>Coluna valor</label>
            <input type="number" name="amount_col" value="2">
            <label>Coluna tipo</label>
            <input type="number" name="type_col" value="3">
            <button class="btn-primary" type="submit">Gerar prévia</button>
        </form>

        <?php if ($preview): ?>
            <div class="preview">
                <h4>Prévia da importação</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($preview['items'], 0, 10) as $item): ?>
                            <tr>
                                <td><?= e($item['date']) ?></td>
                                <td><?= e($item['description']) ?></td>
                                <td>R$ <?= number_format($item['amount'], 2, ',', '.') ?></td>
                                <td><?= e($item['type']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" action="<?= base_url('connections/import') ?>" class="form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="confirm">
                    <label>Conta para lançar os itens</label>
                    <select name="account_id" required>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= e($account['id']) ?>"><?= e($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-primary" type="submit">Confirmar importação</button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</div>
