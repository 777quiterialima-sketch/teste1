<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo 'CSRF validation failed.';
        exit;
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Method not allowed.';
        exit;
    }
}

function normalize_int($value, int $min, int $max): int
{
    $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => $min]]);
    if ($value < $min) {
        return $min;
    }
    if ($value > $max) {
        return $max;
    }
    return $value;
}

function normalize_bool($value): int
{
    return $value ? 1 : 0;
}

function format_date(string $date): string
{
    return date('Y-m-d', strtotime($date));
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function get_settings(int $userId): array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM settings WHERE user_id = ?');
    $stmt->execute([$userId]);
    $settings = $stmt->fetch();

    if ($settings) {
        return $settings;
    }

    $defaults = [
        'water_goal_cups' => 8,
        'reading_goal_minutes' => 30,
        'workout_goal_days_week' => 3,
        'english_goal_minutes' => 15,
        'nicotine_weekly_reduction_target' => 5,
        'work_start_1' => '11:00:00',
        'work_end_1' => '15:00:00',
        'work_start_2' => '19:00:00',
        'work_end_2' => '22:00:00',
        'high_energy_start' => '06:00:00',
        'high_energy_end' => '10:00:00',
        'sergeant_tone' => 'Firme',
    ];

    $insert = $pdo->prepare('INSERT INTO settings (user_id, water_goal_cups, reading_goal_minutes, workout_goal_days_week, english_goal_minutes, nicotine_weekly_reduction_target, work_start_1, work_end_1, work_start_2, work_end_2, high_energy_start, high_energy_end, sergeant_tone, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $insert->execute([
        $userId,
        $defaults['water_goal_cups'],
        $defaults['reading_goal_minutes'],
        $defaults['workout_goal_days_week'],
        $defaults['english_goal_minutes'],
        $defaults['nicotine_weekly_reduction_target'],
        $defaults['work_start_1'],
        $defaults['work_end_1'],
        $defaults['work_start_2'],
        $defaults['work_end_2'],
        $defaults['high_energy_start'],
        $defaults['high_energy_end'],
        $defaults['sergeant_tone'],
    ]);

    return $defaults;
}

function status_color_label(string $status): string
{
    return match ($status) {
        'VERDE' => 'bg-emerald-500',
        'AMARELO' => 'bg-yellow-500',
        default => 'bg-red-500',
    };
}
