<?php
declare(strict_types=1);

function sergeantMessage(array $todayLog, array $last7Logs, array $settings): string
{
    $score = (int)($todayLog['score'] ?? 0);
    $status = compute_status($score);
    $tone = $settings['sergeant_tone'] ?? 'Firme';

    $missing = missing_targets($todayLog, $settings);
    $reinforcements = reinforcement_rules($last7Logs, $settings);

    if ($status === 'VERDE') {
        $base = $tone === 'Firme'
            ? 'Bom trabalho. Missão verde cumprida.'
            : 'Boa disciplina hoje.';
        $next = $reinforcements['priority'] ?? 'Prepare o próximo dia antes das 10h.';
        return $base . ' Próxima missão: ' . $next;
    }

    if ($status === 'AMARELO') {
        $base = $tone === 'Firme'
            ? 'Status amarelo. Corrija agora.'
            : 'Ainda dá para fechar o dia.';
        $focus = $missing[0] ?? ($reinforcements['priority'] ?? 'Ajuste sua rotina.');
        return $base . ' Foque em: ' . $focus;
    }

    $base = $tone === 'Firme'
        ? 'Status vermelho. Disciplina em falta.'
        : 'Dia crítico. Hora de agir.';
    $step1 = $missing[0] ?? 'Resolver o básico agora.';
    $step2 = $missing[1] ?? ($reinforcements['priority'] ?? 'Planejar o próximo bloco.');
    return $base . ' Plano de ataque: 1) ' . $step1 . ' 2) ' . $step2 . '.';
}

function nextAction(array $todayLog, array $settings, array $last7Logs): string
{
    $missing = missing_targets($todayLog, $settings);
    $reinforcements = reinforcement_rules($last7Logs, $settings);

    return $missing[0] ?? ($reinforcements['action'] ?? 'Revise a lista e execute uma ação de 10 minutos.');
}

function missing_targets(array $todayLog, array $settings): array
{
    $targets = [];
    if ((int)$todayLog['water_cups'] < (int)$settings['water_goal_cups']) {
        $targets[] = 'Beber 2 copos de água agora.';
    }
    if ((int)$todayLog['reading_minutes'] < (int)$settings['reading_goal_minutes']) {
        $targets[] = 'Ler 10 minutos.';
    }
    if ((int)$todayLog['workout_done'] !== 1) {
        $targets[] = 'Treino rápido de 15–20 min.';
    }
    if ((int)$todayLog['english_minutes'] < (int)$settings['english_goal_minutes']) {
        $targets[] = 'Inglês por 10 minutos.';
    }
    if ((int)$todayLog['market_done'] !== 1) {
        $targets[] = 'Resolver compras essenciais.';
    }
    if ((int)$todayLog['meal_prep_done'] !== 1) {
        $targets[] = 'Separar comida/meal prep.';
    }

    return $targets;
}

function reinforcement_rules(array $last7Logs, array $settings): array
{
    $workoutMisses = 0;
    $englishMisses = 0;
    $nicotineValues = [];

    foreach ($last7Logs as $log) {
        if ((int)$log['workout_done'] !== 1) {
            $workoutMisses++;
        }
        if ((int)$log['english_done'] !== 1) {
            $englishMisses++;
        }
        $nicotineValues[] = (int)$log['nicotine_puffs'];
    }

    $averageNicotine = count($nicotineValues) ? array_sum($nicotineValues) / count($nicotineValues) : 0;
    $latestNicotine = $last7Logs[0]['nicotine_puffs'] ?? 0;

    $priority = null;
    $action = null;

    if ($workoutMisses >= 3) {
        $priority = 'Priorizar treino hoje.';
        $action = 'Faça um treino curto de 15 minutos.';
    }

    if ($englishMisses >= 2) {
        $priority = $priority ?? 'Inglês falhou 2+ dias. Faça 10 min.';
        $action = $action ?? 'Abra um áudio curto de inglês.';
    }

    if ($latestNicotine > $averageNicotine) {
        $reduce = (int)max((int)$settings['nicotine_weekly_reduction_target'], 1);
        $priority = $priority ?? 'Reduzir nicotina em ' . $reduce . ' na semana.';
        $action = $action ?? 'Substitua 1 trago por água agora.';
    }

    $energyStart = $settings['high_energy_start'] ?? '06:00:00';
    if (strtotime($energyStart) <= strtotime('10:00:00')) {
        $priority = $priority ?? 'Foco pesado antes das 10h.';
    }

    return ['priority' => $priority, 'action' => $action];
}

function compute_streaks(array $logs, string $key): array
{
    $current = 0;
    $best = 0;
    $temp = 0;

    foreach ($logs as $log) {
        if ((int)$log[$key] === 1) {
            $temp++;
            $best = max($best, $temp);
        } else {
            $temp = 0;
        }
    }

    foreach ($logs as $log) {
        if ((int)$log[$key] === 1) {
            $current++;
        } else {
            break;
        }
    }

    return ['current' => $current, 'best' => $best];
}
