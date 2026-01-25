<?php

class DashboardController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $month = date('Y-m');
        $totals = Transaction::monthlyTotals($user['id'], $month);
        $accounts = Account::allByUser($user['id']);
        $accountsWithBalance = [];
        foreach ($accounts as $account) {
            $account['balance'] = Account::balance((int)$account['id']);
            $accountsWithBalance[] = $account;
        }

        $cards = Card::allByUser($user['id']);
        $cardsSummary = [];
        foreach ($cards as $card) {
            $spent = Card::currentInvoiceAmount((int)$card['id'], $month);
            $cardsSummary[] = [
                'card' => $card,
                'spent' => $spent,
                'available' => $card['limit_total'] - $spent,
            ];
        }

        $budgetList = Budget::listByUser($user['id'], $month);
        $expensesByCategory = Transaction::expensesByCategory($user['id'], $month);

        view('dashboard/index', [
            'user' => $user,
            'month' => $month,
            'totals' => $totals,
            'accounts' => $accountsWithBalance,
            'cards' => $cardsSummary,
            'totalBalance' => Account::totalBalanceByUser($user['id']),
            'budgetList' => $budgetList,
            'expensesByCategory' => $expensesByCategory,
        ]);
    }
}
