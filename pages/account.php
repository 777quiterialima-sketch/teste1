<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

require_login();

$user = current_user();
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';

    if ($current === '' || $new === '') {
        $error = 'Preencha todos os campos.';
    } else {
        $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $data = $stmt->fetch();

        if (!$data || !password_verify($current, $data['password_hash'])) {
            $error = 'Senha atual incorreta.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $update->execute([$hash, $user['id']]);
            $success = 'Senha atualizada.';
        }
    }
}

render_header('Conta');
?>
<div class="bg-zinc-900 p-6 rounded space-y-4 max-w-xl">
    <h2 class="text-lg font-semibold">Trocar senha</h2>
    <?php if ($error): ?>
        <div class="bg-red-600 px-3 py-2 rounded text-sm"> <?php echo e($error); ?> </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="bg-emerald-600 px-3 py-2 rounded text-sm"> <?php echo e($success); ?> </div>
    <?php endif; ?>
    <form method="post" class="space-y-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <div>
            <label class="text-sm">Senha atual</label>
            <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="password" name="current_password" required>
        </div>
        <div>
            <label class="text-sm">Nova senha</label>
            <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="password" name="new_password" required>
        </div>
        <button class="bg-white text-black px-4 py-2 rounded font-semibold" type="submit">Salvar</button>
    </form>
</div>
<?php render_footer(); ?>
