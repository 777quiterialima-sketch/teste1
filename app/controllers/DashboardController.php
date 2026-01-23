<?php
class DashboardController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $month = date('Y-m');

        $income = Transaction::sumByTypeForMonth($user['id'], 'receita', $month);
        $expense = Transaction::sumByTypeForMonth($user['id'], 'despesa', $month);
        $accounts = Account::all($user['id']);
        $accountsWithBalance = [];
        foreach ($accounts as $account) {
            $accountsWithBalance[] = [
                'id' => $account['id'],
                'name' => $account['name'],
                'balance' => Account::balance($user['id'], (int)$account['id']),
                'color' => $account['color'],
            ];
        }

        $cards = Card::all($user['id']);
        $cardsWithInvoice = [];
        foreach ($cards as $card) {
            $invoiceTotal = Card::invoiceTotal($user['id'], (int)$card['id'], $month);
            $cardsWithInvoice[] = [
                'id' => $card['id'],
                'name' => $card['name'],
                'brand' => $card['brand'],
                'limit_total' => $card['limit_total'],
                'invoice_total' => $invoiceTotal,
                'available' => $card['limit_total'] - $invoiceTotal,
            ];
        }

        $budgets = Budget::all($user['id'], $month);
        $budgetUsage = [];
        foreach ($budgets as $budget) {
            $categoryId = $budget['category_id'];
            $stmt = db()->prepare('SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE user_id = ? AND type = "despesa" AND category_id = ? AND DATE_FORMAT(date, "%Y-%m") = ?');
            $stmt->execute([$user['id'], $categoryId, $month]);
            $spent = (float)$stmt->fetch()['total'];
            $budgetUsage[] = [
                'category_name' => $budget['category_name'] ?? 'Total',
                'limit_amount' => $budget['limit_amount'],
                'spent' => $spent,
                'percent' => $budget['limit_amount'] > 0 ? min(100, ($spent / $budget['limit_amount']) * 100) : 0,
            ];
        }

        view('dashboard/index', [
            'title' => 'Visão Geral',
            'user' => $user,
            'income' => $income,
            'expense' => $expense,
            'totalBalance' => Account::totalBalance($user['id']),
            'accounts' => $accountsWithBalance,
            'cards' => $cardsWithInvoice,
            'budgetUsage' => $budgetUsage,
        ]);
    }
}
