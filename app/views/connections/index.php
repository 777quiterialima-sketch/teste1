<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Conexões bancárias</h1>
</div>
<div class="dashboard-grid">
    <div class="card">
        <h3>Conexão manual</h3>
        <form method="post" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="connect">
            <label>Banco
                <input type="text" name="bank_name" required>
            </label>
            <label>Conta vinculada
                <select name="account_id" required>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= e($account['id']) ?>"><?= e($account['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn-primary" type="submit">Registrar conexão</button>
        </form>
        <div class="list">
            <?php foreach ($connections as $connection): ?>
                <div class="list-item">
                    <div>
                        <strong><?= e($connection['bank_name']) ?></strong>
                        <small><?= e($connection['account_name']) ?></small>
                    </div>
                    <span class="badge"><?= e($connection['status']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h3>Importar extrato CSV</h3>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="import_csv">
            <label>Arquivo CSV
                <input type="file" name="file" accept=".csv" required>
            </label>
            <div class="grid-2">
                <label>Coluna Data
                    <input type="number" name="col_date" value="0">
                </label>
                <label>Coluna Descrição
                    <input type="number" name="col_description" value="1">
                </label>
                <label>Coluna Valor
                    <input type="number" name="col_amount" value="2">
                </label>
                <label>Coluna Tipo
                    <input type="number" name="col_type" value="3">
                </label>
            </div>
            <button class="btn-primary" type="submit">Carregar CSV</button>
        </form>

        <?php if ($importPreview): ?>
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
                    <?php foreach (array_slice($importPreview['rows'], 0, 5) as $row): ?>
                        <tr>
                            <td><?= e($row['date']) ?></td>
                            <td><?= e($row['description']) ?></td>
                            <td><?= e($row['amount']) ?></td>
                            <td><?= e($row['type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="confirm_import">
                <button class="btn-primary" type="submit">Confirmar importação</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Importar OFX</h3>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="import_ofx">
            <label>Arquivo OFX
                <input type="file" name="ofx" accept=".ofx" required>
            </label>
            <button class="btn-primary" type="submit">Importar OFX</button>
        </form>
        <p class="helper">Parser simples para os principais bancos.</p>
    </div>
</div>
<?php require base_path('app/views/layouts/footer.php'); ?>
