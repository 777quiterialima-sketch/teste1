<?php

class CardController
{
    public function index(): void
    {
        require_auth();
        $cards = Card::allByUser(current_user()['id']);
        view('cards/index', ['cards' => $cards]);
    }

    public function create(): void
    {
        require_auth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            Card::create([
                'user_id' => current_user()['id'],
                'name' => trim($_POST['name'] ?? ''),
                'brand' => trim($_POST['brand'] ?? ''),
                'limit_total' => (float)($_POST['limit_total'] ?? 0),
                'close_day' => (int)($_POST['close_day'] ?? 5),
                'due_day' => (int)($_POST['due_day'] ?? 15),
            ]);
            flash('success', 'Cartão criado.');
            redirect('/cards');
        }
        view('cards/form', ['card' => null]);
    }

    public function edit(): void
    {
        require_auth();
        $user = current_user();
        $card = Card::find((int)($_GET['id'] ?? 0), $user['id']);
        if (!$card) {
            flash('error', 'Cartão não encontrado.');
            redirect('/cards');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            Card::update((int)$card['id'], $user['id'], [
                'name' => trim($_POST['name'] ?? ''),
                'brand' => trim($_POST['brand'] ?? ''),
                'limit_total' => (float)($_POST['limit_total'] ?? 0),
                'close_day' => (int)($_POST['close_day'] ?? 5),
                'due_day' => (int)($_POST['due_day'] ?? 15),
            ]);
            flash('success', 'Cartão atualizado.');
            redirect('/cards');
        }

        view('cards/form', ['card' => $card]);
    }

    public function delete(): void
    {
        require_auth();
        verify_csrf();
        Card::delete((int)($_POST['id'] ?? 0), current_user()['id']);
        flash('success', 'Cartão removido.');
        redirect('/cards');
    }

    public function invoice(string $id): void
    {
        require_auth();
        $user = current_user();
        $card = Card::find((int)$id, $user['id']);
        if (!$card) {
            flash('error', 'Cartão não encontrado.');
            redirect('/cards');
        }
        $month = $_GET['month'] ?? date('Y-m');
        $invoice = CardInvoice::findOrCreate((int)$card['id'], $user['id'], $month);

        $stmt = Database::connection()->prepare('SELECT * FROM transactions WHERE card_id = :card_id AND invoice_month = :month ORDER BY date DESC');
        $stmt->execute(['card_id' => $card['id'], 'month' => $month]);
        $items = $stmt->fetchAll();

        $total = 0;
        foreach ($items as $item) {
            $total += $item['amount'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay') {
            verify_csrf();
            $accountId = (int)($_POST['account_id'] ?? 0);
            if ($accountId) {
                Transaction::create([
                    'user_id' => $user['id'],
                    'type' => 'expense',
                    'date' => date('Y-m-d'),
                    'description' => 'Pagamento fatura ' . $card['name'],
                    'category_id' => null,
                    'amount' => $total,
                    'account_id' => $accountId,
                    'account_dest_id' => null,
                    'payment_method' => 'debito',
                    'card_id' => null,
                    'invoice_month' => null,
                    'tags' => null,
                    'notes' => null,
                ]);
                CardInvoice::markPaid((int)$invoice['id'], $user['id']);
                flash('success', 'Fatura paga com sucesso.');
                redirect('/cards/' . $card['id'] . '/invoice?month=' . $month);
            }
        }

        $accounts = Account::allByUser($user['id']);

        view('cards/invoice', [
            'card' => $card,
            'month' => $month,
            'invoice' => $invoice,
            'items' => $items,
            'total' => $total,
            'accounts' => $accounts,
        ]);
    }
}
