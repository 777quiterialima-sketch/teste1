<?php $user = current_user(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(config('app_name', 'Organizador Financeiro')) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<header class="topbar">
    <div class="container topbar-content">
        <div class="logo">Organizze+</div>
        <?php if ($user): ?>
            <nav class="menu">
                <a href="<?= url('dashboard') ?>">Visão Geral</a>
                <a href="<?= url('transactions') ?>">Lançamentos</a>
                <a href="<?= url('reports') ?>">Relatórios</a>
                <a href="<?= url('budgets') ?>">Limite de Gastos</a>
                <a href="<?= url('connections') ?>">Conexão Bancária</a>
            </nav>
            <div class="topbar-actions">
                <a href="<?= url('profile') ?>" class="icon-button">⚙️</a>
                <a href="<?= url('logout') ?>" class="icon-button">Sair</a>
            </div>
        <?php endif; ?>
    </div>
</header>
<main class="container">
    <?php if ($message = flash('success')): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert error"><?= e($message) ?></div>
    <?php endif; ?>
