<?php
/**
 * Konfigurasi aplikasi umum: nama app, base URL, dan bootstrap session.
 */

define('APP_NAME', 'FACILIA');
define('BASE_URL', '/facilia/public'); // sesuaikan dengan alias virtual host / folder XAMPP

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper redirect sederhana.
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * Helper flash message (disimpan 1x tayang di session).
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function getFlash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Helper cek role & auth dari session (dipakai di view untuk tampilan kondisional).
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}
