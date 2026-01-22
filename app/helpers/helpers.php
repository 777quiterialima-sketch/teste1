<?php
function load_config(): array
{
    static $config = null;
    if ($config === null) {
        $configPath = __DIR__ . '/../../config/config.php';
        if (!file_exists($configPath)) {
            $examplePath = __DIR__ . '/../../config/config.php.example';
            throw new RuntimeException('Crie config/config.php baseado em ' . $examplePath);
        }
        $config = require $configPath;
    }
    return $config;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $config = load_config();
        $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=' . $config['db']['charset'];
        $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function base_url(string $path = ''): string
{
    $config = load_config();
    return rtrim($config['app_url'], '/') . '/' . ltrim($path, '/');
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function view(string $template, array $data = [], string $layout = 'layout'): void
{
    extract($data);
    $viewPath = __DIR__ . '/../views/' . $template . '.php';
    if (!file_exists($viewPath)) {
        throw new RuntimeException('View não encontrada: ' . $template);
    }
    ob_start();
    require $viewPath;
    $content = ob_get_clean();
    require __DIR__ . '/../views/' . $layout . '.php';
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

function require_auth(): void
{
    if (!is_logged_in()) {
        redirect('login');
    }
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
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        throw new RuntimeException('Token CSRF inválido.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function log_message(string $message): void
{
    $logPath = __DIR__ . '/../../storage/logs/app.log';
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logPath, $entry, FILE_APPEND);
}
