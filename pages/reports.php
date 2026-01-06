<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

require_login();

$user = current_user();
$settings = get_settings($user['id']);

$pdo = db();
$scoreStmt = $pdo->prepare('SELECT log_date, score FROM daily_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) ORDER BY log_date');
$scoreStmt->execute([$user['id']]);
$scores = $scoreStmt->fetchAll();

$consistencyStmt = $pdo->prepare('SELECT * FROM daily_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY log_date');
$consistencyStmt->execute([$user['id']]);
$consistencyLogs = $consistencyStmt->fetchAll();

$totalDays = max(count($consistencyLogs), 1);
$consistency = [
    'Água' => 0,
    'Leitura' => 0,
    'Treino' => 0,
    'Inglês' => 0,
    'Mercado' => 0,
    'Meal prep' => 0,
];

foreach ($consistencyLogs as $log) {
    if ((int)$log['water_cups'] >= (int)$settings['water_goal_cups']) {
        $consistency['Água']++;
    }
    if ((int)$log['reading_minutes'] >= (int)$settings['reading_goal_minutes']) {
        $consistency['Leitura']++;
    }
    if ((int)$log['workout_done'] === 1) {
        $consistency['Treino']++;
    }
    if ((int)$log['english_minutes'] >= (int)$settings['english_goal_minutes']) {
        $consistency['Inglês']++;
    }
    if ((int)$log['market_done'] === 1) {
        $consistency['Mercado']++;
    }
    if ((int)$log['meal_prep_done'] === 1) {
        $consistency['Meal prep']++;
    }
}

$nicotineStmt = $pdo->prepare('SELECT log_date, nicotine_puffs FROM daily_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY log_date');
$nicotineStmt->execute([$user['id']]);
$nicotineLogs = $nicotineStmt->fetchAll();

$streakStmt = $pdo->prepare('SELECT * FROM daily_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) ORDER BY log_date DESC');
$streakStmt->execute([$user['id']]);
$streakLogs = $streakStmt->fetchAll();

$workoutStreaks = compute_streaks($streakLogs, 'workout_done');
$englishStreaks = compute_streaks($streakLogs, 'english_done');

render_header('Relatórios');
?>
<div class="grid gap-6 lg:grid-cols-2">
    <div class="bg-zinc-900 p-6 rounded">
        <h2 class="text-lg font-semibold mb-4">Score semanal</h2>
        <canvas id="scoreChart"></canvas>
    </div>
    <div class="bg-zinc-900 p-6 rounded">
        <h2 class="text-lg font-semibold mb-4">Consistência (30 dias)</h2>
        <canvas id="consistencyChart"></canvas>
    </div>
    <div class="bg-zinc-900 p-6 rounded">
        <h2 class="text-lg font-semibold mb-4">Nicotina (30 dias)</h2>
        <canvas id="nicotineChart"></canvas>
    </div>
    <div class="bg-zinc-900 p-6 rounded space-y-4">
        <h2 class="text-lg font-semibold">Streaks</h2>
        <div>
            <div class="text-sm text-gray-400">Treino</div>
            <div class="text-2xl font-bold">Atual: <?php echo e((string)$workoutStreaks['current']); ?> | Melhor: <?php echo e((string)$workoutStreaks['best']); ?></div>
        </div>
        <div>
            <div class="text-sm text-gray-400">Inglês</div>
            <div class="text-2xl font-bold">Atual: <?php echo e((string)$englishStreaks['current']); ?> | Melhor: <?php echo e((string)$englishStreaks['best']); ?></div>
        </div>
    </div>
</div>

<script>
const scoreLabels = <?php echo json_encode(array_column($scores, 'log_date')); ?>;
const scoreData = <?php echo json_encode(array_map('intval', array_column($scores, 'score'))); ?>;
const consistencyLabels = <?php echo json_encode(array_keys($consistency)); ?>;
const consistencyData = <?php echo json_encode(array_map(fn($value) => round(($value / $totalDays) * 100), $consistency)); ?>;
const nicotineLabels = <?php echo json_encode(array_column($nicotineLogs, 'log_date')); ?>;
const nicotineData = <?php echo json_encode(array_map('intval', array_column($nicotineLogs, 'nicotine_puffs'))); ?>;

new Chart(document.getElementById('scoreChart'), {
    type: 'line',
    data: {
        labels: scoreLabels,
        datasets: [{
            label: 'Score',
            data: scoreData,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16,185,129,0.2)'
        }]
    },
    options: {responsive: true}
});

new Chart(document.getElementById('consistencyChart'), {
    type: 'bar',
    data: {
        labels: consistencyLabels,
        datasets: [{
            label: '% Consistência',
            data: consistencyData,
            backgroundColor: '#F59E0B'
        }]
    },
    options: {responsive: true, scales: {y: {beginAtZero: true, max: 100}}}
});

new Chart(document.getElementById('nicotineChart'), {
    type: 'line',
    data: {
        labels: nicotineLabels,
        datasets: [{
            label: 'Tragos',
            data: nicotineData,
            borderColor: '#EF4444',
            backgroundColor: 'rgba(239,68,68,0.2)'
        }]
    },
    options: {responsive: true}
});
</script>
<?php render_footer(); ?>
