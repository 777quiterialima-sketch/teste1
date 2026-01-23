<form method="post" action="<?= base_url('register') ?>" class="form">
    <?= csrf_field() ?>
    <label>Nome</label>
    <input type="text" name="name" required>

    <label>E-mail</label>
    <input type="email" name="email" required>

    <label>Senha</label>
    <input type="password" name="password" required>

    <button class="btn-primary" type="submit">Criar conta</button>
    <p class="form-hint">Já tem conta? <a href="<?= base_url('login') ?>">Entrar</a></p>
</form>
