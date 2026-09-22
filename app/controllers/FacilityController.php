<?php

require_once __DIR__ . '/../models/Facility.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../helpers/slots.php';

class FacilityController
{
    /** GET /facilities — daftar fasilitas ACTIVE/MAINTENANCE + search/filter. */
    public function index(): void
    {
        $keyword = trim($_GET['q'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $minCapacity = isset($_GET['min_capacity']) && $_GET['min_capacity'] !== ''
            ? (int) $_GET['min_capacity']
            : null;

        $facilities = Facility::searchPublic($keyword ?: null, $type ?: null, $minCapacity);
        $types = Facility::distinctTypes();

        require __DIR__ . '/../../views/public/facilities.php';
    }

    /**
     * GET /facility/detail?id=..&date=.. — detail fasilitas + availability tanggal terpilih.
     * Availability ditampilkan sebagai grid 30 menit (read-only, untuk bantu keputusan),
     * sedangkan reservasi sesungguhnya memakai jam mulai/selesai bebas di ReservationController.
     */
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $facility = $id ? Facility::findById($id) : null;

        if (!$facility) {
            http_response_code(404);
            echo 'Fasilitas tidak ditemukan.';
            return;
        }

        $date = $_GET['date'] ?? date('Y-m-d');
        // Jangan biarkan tanggal masa lalu jadi default availability yang membingungkan.
        if ($date < date('Y-m-d')) {
            $date = date('Y-m-d');
        }

        $approved = Reservation::approvedByFacilityAndDate($id, $date);
        $availabilityGrid = buildAvailabilityGrid($approved);
        $timeOptions = generateTimeBoundaries();

        require __DIR__ . '/../../views/public/facility-detail.php';
    }
}
