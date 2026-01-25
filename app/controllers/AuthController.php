<?php

class AuthController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = User::findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ];
                flash('success', 'Bem-vindo de volta!');
                redirect('/dashboard');
            }

            flash('error', 'E-mail ou senha inválidos.');
        }

        view('auth/login');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($name === '' || $email === '' || $password === '') {
                flash('error', 'Preencha todos os campos.');
                view('auth/register');
                return;
            }

            if (User::findByEmail($email)) {
                flash('error', 'E-mail já cadastrado.');
                view('auth/register');
                return;
            }

            $userId = User::create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
            ]);

            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $userId, 'name' => $name, 'email' => $email];
            flash('success', 'Conta criada com sucesso!');
            redirect('/dashboard');
        }

        view('auth/register');
    }

    public function logout(): void
    {
        session_destroy();
        redirect('/login');
    }
}
