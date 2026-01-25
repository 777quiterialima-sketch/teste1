<?php

class BudgetController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $month = $_GET['month'] ?? date('Y-m');
        $categories = Category::allByUser($user['id']);
        $budgets = Budget::listByUser($user['id'], $month);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $totalLimit = (float)($_POST['total_limit'] ?? 0);
            Budget::upsert($user['id'], null, $month, $totalLimit);

            foreach ($categories as $category) {
                $value = $_POST['category_limit'][$category['id']] ?? null;
                if ($value !== null && $value !== '') {
                    Budget::upsert($user['id'], (int)$category['id'], $month, (float)$value);
                }
            }
            flash('success', 'Limites atualizados.');
            redirect('/budgets?month=' . $month);
        }

        view('budgets/index', compact('month', 'categories', 'budgets'));
    }
}
