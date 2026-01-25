<?php

require __DIR__ . '/app/helpers/helpers.php';

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

if (!file_exists(__DIR__ . '/config/config.php')) {
    echo 'Arquivo de configuração não encontrado. Copie config/config.php.example para config/config.php.';
    exit;
}

require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/core/Router.php';

foreach (glob(__DIR__ . '/app/models/*.php') as $file) {
    require $file;
}
foreach (glob(__DIR__ . '/app/controllers/*.php') as $file) {
    require $file;
}

set_exception_handler(function ($exception) {
    log_message('Erro: ' . $exception->getMessage());
    http_response_code(500);
    view('errors/500');
});

$router = new Router();

$authController = new AuthController();
$dashboardController = new DashboardController();
$accountController = new AccountController();
$cardController = new CardController();
$transactionController = new TransactionController();
$reportController = new ReportController();
$budgetController = new BudgetController();
$connectionController = new ConnectionController();
$profileController = new ProfileController();

$router->get('/', function () {
    if (current_user()) {
        redirect('/dashboard');
    }
    redirect('/login');
});

$router->get('/login', [$authController, 'login']);
$router->post('/login', [$authController, 'login']);
$router->get('/register', [$authController, 'register']);
$router->post('/register', [$authController, 'register']);
$router->get('/logout', [$authController, 'logout']);

$router->get('/dashboard', [$dashboardController, 'index']);

$router->get('/accounts', [$accountController, 'index']);
$router->get('/accounts/create', [$accountController, 'create']);
$router->post('/accounts/create', [$accountController, 'create']);
$router->get('/accounts/edit', [$accountController, 'edit']);
$router->post('/accounts/edit', [$accountController, 'edit']);
$router->post('/accounts/delete', [$accountController, 'delete']);

$router->get('/cards', [$cardController, 'index']);
$router->get('/cards/create', [$cardController, 'create']);
$router->post('/cards/create', [$cardController, 'create']);
$router->get('/cards/edit', [$cardController, 'edit']);
$router->post('/cards/edit', [$cardController, 'edit']);
$router->post('/cards/delete', [$cardController, 'delete']);
$router->get('/cards/{id}/invoice', [$cardController, 'invoice']);
$router->post('/cards/{id}/invoice', [$cardController, 'invoice']);

$router->get('/transactions', [$transactionController, 'index']);
$router->get('/transactions/create', [$transactionController, 'create']);
$router->post('/transactions/create', [$transactionController, 'create']);
$router->get('/transactions/edit', [$transactionController, 'edit']);
$router->post('/transactions/edit', [$transactionController, 'edit']);
$router->post('/transactions/delete', [$transactionController, 'delete']);

$router->get('/reports', [$reportController, 'index']);
$router->get('/budgets', [$budgetController, 'index']);
$router->post('/budgets', [$budgetController, 'index']);
$router->get('/connections', [$connectionController, 'index']);
$router->post('/connections', [$connectionController, 'index']);
$router->get('/profile', [$profileController, 'index']);
$router->post('/profile', [$profileController, 'index']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
