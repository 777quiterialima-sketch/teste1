<?php require base_path('app/views/layouts/header.php'); ?>
<section class="auth-card">
    <h1>Entrar</h1>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <label>E-mail
            <input type="email" name="email" required>
        </label>
        <label>Senha
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn-primary">Entrar</button>
    </form>
    <p class="helper">Ainda não tem conta? <a href="<?= url('register') ?>">Crie aqui</a></p>
</section>
<?php require base_path('app/views/layouts/footer.php'); ?>
