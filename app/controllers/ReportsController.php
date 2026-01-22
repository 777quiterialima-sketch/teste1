<?php
class ReportsController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $start = $_GET['start_date'] ?? date('Y-m-01');
        $end = $_GET['end_date'] ?? date('Y-m-t');
        $categoryTotals = Transaction::totalByCategory($user['id'], $start, $end);
        $monthlyTotals = Transaction::totalsLastMonths($user['id'], 6);

        view('reports/index', [
            'title' => 'Relatórios',
            'categoryTotals' => $categoryTotals,
            'monthlyTotals' => $monthlyTotals,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
