<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'OrganizzeLite') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h1>OrganizzeLite</h1>
        <?php foreach (get_flash() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endforeach; ?>
        <?= $content ?>
    </div>
</body>
</html>
