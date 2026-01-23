<h2>Meu perfil</h2>
<form method="post" action="<?= base_url('profile') ?>" class="form">
    <?= csrf_field() ?>
    <label>Nome</label>
    <input type="text" name="name" value="<?= e($user['name'] ?? '') ?>" required>

    <label>Nova senha</label>
    <input type="password" name="password" placeholder="Deixe em branco para manter">

    <button class="btn-primary" type="submit">Salvar</button>
</form>
