<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $settings = get_settings($user['id']);
    header('Content-Type: application/json');
    echo json_encode($settings);
    exit;
}

require_post();
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

header('Content-Type: application/json');
echo json_encode(['success' => true]);
