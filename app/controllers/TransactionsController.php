<?php
class TransactionsController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $filters = [
            'type' => $_GET['type'] ?? null,
            'account_id' => $_GET['account_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'search' => $_GET['search'] ?? null,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $total = Transaction::count($user['id'], $filters);
        $transactions = Transaction::list($user['id'], $filters, $perPage, $offset);
        view('transactions/index', [
            'title' => 'Lançamentos',
            'transactions' => $transactions,
            'accounts' => Account::all($user['id']),
            'categories' => Category::all($user['id']),
            'filters' => $filters,
            'cards' => Card::all($user['id']),
            'page' => $page,
            'pages' => (int)ceil($total / $perPage),
        ]);
    }

    public function create(): void
    {
        require_auth();
        $user = current_user();
        view('transactions/form', [
            'title' => 'Novo lançamento',
            'transaction' => null,
            'accounts' => Account::all($user['id']),
            'categories' => Category::all($user['id']),
            'cards' => Card::all($user['id']),
        ]);
    }

    public function store(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $type = $_POST['type'] ?? 'despesa';
        $installments = max(1, (int)($_POST['installments'] ?? 1));
        $data = [
            'type' => $type,
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'description' => trim($_POST['description'] ?? ''),
            'category_id' => $_POST['category_id'] ?: null,
            'amount' => (float)($_POST['amount'] ?? 0),
            'account_id' => $_POST['account_id'] ?: null,
            'account_dest_id' => $_POST['account_dest_id'] ?: null,
            'payment_method' => $_POST['payment_method'] ?? 'dinheiro',
            'card_id' => $_POST['card_id'] ?: null,
            'tags' => trim($_POST['tags'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'installment_group' => null,
            'installment_number' => null,
            'installment_total' => null,
        ];

        if ($type === 'transferencia') {
            $this->storeTransfer($user['id'], $data);
            flash('success', 'Transferência registrada.');
            redirect('transactions');
        }

        if ($type === 'despesa' && $data['payment_method'] === 'cartao' && $installments > 1) {
            $groupId = bin2hex(random_bytes(6));
            for ($i = 1; $i <= $installments; $i++) {
                $installmentDate = date('Y-m-d', strtotime('+' . ($i - 1) . ' months', strtotime($data['date'])));
                $data['date'] = $installmentDate;
                $data['installment_group'] = $groupId;
                $data['installment_number'] = $i;
                $data['installment_total'] = $installments;
                Transaction::create($user['id'], $data);
            }
        } else {
            Transaction::create($user['id'], $data);
        }

        flash('success', 'Lançamento criado com sucesso.');
        redirect('transactions');
    }

    public function edit(): void
    {
        require_auth();
        $user = current_user();
        $id = (int)($_GET['id'] ?? 0);
        $transaction = Transaction::find($user['id'], $id);
        if (!$transaction) {
            flash('error', 'Lançamento não encontrado.');
            redirect('transactions');
        }
        view('transactions/form', [
            'title' => 'Editar lançamento',
            'transaction' => $transaction,
            'accounts' => Account::all($user['id']),
            'categories' => Category::all($user['id']),
            'cards' => Card::all($user['id']),
        ]);
    }

    public function update(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'type' => $_POST['type'] ?? 'despesa',
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'description' => trim($_POST['description'] ?? ''),
            'category_id' => $_POST['category_id'] ?: null,
            'amount' => (float)($_POST['amount'] ?? 0),
            'account_id' => $_POST['account_id'] ?: null,
            'account_dest_id' => $_POST['account_dest_id'] ?: null,
            'payment_method' => $_POST['payment_method'] ?? 'dinheiro',
            'card_id' => $_POST['card_id'] ?: null,
            'tags' => trim($_POST['tags'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
        Transaction::update($user['id'], $id, $data);
        flash('success', 'Lançamento atualizado.');
        redirect('transactions');
    }

    public function delete(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);
        Transaction::delete($user['id'], $id);
        flash('success', 'Lançamento removido.');
        redirect('transactions');
    }

    private function storeTransfer(int $userId, array $data): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            Transaction::create($userId, $data);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            log_message('Erro ao criar transferência: ' . $e->getMessage());
            throw $e;
        }
    }
}
