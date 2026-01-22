<?php
class AccountsController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();
        $accounts = Account::all($user['id']);
        view('accounts/index', [
            'title' => 'Contas',
            'accounts' => $accounts,
        ]);
    }

    public function create(): void
    {
        require_auth();
        view('accounts/form', [
            'title' => 'Nova conta',
            'account' => null,
        ]);
    }

    public function store(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'type' => $_POST['type'] ?? 'corrente',
            'initial_balance' => (float)($_POST['initial_balance'] ?? 0),
            'color' => $_POST['color'] ?? '#2ecc71',
            'icon' => trim($_POST['icon'] ?? ''),
        ];
        Account::create($user['id'], $data);
        flash('success', 'Conta criada com sucesso.');
        redirect('accounts');
    }

    public function edit(): void
    {
        require_auth();
        $user = current_user();
        $id = (int)($_GET['id'] ?? 0);
        $account = Account::find($user['id'], $id);
        if (!$account) {
            flash('error', 'Conta não encontrada.');
            redirect('accounts');
        }
        view('accounts/form', [
            'title' => 'Editar conta',
            'account' => $account,
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
            'type' => $_POST['type'] ?? 'corrente',
            'initial_balance' => (float)($_POST['initial_balance'] ?? 0),
            'color' => $_POST['color'] ?? '#2ecc71',
            'icon' => trim($_POST['icon'] ?? ''),
        ];
        Account::update($user['id'], $id, $data);
        flash('success', 'Conta atualizada.');
        redirect('accounts');
    }

    public function delete(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $id = (int)($_POST['id'] ?? 0);
        Account::delete($user['id'], $id);
        flash('success', 'Conta removida.');
        redirect('accounts');
    }
}
