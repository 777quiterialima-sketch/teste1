<?php

class TransactionController
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
        $transactions = Transaction::listByUser($user['id'], $filters);
        $accounts = Account::allByUser($user['id']);
        $categories = Category::allByUser($user['id']);

        view('transactions/index', compact('transactions', 'accounts', 'categories', 'filters'));
    }

    public function create(): void
    {
        require_auth();
        $user = current_user();
        $accounts = Account::allByUser($user['id']);
        $categories = Category::allByUser($user['id']);
        $cards = Card::allByUser($user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $pdo = Database::connection();
            $pdo->beginTransaction();
            try {
                $type = $_POST['type'] ?? 'expense';
                $date = $_POST['date'] ?? date('Y-m-d');
                $description = trim($_POST['description'] ?? '');
                $categoryId = $_POST['category_id'] ?: null;
                $amount = (float)($_POST['amount'] ?? 0);
                $accountId = $_POST['account_id'] ?: null;
                $accountDestId = $_POST['account_dest_id'] ?: null;
                $paymentMethod = $_POST['payment_method'] ?? 'dinheiro';
                $cardId = $_POST['card_id'] ?: null;
                $installments = (int)($_POST['installments'] ?? 1);
                $tags = trim($_POST['tags'] ?? '');
                $notes = trim($_POST['notes'] ?? '');

                if ($type === 'transfer') {
                    Transaction::create([
                        'user_id' => $user['id'],
                        'type' => $type,
                        'date' => $date,
                        'description' => $description,
                        'category_id' => null,
                        'amount' => $amount,
                        'account_id' => $accountId,
                        'account_dest_id' => $accountDestId,
                        'payment_method' => 'transferencia',
                        'card_id' => null,
                        'invoice_month' => null,
                        'tags' => $tags,
                        'notes' => $notes,
                    ]);
                } else {
                    for ($i = 0; $i < $installments; $i++) {
                        $installmentDate = date('Y-m-d', strtotime("+$i month", strtotime($date)));
                        $invoiceMonth = null;
                        if ($paymentMethod === 'cartao' && $cardId) {
                            $invoiceMonth = $this->calculateInvoiceMonth($cardId, $installmentDate);
                        }
                        Transaction::create([
                            'user_id' => $user['id'],
                            'type' => $type,
                            'date' => $installmentDate,
                            'description' => $description . ($installments > 1 ? " (" . ($i + 1) . "/$installments)" : ''),
                            'category_id' => $categoryId,
                            'amount' => $amount / max($installments, 1),
                            'account_id' => $accountId,
                            'account_dest_id' => null,
                            'payment_method' => $paymentMethod,
                            'card_id' => $cardId,
                            'invoice_month' => $invoiceMonth,
                            'tags' => $tags,
                            'notes' => $notes,
                        ]);
                    }
                }

                $pdo->commit();
                flash('success', 'Lançamento registrado.');
                redirect('/transactions');
            } catch (Throwable $exception) {
                $pdo->rollBack();
                log_message('Erro ao criar lançamento: ' . $exception->getMessage());
                flash('error', 'Erro ao registrar lançamento.');
            }
        }

        view('transactions/form', ['transaction' => null, 'accounts' => $accounts, 'categories' => $categories, 'cards' => $cards]);
    }

    public function edit(): void
    {
        require_auth();
        $user = current_user();
        $transaction = Transaction::find((int)($_GET['id'] ?? 0), $user['id']);
        if (!$transaction) {
            flash('error', 'Lançamento não encontrado.');
            redirect('/transactions');
        }
        $accounts = Account::allByUser($user['id']);
        $categories = Category::allByUser($user['id']);
        $cards = Card::allByUser($user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $invoiceMonth = null;
            $paymentMethod = $_POST['payment_method'] ?? 'dinheiro';
            $cardId = $_POST['card_id'] ?: null;
            if ($paymentMethod === 'cartao' && $cardId) {
                $invoiceMonth = $this->calculateInvoiceMonth((int)$cardId, $_POST['date'] ?? date('Y-m-d'));
            }
            Transaction::update((int)$transaction['id'], $user['id'], [
                'type' => $_POST['type'] ?? 'expense',
                'date' => $_POST['date'] ?? date('Y-m-d'),
                'description' => trim($_POST['description'] ?? ''),
                'category_id' => $_POST['category_id'] ?: null,
                'amount' => (float)($_POST['amount'] ?? 0),
                'account_id' => $_POST['account_id'] ?: null,
                'account_dest_id' => $_POST['account_dest_id'] ?: null,
                'payment_method' => $paymentMethod,
                'card_id' => $cardId,
                'invoice_month' => $invoiceMonth,
                'tags' => trim($_POST['tags'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
            ]);
            flash('success', 'Lançamento atualizado.');
            redirect('/transactions');
        }

        view('transactions/form', ['transaction' => $transaction, 'accounts' => $accounts, 'categories' => $categories, 'cards' => $cards]);
    }

    public function delete(): void
    {
        require_auth();
        verify_csrf();
        Transaction::delete((int)($_POST['id'] ?? 0), current_user()['id']);
        flash('success', 'Lançamento removido.');
        redirect('/transactions');
    }

    private function calculateInvoiceMonth(int $cardId, string $date): string
    {
        $card = Card::find($cardId, current_user()['id']);
        if (!$card) {
            return date('Y-m', strtotime($date));
        }
        $day = (int)date('d', strtotime($date));
        $month = date('Y-m', strtotime($date));
        if ($day > (int)$card['close_day']) {
            $month = date('Y-m', strtotime('+1 month', strtotime($date)));
        }
        return $month;
    }
}
