<?php require base_path('app/views/layouts/header.php'); ?>
<section class="auth-card">
    <h1>Criar conta</h1>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <label>Nome
            <input type="text" name="name" required>
        </label>
        <label>E-mail
            <input type="email" name="email" required>
        </label>
        <label>Senha
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Cadastrar</button>
    </form>
    <p class="helper">Já tem conta? <a href="<?= url('login') ?>">Entrar</a></p>
</section>
<?php require base_path('app/views/layouts/footer.php'); ?>
