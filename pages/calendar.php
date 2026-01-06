<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

require_login();

$user = current_user();
$settings = get_settings($user['id']);

$month = $_GET['month'] ?? date('Y-m');
$monthDate = date('Y-m-01', strtotime($month . '-01'));
$start = date('Y-m-01', strtotime($monthDate));
$end = date('Y-m-t', strtotime($monthDate));

$stmt = db()->prepare('SELECT log_date, status_color FROM daily_logs WHERE user_id = ? AND log_date BETWEEN ? AND ?');
$stmt->execute([$user['id'], $start, $end]);
$logs = $stmt->fetchAll();
$statusMap = [];
foreach ($logs as $log) {
    $statusMap[$log['log_date']] = $log['status_color'];
}

$firstDayOfWeek = date('N', strtotime($start));
$daysInMonth = (int)date('t', strtotime($start));

render_header('Calendário');
?>
<div class="flex items-center justify-between">
    <a class="text-sm underline" href="<?php echo BASE_URL; ?>/pages/calendar.php?month=<?php echo e(date('Y-m', strtotime($start . ' -1 month'))); ?>">Anterior</a>
    <div class="text-lg font-semibold"><?php echo e(date('F Y', strtotime($start))); ?></div>
    <a class="text-sm underline" href="<?php echo BASE_URL; ?>/pages/calendar.php?month=<?php echo e(date('Y-m', strtotime($start . ' +1 month'))); ?>">Próximo</a>
</div>

<div class="grid grid-cols-7 gap-2 bg-zinc-900 p-4 rounded">
    <?php
    $weekdays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    foreach ($weekdays as $day) {
        echo '<div class="text-xs text-gray-400 uppercase">' . e($day) . '</div>';
    }

    for ($i = 1; $i < $firstDayOfWeek; $i++) {
        echo '<div></div>';
    }

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = date('Y-m-d', strtotime($start . ' +' . ($day - 1) . ' days'));
        $status = $statusMap[$date] ?? 'VERMELHO';
        $color = status_color_label($status);
        echo '<button class="calendar-day bg-black border border-zinc-800 rounded p-2 text-sm hover:border-white" data-date="' . e($date) . '">';
        echo '<div class="text-xs">' . e((string)$day) . '</div>';
        echo '<div class="mt-2 h-2 w-full rounded ' . e($color) . '"></div>';
        echo '</button>';
    }
    ?>
</div>

<div id="dayModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center">
    <div class="bg-zinc-900 p-6 rounded w-full max-w-2xl space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold" id="modalTitle"></h2>
            <button id="closeModal" class="text-sm">Fechar</button>
        </div>
        <form id="modalForm" class="space-y-3" method="post" action="<?php echo BASE_URL; ?>/api/save_log.php">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <input type="hidden" name="log_date" id="modalDate" value="">
            <div class="grid gap-3 md:grid-cols-2">
                <label class="space-y-1">
                    <span class="text-sm">Água (copos)</span>
                    <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" min="0" max="20" name="water_cups" id="water_cups">
                </label>
                <label class="space-y-1">
                    <span class="text-sm">Leitura (min)</span>
                    <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" min="0" max="300" name="reading_minutes" id="reading_minutes">
                </label>
                <label class="space-y-1">
                    <span class="text-sm">Treino</span>
                    <input type="checkbox" class="mr-2" name="workout_done" id="workout_done" value="1">
                    <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded mt-2" type="number" min="0" max="180" name="workout_minutes" id="workout_minutes" placeholder="Minutos">
                </label>
                <label class="space-y-1">
                    <span class="text-sm">Inglês</span>
                    <input type="checkbox" class="mr-2" name="english_done" id="english_done" value="1">
                    <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded mt-2" type="number" min="0" max="180" name="english_minutes" id="english_minutes" placeholder="Minutos">
                </label>
                <label class="space-y-1">
                    <span class="text-sm">Mercado</span>
                    <input type="checkbox" class="mr-2" name="market_done" id="market_done" value="1">
                </label>
                <label class="space-y-1">
                    <span class="text-sm">Meal prep</span>
                    <input type="checkbox" class="mr-2" name="meal_prep_done" id="meal_prep_done" value="1">
                </label>
                <label class="space-y-1">
                    <span class="text-sm">Nicotina</span>
                    <input class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" type="number" min="0" max="200" name="nicotine_puffs" id="nicotine_puffs">
                </label>
            </div>
            <label class="space-y-1 block">
                <span class="text-sm">Notas</span>
                <textarea class="w-full bg-black border border-zinc-700 px-3 py-2 rounded" name="notes" id="notes" rows="3"></textarea>
            </label>
            <div class="flex items-center justify-between">
                <button class="bg-white text-black px-4 py-2 rounded font-semibold" type="submit">Salvar</button>
                <a class="underline text-sm" id="openFullDay" href="#">Abrir dia</a>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('dayModal');
const modalTitle = document.getElementById('modalTitle');
const modalDate = document.getElementById('modalDate');
const openFullDay = document.getElementById('openFullDay');

document.querySelectorAll('.calendar-day').forEach(btn => {
    btn.addEventListener('click', () => {
        const date = btn.dataset.date;
        fetch(`<?php echo BASE_URL; ?>/api/get_log.php?date=${date}`)
            .then(resp => resp.json())
            .then(data => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modalTitle.textContent = new Date(date).toLocaleDateString('pt-BR');
                modalDate.value = date;
                openFullDay.href = `<?php echo BASE_URL; ?>/pages/today.php?date=${date}`;
                document.getElementById('water_cups').value = data.water_cups;
                document.getElementById('reading_minutes').value = data.reading_minutes;
                document.getElementById('workout_done').checked = Number(data.workout_done) === 1;
                document.getElementById('workout_minutes').value = data.workout_minutes;
                document.getElementById('english_done').checked = Number(data.english_done) === 1;
                document.getElementById('english_minutes').value = data.english_minutes;
                document.getElementById('market_done').checked = Number(data.market_done) === 1;
                document.getElementById('meal_prep_done').checked = Number(data.meal_prep_done) === 1;
                document.getElementById('nicotine_puffs').value = data.nicotine_puffs;
                document.getElementById('notes').value = data.notes;
            });
    });
});

const closeModal = document.getElementById('closeModal');
if (closeModal) {
    closeModal.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
}
</script>
<?php render_footer(); ?>
