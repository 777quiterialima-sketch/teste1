<?php
declare(strict_types=1);

// Basic configuration. Update DB credentials for your environment.
const DB_HOST = 'localhost';
const DB_NAME = 'discipline_dashboard';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

const APP_NAME = 'Dashboard de Disciplina';
const BASE_URL = '';

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', '1');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
