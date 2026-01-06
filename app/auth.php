<?php
declare(strict_types=1);

function require_login(): void
{
    if (!current_user()) {
        redirect('/pages/login.php');
    }
}
