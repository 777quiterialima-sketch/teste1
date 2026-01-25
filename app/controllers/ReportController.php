<?php

class ReportController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $month = $_GET['month'] ?? date('Y-m');
        $expensesByCategory = Transaction::expensesByCategory($user['id'], $month);
        $incomeExpense = Transaction::incomeExpenseByMonth($user['id'], 6);

        view('reports/index', [
            'month' => $month,
            'expensesByCategory' => $expensesByCategory,
            'incomeExpense' => $incomeExpense,
        ]);
    }
}
