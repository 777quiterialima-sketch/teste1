<?php
declare(strict_types=1);

function render_header(string $title): void
{
    $user = current_user();
    $flash = flash('message');
    echo '<!DOCTYPE html><html lang="pt-br"><head>';
    echo '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' - ' . e(APP_NAME) . '</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script>';
    echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
    echo '</head><body class="bg-black text-white min-h-screen">';
    echo '<div class="flex">';
    echo '<aside class="hidden md:flex md:flex-col w-64 bg-zinc-900 min-h-screen p-6">';
    echo '<div class="text-xl font-bold mb-6">' . e(APP_NAME) . '</div>';
    echo '<nav class="space-y-3">';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/today.php">Hoje</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/calendar.php">Calendário</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/reports.php">Relatórios</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/settings.php">Configurações</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/account.php">Conta</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/logout.php">Sair</a>';
    echo '</nav></aside>';
    echo '<div class="flex-1">';
    echo '<header class="bg-zinc-900 p-4 flex items-center justify-between">';
    echo '<div class="text-lg font-semibold">' . e($title) . '</div>';
    echo '<button class="md:hidden text-sm" id="menuToggle">Menu</button>';
    if ($user) {
        echo '<div class="text-sm text-gray-300">' . e($user['name']) . '</div>';
    }
    echo '</header>';
    echo '<div id="mobileMenu" class="md:hidden hidden bg-zinc-900 px-4 py-3">';
    echo '<nav class="space-y-2">';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/today.php">Hoje</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/calendar.php">Calendário</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/reports.php">Relatórios</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/settings.php">Configurações</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/account.php">Conta</a>';
    echo '<a class="block text-sm uppercase tracking-widest text-gray-300 hover:text-white" href="' . BASE_URL . '/pages/logout.php">Sair</a>';
    echo '</nav></div>';
    echo '<main class="p-6 space-y-6">';
    if ($flash) {
        echo '<div class="bg-emerald-600 text-white px-4 py-2 rounded">' . e($flash) . '</div>';
    }
}

function render_footer(): void
{
    echo '</main></div></div>';
    echo '<script>';
    echo 'const toggle=document.getElementById("menuToggle");if(toggle){toggle.addEventListener("click",()=>{document.getElementById("mobileMenu").classList.toggle("hidden");});}';
    echo '</script>';
    echo '</body></html>';
}
