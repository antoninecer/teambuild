<?php

declare(strict_types=1);

// Core
require __DIR__ . '/../app/Support/Database.php';

// Repositories
require __DIR__ . '/../app/Repositories/UserRepository.php';
require __DIR__ . '/../app/Repositories/GameRepository.php';
require __DIR__ . '/../app/Repositories/PoiRepository.php';
require __DIR__ . '/../app/Repositories/InviteRepository.php';
require __DIR__ . '/../app/Repositories/TeamRepository.php';
require __DIR__ . '/../app/Repositories/PlayerRepository.php';

// Controllers - Admin
require __DIR__ . '/../app/Controllers/Admin/AuthController.php';
require __DIR__ . '/../app/Controllers/Admin/DashboardController.php';
require __DIR__ . '/../app/Controllers/Admin/GameController.php';
require __DIR__ . '/../app/Controllers/Admin/PoiController.php';
require __DIR__ . '/../app/Controllers/Admin/InviteController.php';
require __DIR__ . '/../app/Controllers/Admin/UserController.php';

// Controllers - Player
require __DIR__ . '/../app/Controllers/Player/PlayerController.php';

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\GameController;
use App\Controllers\Admin\InviteController;
use App\Controllers\Admin\PoiController;
use App\Controllers\Admin\UserController;
use App\Controllers\Player\PlayerController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// =========================
// ROOT
// =========================
if ($uri === '/' || $uri === '') {
    header('Location: /admin');
    exit;
}

// =========================
// AUTH
// =========================
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

// =========================
// ADMIN DASHBOARD
// =========================
if ($uri === '/admin' && $method === 'GET') {
    (new DashboardController())->index();
    exit;
}

// =========================
// ADMIN - USERS
// =========================
if ($uri === '/admin/users' && $method === 'GET') {
    (new UserController())->index();
    exit;
}

if ($uri === '/admin/users/create' && $method === 'POST') {
    (new UserController())->create();
    exit;
}

if ($method === 'POST' && preg_match('#^/admin/users/(\d+)/toggle$#', $uri, $matches)) {
    (new UserController())->toggle((int) $matches[1]);
    exit;
}

if ($method === 'GET' && preg_match('#^/admin/users/(\d+)/password$#', $uri, $matches)) {
    (new UserController())->changePasswordForm((int) $matches[1]);
    exit;
}

if ($method === 'POST' && preg_match('#^/admin/users/(\d+)/password$#', $uri, $matches)) {
    (new UserController())->changePassword((int) $matches[1]);
    exit;
}

// =========================
// ADMIN - GAMES
// =========================
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

// =========================
// ADMIN - POI
// =========================
if ($method === 'GET' && preg_match('#^/admin/games/(\d+)/pois$#', $uri, $matches)) {
    (new PoiController())->index((int) $matches[1]);
    exit;
}

if ($method === 'GET' && preg_match('#^/admin/games/(\d+)/pois/create$#', $uri, $matches)) {
    (new PoiController())->createForm((int) $matches[1]);
    exit;
}

if ($method === 'POST' && preg_match('#^/admin/games/(\d+)/pois$#', $uri, $matches)) {
    (new PoiController())->store((int) $matches[1]);
    exit;
}

if ($method === 'GET' && preg_match('#^/admin/pois/(\d+)/edit$#', $uri, $matches)) {
    (new PoiController())->editForm((int) $matches[1]);
    exit;
}

if ($method === 'POST' && preg_match('#^/admin/pois/(\d+)$#', $uri, $matches)) {
    (new PoiController())->update((int) $matches[1]);
    exit;
}

if ($method === 'POST' && preg_match('#^/admin/pois/(\d+)/delete$#', $uri, $matches)) {
    (new PoiController())->delete((int) $matches[1]);
    exit;
}

// =========================
// ADMIN - INVITES
// =========================
if ($method === 'GET' && preg_match('#^/admin/games/(\d+)/invites$#', $uri, $matches)) {
    (new InviteController())->index((int) $matches[1]);
    exit;
}

if ($method === 'GET' && preg_match('#^/admin/games/(\d+)/invites/create$#', $uri, $matches)) {
    (new InviteController())->createForm((int) $matches[1]);
    exit;
}

if ($method === 'POST' && preg_match('#^/admin/games/(\d+)/invites$#', $uri, $matches)) {
    (new InviteController())->store((int) $matches[1]);
    exit;
}

if ($method === 'POST' && preg_match('#^/admin/invites/(\d+)/delete$#', $uri, $matches)) {
    (new InviteController())->delete((int) $matches[1]);
    exit;
}

// =========================
// PLAYER
// =========================
if ($method === 'GET' && preg_match('#^/game/([^/]+)$#', $uri, $matches)) {
    (new PlayerController())->showGame($matches[1]);
    exit;
}

if ($method === 'POST' && preg_match('#^/game/([^/]+)/register$#', $uri, $matches)) {
    (new PlayerController())->register($matches[1]);
    exit;
}

// =========================
// API
// =========================
if ($method === 'POST' && $uri === '/api/player/location') {
    (new PlayerController())->updateLocation();
    exit;
}

if ($method === 'POST' && $uri === '/api/player/help') {
    (new PlayerController())->requestHelp();
    exit;
}

// =========================
// 404
// =========================
http_response_code(404);
echo '404 Not Found';