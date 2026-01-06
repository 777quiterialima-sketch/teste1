<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

if (current_user()) {
    redirect('/pages/today.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Preencha todos os campos.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email já cadastrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = db()->prepare('INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
            $insert->execute([$name, $email, $hash]);
            flash('message', 'Conta criada. Faça login.');
            redirect('/pages/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar - <?php echo e(APP_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="bg-zinc-900 p-8 rounded w-full max-w-md space-y-4">
        <h1 class="text-xl font-bold">Registrar</h1>
        <?php if ($error): ?>
            <div class="bg-red-600 px-3 py-2 rounded text-sm"> <?php echo e($error); ?> </div>
        <?php endif; ?>
        <form method="post" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <div>
                <label class="text-sm">Nome</label>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="text" name="name" required>
            </div>
            <div>
                <label class="text-sm">Email</label>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="email" name="email" required>
            </div>
            <div>
                <label class="text-sm">Senha</label>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="password" name="password" required>
            </div>
            <button class="w-full bg-white text-black py-2 rounded font-semibold" type="submit">Criar conta</button>
        </form>
        <p class="text-sm text-gray-400"><a class="underline" href="<?php echo BASE_URL; ?>/pages/login.php">Voltar ao login</a></p>
    </div>
</body>
</html>
