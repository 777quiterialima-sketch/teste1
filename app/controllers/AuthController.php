<?php
class AuthController
{
    public function showLogin(): void
    {
        view('auth/login', ['title' => 'Entrar'], 'auth_layout');
    }

    public function login(): void
    {
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', 'E-mail ou senha inválidos.');
            redirect('login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
        flash('success', 'Bem-vindo de volta!');
        redirect('dashboard');
    }

    public function showRegister(): void
    {
        view('auth/register', ['title' => 'Criar conta'], 'auth_layout');
    }

    public function register(): void
    {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            flash('error', 'Preencha todos os campos.');
            redirect('register');
        }
        if (User::findByEmail($email)) {
            flash('error', 'E-mail já cadastrado.');
            redirect('register');
        }

        $userId = User::create($name, $email, $password);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user'] = ['id' => $userId, 'name' => $name, 'email' => $email];
        flash('success', 'Conta criada com sucesso!');
        redirect('dashboard');
    }

    public function logout(): void
    {
        session_destroy();
        redirect('login');
    }

    public function profile(): void
    {
        require_auth();
        $user = current_user();
        view('auth/profile', ['title' => 'Meu perfil', 'user' => $user]);
    }

    public function updateProfile(): void
    {
        require_auth();
        verify_csrf();
        $user = current_user();
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name) {
            User::updateProfile($user['id'], $name);
            $_SESSION['user']['name'] = $name;
        }
        if ($password) {
            User::updatePassword($user['id'], $password);
        }
        flash('success', 'Perfil atualizado.');
        redirect('profile');
    }
}
