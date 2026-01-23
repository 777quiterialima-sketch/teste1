<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/app/helpers/helpers.php';
require __DIR__ . '/app/models/User.php';
require __DIR__ . '/app/models/Account.php';
require __DIR__ . '/app/models/Card.php';
require __DIR__ . '/app/models/Category.php';
require __DIR__ . '/app/models/Transaction.php';
require __DIR__ . '/app/models/Budget.php';
require __DIR__ . '/app/models/BankConnection.php';
require __DIR__ . '/app/models/Import.php';
require __DIR__ . '/app/controllers/AuthController.php';
require __DIR__ . '/app/controllers/DashboardController.php';
require __DIR__ . '/app/controllers/AccountsController.php';
require __DIR__ . '/app/controllers/CardsController.php';
require __DIR__ . '/app/controllers/TransactionsController.php';
require __DIR__ . '/app/controllers/ReportsController.php';
require __DIR__ . '/app/controllers/BudgetsController.php';
require __DIR__ . '/app/controllers/ConnectionsController.php';

set_exception_handler(function (Throwable $e): void {
    log_message($e->getMessage());
    http_response_code(500);
    view('errors/500', ['title' => 'Erro interno']);
});

$requestPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$basePath = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = $requestPath;
if ($basePath && str_starts_with($requestPath, $basePath)) {
    $path = trim(substr($requestPath, strlen($basePath)), '/');
}
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$dashboardController = new DashboardController();
$accountsController = new AccountsController();
$cardsController = new CardsController();
$transactionsController = new TransactionsController();
$reportsController = new ReportsController();
$budgetsController = new BudgetsController();
$connectionsController = new ConnectionsController();

if ($path === '') {
    if (is_logged_in()) {
        redirect('dashboard');
    }
    redirect('login');
}

if (preg_match('#^cards/(\d+)/invoice$#', $path, $matches)) {
    $_GET['id'] = $matches[1];
    $cardsController->invoice();
    return;
}

switch ($path) {
    case 'login':
        if ($method === 'POST') {
            $authController->login();
            break;
        }
        $authController->showLogin();
        break;
    case 'register':
        if ($method === 'POST') {
            $authController->register();
            break;
        }
        $authController->showRegister();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'profile':
        if ($method === 'POST') {
            $authController->updateProfile();
            break;
        }
        $authController->profile();
        break;
    case 'dashboard':
        $dashboardController->index();
        break;
    case 'accounts':
        $accountsController->index();
        break;
    case 'accounts/create':
        $accountsController->create();
        break;
    case 'accounts/store':
        $accountsController->store();
        break;
    case 'accounts/edit':
        $accountsController->edit();
        break;
    case 'accounts/update':
        $accountsController->update();
        break;
    case 'accounts/delete':
        $accountsController->delete();
        break;
    case 'cards':
        $cardsController->index();
        break;
    case 'cards/create':
        $cardsController->create();
        break;
    case 'cards/store':
        $cardsController->store();
        break;
    case 'cards/edit':
        $cardsController->edit();
        break;
    case 'cards/update':
        $cardsController->update();
        break;
    case 'cards/delete':
        $cardsController->delete();
        break;
    case 'cards/pay-invoice':
        $cardsController->payInvoice();
        break;
    case 'transactions':
        $transactionsController->index();
        break;
    case 'transactions/create':
        $transactionsController->create();
        break;
    case 'transactions/store':
        $transactionsController->store();
        break;
    case 'transactions/edit':
        $transactionsController->edit();
        break;
    case 'transactions/update':
        $transactionsController->update();
        break;
    case 'transactions/delete':
        $transactionsController->delete();
        break;
    case 'reports':
        $reportsController->index();
        break;
    case 'budgets':
        $budgetsController->index();
        break;
    case 'budgets/store':
        $budgetsController->store();
        break;
    case 'connections':
        $connectionsController->index();
        break;
    case 'connections/store':
        $connectionsController->store();
        break;
    case 'connections/delete':
        $connectionsController->delete();
        break;
    case 'connections/import':
        $connectionsController->import();
        break;
    default:
        http_response_code(404);
        view('errors/404', ['title' => 'Página não encontrada']);
        break;
}
