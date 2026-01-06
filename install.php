<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$installed = false;
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $seed = isset($_POST['seed']);

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Preencha todos os campos.';
    } else {
        try {
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            $pdo = db();
            $pdo->exec($schema);

            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if (!$stmt->fetch()) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insertUser = $pdo->prepare('INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
                $insertUser->execute([$name, $email, $hash]);
                $userId = (int)$pdo->lastInsertId();

                $defaultSettings = get_settings($userId);

                if ($seed) {
                    seed_logs($pdo, $userId, $defaultSettings);
                }
            }

            $success = 'Instalação concluída. Faça login com o usuário criado.';
            $installed = true;
        } catch (Throwable $e) {
            $error = 'Erro na instalação: ' . $e->getMessage();
        }
    }
}

function seed_logs(PDO $pdo, int $userId, array $settings): void
{
    for ($i = 0; $i < 10; $i++) {
        $date = date('Y-m-d', strtotime('-' . $i . ' days'));
        $log = [
            'water_cups' => rand(4, 10),
            'reading_minutes' => rand(10, 40),
            'workout_done' => rand(0, 1),
            'workout_minutes' => rand(10, 45),
            'english_done' => rand(0, 1),
            'english_minutes' => rand(5, 20),
            'market_done' => rand(0, 1),
            'meal_prep_done' => rand(0, 1),
            'nicotine_puffs' => rand(0, 15),
            'notes' => 'Seed automático',
        ];
        $score = compute_score($log, $settings);
        $status = compute_status($score);

        $stmt = $pdo->prepare('INSERT INTO daily_logs (user_id, log_date, water_cups, reading_minutes, workout_done, workout_minutes, english_done, english_minutes, market_done, meal_prep_done, nicotine_puffs, notes, score, status_color, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
            $userId,
            $date,
            $log['water_cups'],
            $log['reading_minutes'],
            $log['workout_done'],
            $log['workout_minutes'],
            $log['english_done'],
            $log['english_minutes'],
            $log['market_done'],
            $log['meal_prep_done'],
            $log['nicotine_puffs'],
            $log['notes'],
            $score,
            $status,
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalação - <?php echo e(APP_NAME); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center">
    <div class="bg-zinc-900 p-8 rounded w-full max-w-md space-y-4">
        <h1 class="text-xl font-bold">Instalar Dashboard</h1>
        <?php if ($error): ?>
            <div class="bg-red-600 px-3 py-2 rounded text-sm"> <?php echo e($error); ?> </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-emerald-600 px-3 py-2 rounded text-sm"> <?php echo e($success); ?> </div>
        <?php endif; ?>
        <?php if (!$installed): ?>
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
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="seed" class="mr-2"> Criar 10 dias de dados fictícios
                </label>
                <button class="w-full bg-white text-black py-2 rounded font-semibold" type="submit">Instalar</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
