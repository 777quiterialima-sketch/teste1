<?php

function base_path(string $path = ''): string
{
    return __DIR__ . '/../../' . ltrim($path, '/');
}

function config(string $key, $default = null)
{
    static $config;
    if ($config === null) {
        $configPath = base_path('config/config.php');
        if (!file_exists($configPath)) {
            return $default;
        }
        $config = require $configPath;
    }

    return $config[$key] ?? $default;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(config('base_url', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo 'Token CSRF inválido.';
            exit;
        }
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function view(string $template, array $data = []): void
{
    extract($data);
    require base_path('app/views/' . $template . '.php');
}

function require_auth(): void
{
    if (empty($_SESSION['user'])) {
        redirect('/login');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function log_message(string $message): void
{
    $path = base_path('storage/logs/app.log');
    $date = date('Y-m-d H:i:s');
    file_put_contents($path, "[$date] $message\n", FILE_APPEND);
}
