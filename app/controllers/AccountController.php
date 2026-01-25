<?php

class AccountController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $accounts = Account::allByUser($user['id']);
        foreach ($accounts as &$account) {
            $account['balance'] = Account::balance((int)$account['id']);
        }
        view('accounts/index', ['accounts' => $accounts]);
    }

    public function create(): void
    {
        require_auth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $data = [
                'user_id' => current_user()['id'],
                'name' => trim($_POST['name'] ?? ''),
                'type' => $_POST['type'] ?? 'corrente',
                'initial_balance' => (float)($_POST['initial_balance'] ?? 0),
                'color' => $_POST['color'] ?? '#2ebd59',
            ];
            Account::create($data);
            flash('success', 'Conta criada com sucesso.');
            redirect('/accounts');
        }

        view('accounts/form', ['account' => null]);
    }

    public function edit(): void
    {
        require_auth();
        $user = current_user();
        $account = Account::find((int)($_GET['id'] ?? 0), $user['id']);
        if (!$account) {
            flash('error', 'Conta não encontrada.');
            redirect('/accounts');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            Account::update((int)$account['id'], $user['id'], [
                'name' => trim($_POST['name'] ?? ''),
                'type' => $_POST['type'] ?? 'corrente',
                'initial_balance' => (float)($_POST['initial_balance'] ?? 0),
                'color' => $_POST['color'] ?? '#2ebd59',
            ]);
            flash('success', 'Conta atualizada.');
            redirect('/accounts');
        }

        view('accounts/form', ['account' => $account]);
    }

    public function delete(): void
    {
        require_auth();
        verify_csrf();
        Account::delete((int)($_POST['id'] ?? 0), current_user()['id']);
        flash('success', 'Conta removida.');
        redirect('/accounts');
    }
}
