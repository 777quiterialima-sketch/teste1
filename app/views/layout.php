<?php $user = current_user(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'OrganizzeLite') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <script defer src="<?= base_url('assets/js/app.js') ?>"></script>
</head>
<body>
    <header class="topbar">
        <div class="container topbar__content">
            <div class="logo">OrganizzeLite</div>
            <nav class="menu">
                <a href="<?= base_url('dashboard') ?>">Visão Geral</a>
                <a href="<?= base_url('transactions') ?>">Lançamentos</a>
                <a href="<?= base_url('reports') ?>">Relatórios</a>
                <a href="<?= base_url('budgets') ?>">Limite de Gastos</a>
                <a href="<?= base_url('connections') ?>">Conexão Bancária</a>
                <a href="<?= base_url('accounts') ?>">Contas</a>
                <a href="<?= base_url('cards') ?>">Cartões</a>
            </nav>
            <div class="menu-actions">
                <a href="<?= base_url('profile') ?>" class="icon-button">⚙️</a>
                <a href="<?= base_url('logout') ?>" class="icon-button">Sair</a>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <?php foreach (get_flash() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endforeach; ?>
            <?= $content ?>
        </div>
    </main>
</body>
</html>
