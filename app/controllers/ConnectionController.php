<?php

class ConnectionController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $accounts = Account::allByUser($user['id']);
        $connections = BankConnection::allByUser($user['id']);
        $importPreview = $_SESSION['import_preview'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = $_POST['action'] ?? '';

            if ($action === 'connect') {
                BankConnection::create([
                    'user_id' => $user['id'],
                    'account_id' => (int)($_POST['account_id'] ?? 0),
                    'bank_name' => trim($_POST['bank_name'] ?? ''),
                    'type' => 'manual',
                    'status' => 'active',
                ]);
                flash('success', 'Conexão registrada.');
                redirect('/connections');
            }

            if ($action === 'import_csv' && isset($_FILES['file']['tmp_name'])) {
                $mapping = [
                    'date' => (int)($_POST['col_date'] ?? 0),
                    'description' => (int)($_POST['col_description'] ?? 1),
                    'amount' => (int)($_POST['col_amount'] ?? 2),
                    'type' => (int)($_POST['col_type'] ?? 3),
                ];
                $rows = $this->parseCsv($_FILES['file']['tmp_name'], $mapping);
                $_SESSION['import_preview'] = [
                    'rows' => $rows,
                    'mapping' => $mapping,
                    'filename' => $_FILES['file']['name'],
                ];
                flash('success', 'Arquivo carregado. Revise antes de importar.');
                redirect('/connections');
            }

            if ($action === 'confirm_import' && !empty($_SESSION['import_preview'])) {
                $preview = $_SESSION['import_preview'];
                $inserted = $this->importRows($user['id'], $preview['rows']);
                Import::create([
                    'user_id' => $user['id'],
                    'type' => 'csv',
                    'filename' => $preview['filename'],
                ]);
                unset($_SESSION['import_preview']);
                flash('success', "Importação concluída. $inserted lançamentos inseridos.");
                redirect('/connections');
            }

            if ($action === 'import_ofx' && isset($_FILES['ofx']['tmp_name'])) {
                $rows = $this->parseOfx($_FILES['ofx']['tmp_name']);
                $inserted = $this->importRows($user['id'], $rows);
                Import::create([
                    'user_id' => $user['id'],
                    'type' => 'ofx',
                    'filename' => $_FILES['ofx']['name'],
                ]);
                flash('success', "Importação OFX concluída. $inserted lançamentos inseridos.");
                redirect('/connections');
            }
        }

        view('connections/index', compact('accounts', 'connections', 'importPreview'));
    }

    private function parseCsv(string $path, array $mapping): array
    {
        $handle = fopen($path, 'r');
        $rows = [];
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) < 3) {
                continue;
            }
            $rows[] = [
                'date' => $data[$mapping['date']] ?? '',
                'description' => $data[$mapping['description']] ?? '',
                'amount' => (float)str_replace(',', '.', $data[$mapping['amount']] ?? 0),
                'type' => strtolower(trim($data[$mapping['type']] ?? '')),
            ];
        }
        fclose($handle);
        return $rows;
    }

    private function parseOfx(string $path): array
    {
        $content = file_get_contents($path);
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/s', $content, $matches);
        $rows = [];
        foreach ($matches[1] as $block) {
            preg_match('/<DTPOSTED>(\d{8})/', $block, $dateMatch);
            preg_match('/<TRNAMT>([-\d\.]+)/', $block, $amountMatch);
            preg_match('/<MEMO>([^\r\n<]+)/', $block, $memoMatch);
            $date = $dateMatch[1] ?? date('Ymd');
            $rows[] = [
                'date' => substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2),
                'description' => trim($memoMatch[1] ?? 'Importação OFX'),
                'amount' => (float)($amountMatch[1] ?? 0),
                'type' => ((float)($amountMatch[1] ?? 0)) >= 0 ? 'receita' : 'despesa',
            ];
        }
        return $rows;
    }

    private function importRows(int $userId, array $rows): int
    {
        $inserted = 0;
        foreach ($rows as $row) {
            $amount = abs((float)$row['amount']);
            $type = in_array($row['type'], ['receita', 'income']) ? 'income' : 'expense';
            $exists = Database::connection()->prepare('SELECT id FROM transactions WHERE user_id = :user_id AND date = :date AND amount = :amount AND description = :description');
            $exists->execute([
                'user_id' => $userId,
                'date' => $row['date'],
                'amount' => $amount,
                'description' => $row['description'],
            ]);
            if ($exists->fetch()) {
                continue;
            }
            Transaction::create([
                'user_id' => $userId,
                'type' => $type,
                'date' => $row['date'],
                'description' => $row['description'],
                'category_id' => null,
                'amount' => $amount,
                'account_id' => null,
                'account_dest_id' => null,
                'payment_method' => 'importado',
                'card_id' => null,
                'invoice_month' => null,
                'tags' => null,
                'notes' => 'Importado via conexão bancária',
            ]);
            $inserted++;
        }
        return $inserted;
    }
}
