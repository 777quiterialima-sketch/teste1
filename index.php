<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

if (current_user()) {
    redirect('/pages/today.php');
}

redirect('/pages/login.php');
