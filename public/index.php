<?php

declare(strict_types=1);

require __DIR__ . '/../app/Support/Database.php';
require __DIR__ . '/../app/Repositories/UserRepository.php';
require __DIR__ . '/../app/Repositories/GameRepository.php';
require __DIR__ . '/../app/Controllers/Admin/AuthController.php';
require __DIR__ . '/../app/Controllers/Admin/GameController.php';

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\GameController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/' || $uri === '') {
    header('Location: /admin/login');
    exit;
}

if ($uri === '/admin/login' && $method === 'GET') {
    (new AuthController())->showLogin();
    exit;
}

if ($uri === '/admin/login' && $method === 'POST') {
    (new AuthController())->login();
    exit;
}

if ($uri === '/admin/logout' && $method === 'POST') {
    (new AuthController())->logout();
    exit;
}

if ($uri === '/admin/games' && $method === 'GET') {
    (new GameController())->index();
    exit;
}

if ($uri === '/admin/games/create' && $method === 'GET') {
    (new GameController())->createForm();
    exit;
}

if ($uri === '/admin/games' && $method === 'POST') {
    (new GameController())->store();
    exit;
}

if ($method === 'GET' && preg_match('#^/admin/games/(\d+)$#', $uri, $matches)) {
    (new GameController())->show((int) $matches[1]);
    exit;
}

http_response_code(404);
echo '404 Not Found';
