<?php
declare(strict_types=1);

function compute_score(array $log, array $settings): int
{
    $water = min(($log['water_cups'] ?? 0) / max((int)$settings['water_goal_cups'], 1), 1) * 20;
    $reading = min(($log['reading_minutes'] ?? 0) / max((int)$settings['reading_goal_minutes'], 1), 1) * 15;

    $workoutMinutes = (int)($log['workout_minutes'] ?? 0);
    $workoutDone = (int)($log['workout_done'] ?? 0);
    $workoutScore = 0;
    if ($workoutDone === 1) {
        $workoutScore = min($workoutMinutes / 30, 1) * 25;
    }

    $englishMinutes = (int)($log['english_minutes'] ?? 0);
    $englishScore = min($englishMinutes / max((int)$settings['english_goal_minutes'], 1), 1) * 20;

    $market = ((int)($log['market_done'] ?? 0) === 1) ? 10 : 0;
    $mealPrep = ((int)($log['meal_prep_done'] ?? 0) === 1) ? 10 : 0;

    $nicotine = min((int)($log['nicotine_puffs'] ?? 0) * 2, 20);

    $score = (int)round($water + $reading + $workoutScore + $englishScore + $market + $mealPrep - $nicotine);
    return max(0, min(100, $score));
}

function compute_status(int $score): string
{
    if ($score >= 80) {
        return 'VERDE';
    }
    if ($score >= 50) {
        return 'AMARELO';
    }
    return 'VERMELHO';
}
