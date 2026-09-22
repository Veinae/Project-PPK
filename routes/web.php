<?php

/**
 * Router sederhana tanpa dependency eksternal.
 * Cocok untuk struktur "MVC ringan" sesuai kontrak dokumen (bukan framework Laravel penuh).
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/middleware/AdminMiddleware.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/FacilityController.php';
require_once __DIR__ . '/../app/controllers/ReservationController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace(BASE_URL, '', $uri), '/');
if ($uri === '') {
    $uri = '/';
}
$method = $_SERVER['REQUEST_METHOD'];

$auth = new AuthController();
$admin = new AdminController();
$facilityCtrl = new FacilityController();
$reservationCtrl = new ReservationController();

// -------------------- PUBLIC / AUTH --------------------
if ($uri === '/' && $method === 'GET') {
    redirect('/login');
}
if ($uri === '/register' && $method === 'GET') {
    $auth->showRegister();
    exit;
}
if ($uri === '/register' && $method === 'POST') {
    $auth->register();
    exit;
}
if ($uri === '/login' && $method === 'GET') {
    $auth->showLogin();
    exit;
}
if ($uri === '/login' && $method === 'POST') {
    $auth->login();
    exit;
}
if ($uri === '/logout' && $method === 'GET') {
    $auth->logout();
    exit;
}

// -------------------- ADMIN (dilindungi AdminMiddleware) --------------------
if (str_starts_with($uri, '/admin')) {
    AdminMiddleware::handle();

    match (true) {
        $uri === '/admin/dashboard' && $method === 'GET' => $admin->dashboard(),
        $uri === '/admin/users' && $method === 'GET' => $admin->users(),
        $uri === '/admin/users/verify' && $method === 'POST' => $admin->verifyUser(),
        $uri === '/admin/users/create' && $method === 'POST' => $admin->createUser(),
        $uri === '/admin/facilities' && $method === 'GET' => $admin->facilities(),
        $uri === '/admin/facilities/create' && $method === 'GET' => $admin->facilityForm(),
        $uri === '/admin/facilities/edit' && $method === 'GET' => $admin->facilityForm(),
        $uri === '/admin/facilities/save' && $method === 'POST' => $admin->saveFacility(),
        $uri === '/admin/facilities/deactivate' && $method === 'POST' => $admin->deactivateFacility(),
        $uri === '/admin/analytics' && $method === 'GET' => $admin->analytics(),
        $uri === '/admin/analytics/export' && $method === 'GET' => $admin->exportOccupancy(),
        $uri === '/admin/analytics/export-damage' && $method === 'GET' => $admin->exportDamage(),
        default => http_response_code(404),
    };
    exit;
}

// -------------------- MODUL 2: PUBLIK & RESERVASI --------------------
if ($uri === '/facilities' && $method === 'GET') {
    $facilityCtrl->index();
    exit;
}
if ($uri === '/facility/detail' && $method === 'GET') {
    $facilityCtrl->show();
    exit;
}

// Submit, history, dan cancel reservasi butuh session aktif (siapa pun role-nya).
if (str_starts_with($uri, '/reservation')) {
    AuthMiddleware::handle();

    match (true) {
        $uri === '/reservation/create' && $method === 'POST' => $reservationCtrl->store(),
        $uri === '/reservation/history' && $method === 'GET' => $reservationCtrl->history(),
        $uri === '/reservation/cancel' && $method === 'POST' => $reservationCtrl->cancel(),
        default => http_response_code(404),
    };
    exit;
}

// -------------------- PLACEHOLDER UNTUK MODUL 3 --------------------
// Rute /petugas/* akan ditambahkan saat Modul 3 dikerjakan.
// Ditinggalkan kosong di sini agar tidak bentrok dengan route naming tim lain.

http_response_code(404);
echo '404 — Halaman tidak ditemukan.';
