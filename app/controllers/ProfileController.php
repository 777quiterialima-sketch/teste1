<?php

class ProfileController
{
    public function index(): void
    {
        require_auth();
        $user = current_user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $name = trim($_POST['name'] ?? $user['name']);
            $email = trim($_POST['email'] ?? $user['email']);
            User::updateProfile($user['id'], $name, $email);

            if (!empty($_POST['new_password'])) {
                User::updatePassword($user['id'], password_hash($_POST['new_password'], PASSWORD_BCRYPT));
            }

            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            flash('success', 'Perfil atualizado.');
            redirect('/profile');
        }

        view('profile/index', ['user' => User::findById($user['id'])]);
    }
}
