<?php
class BudgetsController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $month = $_GET['month'] ?? date('Y-m');
        $budgets = Budget::all($user['id'], $month);
        view('budgets/index', [
            'title' => 'Limites de gastos',
            'budgets' => $budgets,
            'month' => $month,
            'categories' => Category::all($user['id']),
        ]);
    }

    public function store(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $month = $_POST['month'] ?? date('Y-m');
        $categoryId = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
        $limit = (float)($_POST['limit_amount'] ?? 0);
        if ($limit <= 0) {
            flash('error', 'Informe um limite válido.');
            redirect('budgets?month=' . $month);
        }
        Budget::upsert($user['id'], $categoryId, $month, $limit);
        flash('success', 'Limite salvo.');
        redirect('budgets?month=' . $month);
    }
}
