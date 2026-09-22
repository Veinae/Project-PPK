<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Facility.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/export.php';

class AdminController
{
    /** GET /admin/dashboard — ringkasan singkat. */
    public function dashboard(): void
    {
        $stats = [
            'total_users' => count(User::all()),
            'pending_users' => count(User::all('PENDING')),
            'total_petugas' => User::countByRole('PETUGAS'),
            'total_facilities' => count(Facility::all()),
        ];
        require __DIR__ . '/../../views/admin/dashboard.php';
    }

    // ---------------------------------------------------------------
    // USER & FACILITY MANAGEMENT
    // ---------------------------------------------------------------

    /** GET /admin/users — daftar user + filter verification_status. */
    public function users(): void
    {
        $filter = $_GET['status'] ?? null;
        if ($filter && !isInWhitelist($filter, ['PENDING', 'VERIFIED', 'REJECTED'])) {
            $filter = null;
        }
        $users = User::all($filter);
        require __DIR__ . '/../../views/admin/users.php';
    }

    /** POST /admin/users/verify — approve/reject registrasi PENDING. */
    public function verifyUser(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $decision = $_POST['decision'] ?? '';

        if (!$id || !isInWhitelist($decision, ['VERIFIED', 'REJECTED'])) {
            setFlash('error', 'Permintaan tidak valid.');
            redirect('/admin/users');
        }

        $target = User::findById($id);
        if (!$target || $target['verification_status'] !== 'PENDING') {
            setFlash('error', 'User tidak ditemukan atau bukan status PENDING.');
            redirect('/admin/users');
        }

        User::updateVerificationStatus($id, $decision);
        setFlash('success', 'Status user berhasil diperbarui menjadi ' . $decision . '.');
        redirect('/admin/users');
    }

    /** POST /admin/users/create — admin membuat akun langsung (user/petugas/admin). */
    public function createUser(): void
    {
        $name = cleanInput($_POST['name'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        $errors = [];
        if ($name === '') $errors[] = 'Nama wajib diisi.';
        if (!isValidEmail($email)) $errors[] = 'Email tidak valid.';
        if (!isValidPassword($password)) $errors[] = 'Password minimal 8 karakter.';
        if (!isInWhitelist($role, ['USER', 'PETUGAS', 'ADMIN'])) $errors[] = 'Role tidak valid.';
        if ($email !== '' && User::findByEmail($email)) $errors[] = 'Email sudah terdaftar.';

        if ($errors) {
            setFlash('errors', implode(' ', $errors));
            redirect('/admin/users');
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        // Akun yang dibuat admin langsung VERIFIED (tidak perlu approval sendiri).
        User::createByAdmin($name, $email, $hashed, $role, 'VERIFIED');

        setFlash('success', 'Akun ' . $role . ' berhasil dibuat.');
        redirect('/admin/users');
    }

    /** GET /admin/facilities — daftar fasilitas. */
    public function facilities(): void
    {
        $facilities = Facility::all();
        require __DIR__ . '/../../views/admin/facilities.php';
    }

    /** GET /admin/facilities/create & /admin/facilities/edit — form CRUD. */
    public function facilityForm(): void
    {
        $facility = null;
        if (!empty($_GET['id'])) {
            $facility = Facility::findById((int) $_GET['id']);
        }
        require __DIR__ . '/../../views/admin/facility-form.php';
    }

    /** POST /admin/facilities/save — create atau update tergantung ada id atau tidak. */
    public function saveFacility(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $name = cleanInput($_POST['name'] ?? '');
        $type = cleanInput($_POST['type'] ?? '');
        $location = cleanInput($_POST['location'] ?? '');
        $capacity = $_POST['capacity'] ?? '';
        $status = $_POST['status'] ?? '';
        $description = cleanInput($_POST['description'] ?? '');

        $errors = [];
        if ($name === '') $errors[] = 'Nama fasilitas wajib diisi.';
        if ($type === '') $errors[] = 'Tipe fasilitas wajib diisi.';
        if ($location === '') $errors[] = 'Lokasi wajib diisi.';
        if (!isValidCapacity($capacity)) $errors[] = 'Kapasitas harus angka bulat > 0.';
        if (!isInWhitelist($status, Facility::STATUSES)) $errors[] = 'Status tidak valid.';

        if ($errors) {
            setFlash('errors', implode(' ', $errors));
            redirect($id ? '/admin/facilities/edit?id=' . $id : '/admin/facilities/create');
        }

        if ($id) {
            Facility::update($id, $name, $type, $location, (int) $capacity, $status, $description);
            setFlash('success', 'Fasilitas berhasil diperbarui.');
        } else {
            Facility::create($name, $type, $location, (int) $capacity, $status, $description);
            setFlash('success', 'Fasilitas berhasil ditambahkan.');
        }

        redirect('/admin/facilities');
    }

    /**
     * POST /admin/facilities/deactivate — "hapus" versi aman.
     * Tidak pernah DELETE baris; hanya set status INACTIVE.
     */
    public function deactivateFacility(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $facility = $id ? Facility::findById($id) : null;

        if (!$facility) {
            setFlash('error', 'Fasilitas tidak ditemukan.');
            redirect('/admin/facilities');
        }

        Facility::deactivate($id);

        $note = Facility::isReferenced($id)
            ? ' (fasilitas ini memiliki riwayat reservasi/laporan, data historis tetap tersimpan)'
            : '';
        setFlash('success', 'Fasilitas dinonaktifkan.' . $note);
        redirect('/admin/facilities');
    }

    // ---------------------------------------------------------------
    // ANALYTICS & EXPORT
    // ---------------------------------------------------------------

    /** GET /admin/analytics — dashboard analytics dengan filter periode. */
    public function analytics(): void
    {
        $dateFrom = $_GET['from'] ?? null;
        $dateTo = $_GET['to'] ?? null;

        $occupancy = Reservation::occupancyByFacility($dateFrom, $dateTo);
        $damageFrequency = Report::frequencyByFacilityAndCategory($dateFrom, $dateTo);

        require __DIR__ . '/../../views/admin/analytics.php';
    }

    /** GET /admin/analytics/export — export CSV occupancy (baseline termudah). */
    public function exportOccupancy(): void
    {
        $dateFrom = $_GET['from'] ?? null;
        $dateTo = $_GET['to'] ?? null;
        $rows = Reservation::occupancyByFacility($dateFrom, $dateTo);
        exportCsv('occupancy_' . date('Ymd_His') . '.csv', $rows);
    }

    /** GET /admin/analytics/export-damage — export CSV frekuensi kerusakan. */
    public function exportDamage(): void
    {
        $dateFrom = $_GET['from'] ?? null;
        $dateTo = $_GET['to'] ?? null;
        $rows = Report::frequencyByFacilityAndCategory($dateFrom, $dateTo);
        exportCsv('kerusakan_' . date('Ymd_His') . '.csv', $rows);
    }
}
