<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

require_login();

$user = current_user();
$settings = get_settings($user['id']);

$date = $_GET['date'] ?? date('Y-m-d');
$date = format_date($date);

$stmt = db()->prepare('SELECT * FROM daily_logs WHERE user_id = ? AND log_date = ?');
$stmt->execute([$user['id'], $date]);
$log = $stmt->fetch();

if (!$log) {
    $log = [
        'log_date' => $date,
        'water_cups' => 0,
        'reading_minutes' => 0,
        'workout_done' => 0,
        'workout_minutes' => 0,
        'english_done' => 0,
        'english_minutes' => 0,
        'market_done' => 0,
        'meal_prep_done' => 0,
        'nicotine_puffs' => 0,
        'notes' => '',
        'score' => 0,
    ];
}

$score = compute_score($log, $settings);
$status = compute_status($score);

$last7Stmt = db()->prepare('SELECT * FROM daily_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 7');
$last7Stmt->execute([$user['id']]);
$last7Logs = $last7Stmt->fetchAll();

$sergeant = sergeantMessage($log + ['score' => $score], $last7Logs, $settings);
$nextAction = nextAction($log, $settings, $last7Logs);

render_header('Hoje');
?>
<div class="grid gap-6 lg:grid-cols-3">
    <div class="bg-zinc-900 p-6 rounded space-y-3">
        <div class="text-sm text-gray-400">Data</div>
        <div class="text-xl font-semibold"><?php echo e(date('d/m/Y', strtotime($date))); ?></div>
        <div class="mt-4">
            <div class="text-sm uppercase text-gray-400">Score do dia</div>
            <div class="text-3xl font-bold"><?php echo e((string)$score); ?>/100</div>
            <div class="mt-2 inline-flex items-center px-3 py-1 rounded text-xs <?php echo e(status_color_label($status)); ?>">
                <?php echo e($status); ?>
            </div>
        </div>
    </div>
    <div class="bg-zinc-900 p-6 rounded">
        <div class="text-sm uppercase text-gray-400 mb-2">Sargento</div>
        <div class="text-lg font-semibold"><?php echo e($sergeant); ?></div>
    </div>
    <div class="bg-zinc-900 p-6 rounded">
        <div class="text-sm uppercase text-gray-400 mb-2">Próxima ação agora</div>
        <div class="text-lg font-semibold"><?php echo e($nextAction); ?></div>
    </div>
</div>

<form class="bg-zinc-900 p-6 rounded space-y-4" method="post" action="<?php echo BASE_URL; ?>/api/save_log.php">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
    <input type="hidden" name="log_date" value="<?php echo e($date); ?>">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="space-y-1">
            <span class="text-sm">Água (copos)</span>
            <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" min="0" max="20" name="water_cups" value="<?php echo e((string)$log['water_cups']); ?>">
        </label>
        <label class="space-y-1">
            <span class="text-sm">Leitura (min)</span>
            <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" min="0" max="300" name="reading_minutes" value="<?php echo e((string)$log['reading_minutes']); ?>">
        </label>
        <label class="space-y-1">
            <span class="text-sm">Treino feito?</span>
            <input type="checkbox" class="mr-2" name="workout_done" value="1" <?php echo (int)$log['workout_done'] === 1 ? 'checked' : ''; ?>>
            <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded mt-2" type="number" min="0" max="180" name="workout_minutes" value="<?php echo e((string)$log['workout_minutes']); ?>" placeholder="Minutos">
        </label>
        <label class="space-y-1">
            <span class="text-sm">Inglês feito?</span>
            <input type="checkbox" class="mr-2" name="english_done" value="1" <?php echo (int)$log['english_done'] === 1 ? 'checked' : ''; ?>>
            <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded mt-2" type="number" min="0" max="180" name="english_minutes" value="<?php echo e((string)$log['english_minutes']); ?>" placeholder="Minutos">
        </label>
        <label class="space-y-1">
            <span class="text-sm">Mercado</span>
            <input type="checkbox" class="mr-2" name="market_done" value="1" <?php echo (int)$log['market_done'] === 1 ? 'checked' : ''; ?>>
        </label>
        <label class="space-y-1">
            <span class="text-sm">Meal prep</span>
            <input type="checkbox" class="mr-2" name="meal_prep_done" value="1" <?php echo (int)$log['meal_prep_done'] === 1 ? 'checked' : ''; ?>>
        </label>
        <label class="space-y-1">
            <span class="text-sm">Nicotina (tragos/cigarros)</span>
            <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" min="0" max="200" name="nicotine_puffs" value="<?php echo e((string)$log['nicotine_puffs']); ?>">
        </label>
    </div>
    <label class="space-y-1 block">
        <span class="text-sm">Notas</span>
        <textarea class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" name="notes" rows="3"><?php echo e((string)$log['notes']); ?></textarea>
    </label>
    <button class="bg-white text-black px-4 py-2 rounded font-semibold" type="submit">Salvar check-in</button>
</form>

<script>
const form = document.querySelector('form');
if (form) {
    let timeout;
    form.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF': data.get('csrf_token'),
                    'X-Requested-With': 'fetch'
                },
                body: data,
            }).then(() => {}).catch(() => {});
        }, 1200);
    });
}
</script>
<?php render_footer(); ?>
