<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

if (current_user()) {
    redirect('/pages/today.php');
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$attempts = $_SESSION['login_attempts'][$ip]['count'] ?? 0;
$lastAttempt = $_SESSION['login_attempts'][$ip]['time'] ?? 0;
$blocked = $attempts >= 5 && (time() - $lastAttempt) < 900;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if ($blocked) {
        $error = 'Muitas tentativas. Aguarde alguns minutos.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
            unset($_SESSION['login_attempts'][$ip]);
            redirect('/pages/today.php');
        }

        $_SESSION['login_attempts'][$ip] = [
            'count' => $attempts + 1,
            'time' => time(),
        ];
        $error = 'Credenciais inválidas.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?php echo e(APP_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="bg-zinc-900 p-8 rounded w-full max-w-md space-y-4">
        <h1 class="text-xl font-bold">Login</h1>
        <?php if ($error): ?>
            <div class="bg-red-600 px-3 py-2 rounded text-sm"> <?php echo e($error); ?> </div>
        <?php endif; ?>
        <form method="post" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <div>
                <label class="text-sm">Email</label>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="email" name="email" required>
            </div>
            <div>
                <label class="text-sm">Senha</label>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="password" name="password" required>
            </div>
            <button class="w-full bg-white text-black py-2 rounded font-semibold" type="submit">Entrar</button>
        </form>
        <p class="text-sm text-gray-400">Novo por aqui? <a class="underline" href="<?php echo BASE_URL; ?>/pages/register.php">Registrar</a></p>
    </div>
</body>
</html>
