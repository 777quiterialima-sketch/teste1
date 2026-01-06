<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

require_login();

$user = current_user();
$from = format_date($_GET['from'] ?? date('Y-m-01'));
$to = format_date($_GET['to'] ?? date('Y-m-t'));

$stmt = db()->prepare('SELECT * FROM daily_logs WHERE user_id = ? AND log_date BETWEEN ? AND ? ORDER BY log_date');
$stmt->execute([$user['id'], $from, $to]);
$logs = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($logs);
