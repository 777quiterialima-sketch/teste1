<form method="post" action="<?= base_url('login') ?>" class="form">
    <?= csrf_field() ?>
    <label>E-mail</label>
    <input type="email" name="email" required>

    <label>Senha</label>
    <input type="password" name="password" required>

    <button class="btn-primary" type="submit">Entrar</button>
    <p class="form-hint">Ainda não tem conta? <a href="<?= base_url('register') ?>">Criar conta</a></p>
</form>
