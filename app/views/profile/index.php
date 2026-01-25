<?php require base_path('app/views/layouts/header.php'); ?>
<div class="page-header">
    <h1>Meu perfil</h1>
</div>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <label>Nome
        <input type="text" name="name" value="<?= e($user['name'] ?? '') ?>" required>
    </label>
    <label>E-mail
        <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
    </label>
    <label>Nova senha
        <input type="password" name="new_password" placeholder="Deixe em branco para manter">
    </label>
    <button class="btn-primary" type="submit">Salvar</button>
</form>
<?php require base_path('app/views/layouts/footer.php'); ?>
