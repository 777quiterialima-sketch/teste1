<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

require_login();

$user = current_user();
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
    ];
}

header('Content-Type: application/json');
echo json_encode($log);
