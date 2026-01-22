<?php
class ConnectionsController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $connections = BankConnection::all($user['id']);
        $preview = $_SESSION['import_preview'] ?? null;
        view('connections/index', [
            'title' => 'Conexão bancária',
            'connections' => $connections,
            'accounts' => Account::all($user['id']),
            'preview' => $preview,
        ]);
    }

    public function store(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $accountId = (int)($_POST['account_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'manual';
        BankConnection::create($user['id'], $accountId, $name, $type);
        flash('success', 'Conexão registrada.');
        redirect('connections');
    }

    public function delete(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);
        BankConnection::delete($user['id'], $id);
        flash('success', 'Conexão removida.');
        redirect('connections');
    }

    public function import(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $action = $_POST['action'] ?? 'preview';

        if ($action === 'confirm') {
            $this->confirmImport($user['id']);
            redirect('connections');
        }

        $file = $_FILES['import_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Falha ao enviar o arquivo.');
            redirect('connections');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $destination = __DIR__ . '/../../storage/uploads/' . uniqid('import_', true) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $destination);

        $type = $ext === 'ofx' ? 'ofx' : 'csv';
        $preview = $type === 'ofx'
            ? $this->parseOfx($destination)
            : $this->parseCsv($destination);

        $_SESSION['import_preview'] = [
            'items' => $preview,
            'file_name' => $file['name'],
            'type' => $type,
        ];
        ImportModel::create($user['id'], $file['name'], $type, 'preview');
        flash('success', 'Prévia gerada. Revise e confirme a importação.');
        redirect('connections');
    }

    private function parseCsv(string $path): array
    {
        $delimiter = $_POST['delimiter'] ?? ';';
        $dateCol = (int)($_POST['date_col'] ?? 0);
        $descCol = (int)($_POST['desc_col'] ?? 1);
        $amountCol = (int)($_POST['amount_col'] ?? 2);
        $typeCol = (int)($_POST['type_col'] ?? 3);

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count($data) < 3) {
                    continue;
                }
                $rows[] = [
                    'date' => $this->normalizeDate($data[$dateCol] ?? ''),
                    'description' => trim($data[$descCol] ?? ''),
                    'amount' => (float)str_replace(',', '.', $data[$amountCol] ?? 0),
                    'type' => strtolower(trim($data[$typeCol] ?? 'despesa')),
                ];
            }
            fclose($handle);
        }
        return $rows;
    }

    private function parseOfx(string $path): array
    {
        $content = file_get_contents($path);
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/s', $content, $matches);
        $rows = [];
        foreach ($matches[1] as $block) {
            preg_match('/<DTPOSTED>(\d{8})/', $block, $dateMatch);
            preg_match('/<TRNAMT>([^<]+)/', $block, $amountMatch);
            preg_match('/<MEMO>([^<]+)/', $block, $memoMatch);
            $amount = isset($amountMatch[1]) ? (float)$amountMatch[1] : 0;
            $rows[] = [
                'date' => $this->normalizeDate($dateMatch[1] ?? ''),
                'description' => trim($memoMatch[1] ?? 'Importação OFX'),
                'amount' => abs($amount),
                'type' => $amount < 0 ? 'despesa' : 'receita',
            ];
        }
        return $rows;
    }

    private function normalizeDate(string $date): string
    {
        $date = preg_replace('/\D/', '', $date);
        if (strlen($date) >= 8) {
            return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }
        return date('Y-m-d');
    }

    private function confirmImport(int $userId): void
    {
        $preview = $_SESSION['import_preview'] ?? null;
        if (!$preview) {
            flash('error', 'Nenhuma prévia encontrada.');
            return;
        }
        $accountId = (int)($_POST['account_id'] ?? 0);
        $inserted = 0;
        foreach ($preview['items'] as $item) {
            $existsStmt = db()->prepare('SELECT id FROM transactions WHERE user_id = ? AND date = ? AND amount = ? AND description = ? LIMIT 1');
            $existsStmt->execute([$userId, $item['date'], $item['amount'], $item['description']]);
            if ($existsStmt->fetch()) {
                continue;
            }
            Transaction::create($userId, [
                'type' => $item['type'] === 'receita' ? 'receita' : 'despesa',
                'date' => $item['date'],
                'description' => $item['description'],
                'category_id' => null,
                'amount' => $item['amount'],
                'account_id' => $accountId,
                'account_dest_id' => null,
                'payment_method' => 'pix',
                'card_id' => null,
                'tags' => 'importado',
                'notes' => 'Importação ' . $preview['type'],
                'installment_group' => null,
                'installment_number' => null,
                'installment_total' => null,
            ]);
            $inserted++;
        }
        unset($_SESSION['import_preview']);
        flash('success', 'Importação concluída. Itens adicionados: ' . $inserted);
    }
}
