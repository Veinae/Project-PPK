<?php

require_once __DIR__ . '/AuthMiddleware.php';

/**
 * Menolak akses /petugas/* untuk role selain PETUGAS atau ADMIN.
 * Disediakan di sini (kontrak folder Modul 1) untuk dipakai Modul 3.
 */
class PetugasMiddleware
{
    public static function handle(): void
    {
        AuthMiddleware::handle();

        if (!in_array(currentRole(), ['PETUGAS', 'ADMIN'], true)) {
            http_response_code(403);
            require __DIR__ . '/../../views/errors/403.php';
            exit;
        }
    }
}
