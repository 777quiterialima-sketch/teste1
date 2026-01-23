<?php
class CardsController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $cards = Card::all($user['id']);
        view('cards/index', [
            'title' => 'Cartões',
            'cards' => $cards,
        ]);
    }

    public function create(): void
    {
        require_auth();
        view('cards/form', [
            'title' => 'Novo cartão',
            'card' => null,
        ]);
    }

    public function store(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'limit_total' => (float)($_POST['limit_total'] ?? 0),
            'closing_day' => (int)($_POST['closing_day'] ?? 1),
            'due_day' => (int)($_POST['due_day'] ?? 10),
        ];
        Card::create($user['id'], $data);
        flash('success', 'Cartão criado com sucesso.');
        redirect('cards');
    }

    public function edit(): void
    {
        require_auth();
        $user = current_user();
        $id = (int)($_GET['id'] ?? 0);
        $card = Card::find($user['id'], $id);
        if (!$card) {
            flash('error', 'Cartão não encontrado.');
            redirect('cards');
        }
        view('cards/form', [
            'title' => 'Editar cartão',
            'card' => $card,
        ]);
    }

    public function update(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'brand' => trim($_POST['brand'] ?? ''),
            'limit_total' => (float)($_POST['limit_total'] ?? 0),
            'closing_day' => (int)($_POST['closing_day'] ?? 1),
            'due_day' => (int)($_POST['due_day'] ?? 10),
        ];
        Card::update($user['id'], $id, $data);
        flash('success', 'Cartão atualizado.');
        redirect('cards');
    }

    public function delete(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);
        Card::delete($user['id'], $id);
        flash('success', 'Cartão removido.');
        redirect('cards');
    }

    public function invoice(): void
    {
        require_auth();
        $user = current_user();
        $cardId = (int)($_GET['id'] ?? 0);
        $month = $_GET['month'] ?? date('Y-m');
        $card = Card::find($user['id'], $cardId);
        if (!$card) {
            flash('error', 'Cartão não encontrado.');
            redirect('cards');
        }
        $stmt = db()->prepare('SELECT * FROM transactions WHERE user_id = ? AND card_id = ? AND DATE_FORMAT(date, "%Y-%m") = ? ORDER BY date DESC');
        $stmt->execute([$user['id'], $cardId, $month]);
        $items = $stmt->fetchAll();
        $total = Card::invoiceTotal($user['id'], $cardId, $month);

        $invoice = $this->getOrCreateInvoice($user['id'], $cardId, $month, $total);

        view('cards/invoice', [
            'title' => 'Fatura do cartão',
            'card' => $card,
            'month' => $month,
            'items' => $items,
            'total' => $total,
            'invoice' => $invoice,
            'accounts' => Account::all($user['id']),
        ]);
    }

    public function payInvoice(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $cardId = (int)($_POST['card_id'] ?? 0);
        $month = $_POST['month'] ?? date('Y-m');
        $accountId = (int)($_POST['account_id'] ?? 0);
        $card = Card::find($user['id'], $cardId);
        if (!$card) {
            flash('error', 'Cartão não encontrado.');
            redirect('cards');
        }
        $total = Card::invoiceTotal($user['id'], $cardId, $month);
        $invoice = $this->getOrCreateInvoice($user['id'], $cardId, $month, $total);
        if ($invoice['status'] === 'paga') {
            flash('error', 'Fatura já está paga.');
            redirect('cards/' . $cardId . '/invoice?month=' . $month);
        }
        $data = [
            'type' => 'despesa',
            'date' => date('Y-m-d'),
            'description' => 'Pagamento fatura ' . $card['name'],
            'category_id' => null,
            'amount' => $total,
            'account_id' => $accountId,
            'account_dest_id' => null,
            'payment_method' => 'debito',
            'card_id' => $cardId,
            'tags' => null,
            'notes' => 'Pagamento de fatura ' . $month,
            'installment_group' => null,
            'installment_number' => null,
            'installment_total' => null,
        ];
        Transaction::create($user['id'], $data);

        $stmt = db()->prepare('UPDATE card_invoices SET status = "paga", paid_at = NOW(), total = ? WHERE id = ?');
        $stmt->execute([$total, $invoice['id']]);
        flash('success', 'Fatura paga com sucesso.');
        redirect('cards/' . $cardId . '/invoice?month=' . $month);
    }

    private function getOrCreateInvoice(int $userId, int $cardId, string $month, float $total): array
    {
        $stmt = db()->prepare('SELECT * FROM card_invoices WHERE user_id = ? AND card_id = ? AND month = ?');
        $stmt->execute([$userId, $cardId, $month]);
        $invoice = $stmt->fetch();
        if ($invoice) {
            return $invoice;
        }
        $stmt = db()->prepare('INSERT INTO card_invoices (user_id, card_id, month, status, total, created_at) VALUES (?, ?, ?, "aberta", ?, NOW())');
        $stmt->execute([$userId, $cardId, $month, $total]);
        $id = (int)db()->lastInsertId();
        return [
            'id' => $id,
            'user_id' => $userId,
            'card_id' => $cardId,
            'month' => $month,
            'status' => 'aberta',
            'total' => $total,
        ];
    }
}
