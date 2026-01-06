<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

require_login();
require_post();
verify_csrf();

$user = current_user();
$settings = get_settings($user['id']);

$logDate = $_POST['log_date'] ?? date('Y-m-d');
$logDate = format_date($logDate);

$log = [
    'water_cups' => normalize_int($_POST['water_cups'] ?? 0, 0, 20),
    'reading_minutes' => normalize_int($_POST['reading_minutes'] ?? 0, 0, 300),
    'workout_done' => normalize_bool($_POST['workout_done'] ?? 0),
    'workout_minutes' => normalize_int($_POST['workout_minutes'] ?? 0, 0, 180),
    'english_done' => normalize_bool($_POST['english_done'] ?? 0),
    'english_minutes' => normalize_int($_POST['english_minutes'] ?? 0, 0, 180),
    'market_done' => normalize_bool($_POST['market_done'] ?? 0),
    'meal_prep_done' => normalize_bool($_POST['meal_prep_done'] ?? 0),
    'nicotine_puffs' => normalize_int($_POST['nicotine_puffs'] ?? 0, 0, 200),
    'notes' => trim($_POST['notes'] ?? ''),
];

$score = compute_score($log, $settings);
$status = compute_status($score);

$pdo = db();
$sql = 'INSERT INTO daily_logs (user_id, log_date, water_cups, reading_minutes, workout_done, workout_minutes, english_done, english_minutes, market_done, meal_prep_done, nicotine_puffs, notes, score, status_color, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE water_cups = VALUES(water_cups), reading_minutes = VALUES(reading_minutes), workout_done = VALUES(workout_done), workout_minutes = VALUES(workout_minutes), english_done = VALUES(english_done), english_minutes = VALUES(english_minutes), market_done = VALUES(market_done), meal_prep_done = VALUES(meal_prep_done), nicotine_puffs = VALUES(nicotine_puffs), notes = VALUES(notes), score = VALUES(score), status_color = VALUES(status_color), updated_at = NOW()';

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $user['id'],
    $logDate,
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

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'score' => $score, 'status' => $status]);
    exit;
}

flash('message', 'Check-in salvo.');
redirect('/pages/today.php?date=' . $logDate);
