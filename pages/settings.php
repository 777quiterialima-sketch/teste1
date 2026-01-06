<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

require_login();

$user = current_user();
$settings = get_settings($user['id']);
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'water_goal_cups' => normalize_int($_POST['water_goal_cups'] ?? 8, 1, 20),
        'reading_goal_minutes' => normalize_int($_POST['reading_goal_minutes'] ?? 30, 1, 300),
        'workout_goal_days_week' => normalize_int($_POST['workout_goal_days_week'] ?? 3, 1, 7),
        'english_goal_minutes' => normalize_int($_POST['english_goal_minutes'] ?? 15, 1, 180),
        'nicotine_weekly_reduction_target' => normalize_int($_POST['nicotine_weekly_reduction_target'] ?? 5, 1, 50),
        'work_start_1' => $_POST['work_start_1'] ?? '11:00',
        'work_end_1' => $_POST['work_end_1'] ?? '15:00',
        'work_start_2' => $_POST['work_start_2'] ?? '19:00',
        'work_end_2' => $_POST['work_end_2'] ?? '22:00',
        'high_energy_start' => $_POST['high_energy_start'] ?? '06:00',
        'high_energy_end' => $_POST['high_energy_end'] ?? '10:00',
        'sergeant_tone' => $_POST['sergeant_tone'] ?? 'Firme',
    ];

    $stmt = db()->prepare('UPDATE settings SET water_goal_cups = ?, reading_goal_minutes = ?, workout_goal_days_week = ?, english_goal_minutes = ?, nicotine_weekly_reduction_target = ?, work_start_1 = ?, work_end_1 = ?, work_start_2 = ?, work_end_2 = ?, high_energy_start = ?, high_energy_end = ?, sergeant_tone = ?, updated_at = NOW() WHERE user_id = ?');
    $stmt->execute([
        $data['water_goal_cups'],
        $data['reading_goal_minutes'],
        $data['workout_goal_days_week'],
        $data['english_goal_minutes'],
        $data['nicotine_weekly_reduction_target'],
        $data['work_start_1'],
        $data['work_end_1'],
        $data['work_start_2'],
        $data['work_end_2'],
        $data['high_energy_start'],
        $data['high_energy_end'],
        $data['sergeant_tone'],
        $user['id'],
    ]);

    $settings = get_settings($user['id']);
    $success = 'Configurações salvas.';
}

render_header('Configurações');
?>
<div class="bg-zinc-900 p-6 rounded space-y-4">
    <h2 class="text-lg font-semibold">Metas</h2>
    <?php if ($success): ?>
        <div class="bg-emerald-600 px-3 py-2 rounded text-sm"> <?php echo e($success); ?> </div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-1">
                <span class="text-sm">Meta água (copos)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" name="water_goal_cups" min="1" max="20" value="<?php echo e((string)$settings['water_goal_cups']); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Meta leitura (min)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" name="reading_goal_minutes" min="1" max="300" value="<?php echo e((string)$settings['reading_goal_minutes']); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Meta treino (dias/semana)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" name="workout_goal_days_week" min="1" max="7" value="<?php echo e((string)$settings['workout_goal_days_week']); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Meta inglês (min)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" name="english_goal_minutes" min="1" max="180" value="<?php echo e((string)$settings['english_goal_minutes']); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Redução nicotina/semana</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" name="nicotine_weekly_reduction_target" min="1" max="50" value="<?php echo e((string)$settings['nicotine_weekly_reduction_target']); ?>">
            </label>
        </div>
        <h2 class="text-lg font-semibold">Rotina</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-1">
                <span class="text-sm">Trabalho 1 (início)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="time" name="work_start_1" value="<?php echo e(substr($settings['work_start_1'], 0, 5)); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Trabalho 1 (fim)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="time" name="work_end_1" value="<?php echo e(substr($settings['work_end_1'], 0, 5)); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Trabalho 2 (início)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="time" name="work_start_2" value="<?php echo e(substr($settings['work_start_2'], 0, 5)); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Trabalho 2 (fim)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="time" name="work_end_2" value="<?php echo e(substr($settings['work_end_2'], 0, 5)); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Alta energia (início)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="time" name="high_energy_start" value="<?php echo e(substr($settings['high_energy_start'], 0, 5)); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Alta energia (fim)</span>
                <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="time" name="high_energy_end" value="<?php echo e(substr($settings['high_energy_end'], 0, 5)); ?>">
            </label>
            <label class="space-y-1">
                <span class="text-sm">Tom do Sargento</span>
                <select class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" name="sergeant_tone">
                    <option value="Firme" <?php echo $settings['sergeant_tone'] === 'Firme' ? 'selected' : ''; ?>>Firme</option>
                    <option value="Normal" <?php echo $settings['sergeant_tone'] === 'Normal' ? 'selected' : ''; ?>>Normal</option>
                </select>
            </label>
        </div>
        <button class="bg-white text-black px-4 py-2 rounded font-semibold" type="submit">Salvar configurações</button>
    </form>
</div>
<?php render_footer(); ?>
