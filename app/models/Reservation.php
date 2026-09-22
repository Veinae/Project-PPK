<?php

class Reservation
{
    /**
     * Ambil semua reservasi APPROVED untuk facility+tanggal tertentu.
     * Dipakai untuk: (1) menyusun grid availability, (2) cek overlap saat submit/approve.
     */
    public static function approvedByFacilityAndDate(int $facilityId, string $date): array
    {
        $stmt = getDB()->prepare(
            "SELECT start_time, end_time FROM reservations
             WHERE facility_id = ? AND reservation_date = ? AND status = 'APPROVED'
             ORDER BY start_time ASC"
        );
        $stmt->execute([$facilityId, $date]);
        return $stmt->fetchAll();
    }

    /**
     * Cek apakah rentang start-end yang diminta bentrok dengan reservasi APPROVED lain
     * pada facility+tanggal yang sama. $excludeId dipakai saat re-check approval
     * agar reservasi yang sedang diproses tidak membandingkan dengan dirinya sendiri.
     */
    public static function hasApprovedOverlap(int $facilityId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM reservations
                WHERE facility_id = ? AND reservation_date = ? AND status = 'APPROVED'
                  AND start_time < ? AND end_time > ?";
        $params = [$facilityId, $date, $endTime, $startTime];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'] > 0;
    }

    /** Simpan reservasi baru dengan status PENDING. */
    public static function create(int $userId, int $facilityId, string $date, string $startTime, string $endTime, ?string $purpose): int
    {
        $stmt = getDB()->prepare(
            "INSERT INTO reservations (user_id, facility_id, reservation_date, start_time, end_time, purpose, status)
             VALUES (?, ?, ?, ?, ?, ?, 'PENDING')"
        );
        $stmt->execute([$userId, $facilityId, $date, $startTime, $endTime, $purpose]);
        return (int) getDB()->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT r.*, f.name AS facility_name
             FROM reservations r JOIN facilities f ON f.id = r.facility_id
             WHERE r.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Riwayat reservasi milik satu user (dashboard/history). */
    public static function historyByUser(int $userId): array
    {
        $stmt = getDB()->prepare(
            'SELECT r.*, f.name AS facility_name
             FROM reservations r JOIN facilities f ON f.id = r.facility_id
             WHERE r.user_id = ?
             ORDER BY r.reservation_date DESC, r.start_time DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Cancel: hanya mengubah status, ownership dicek di controller sebelum memanggil ini. */
    public static function cancel(int $id, ?string $reason): void
    {
        $stmt = getDB()->prepare(
            "UPDATE reservations SET status = 'CANCELLED', cancellation_reason = ? WHERE id = ?"
        );
        $stmt->execute([$reason, $id]);
    }

    /** Queue PENDING untuk petugas (disiapkan di sini, dipakai penuh oleh Modul 3). */
    public static function pendingQueue(): array
    {
        $stmt = getDB()->query(
            "SELECT r.*, f.name AS facility_name, u.name AS user_name
             FROM reservations r
             JOIN facilities f ON f.id = r.facility_id
             JOIN users u ON u.id = r.user_id
             WHERE r.status = 'PENDING'
             ORDER BY r.reservation_date ASC, r.start_time ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Hitung occupancy per fasilitas dari reservations APPROVED saja
     * (PENDING tidak boleh dihitung — sesuai kontrak dokumen).
     */
    public static function occupancyByFacility(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "SELECT f.id, f.name,
                       COUNT(r.id) AS approved_count
                FROM facilities f
                LEFT JOIN reservations r
                       ON r.facility_id = f.id
                      AND r.status = 'APPROVED'";

        $params = [];
        $conditions = [];

        if ($dateFrom) {
            $conditions[] = 'r.reservation_date >= ?';
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $conditions[] = 'r.reservation_date <= ?';
            $params[] = $dateTo;
        }

        if ($conditions) {
            $sql .= ' AND ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY f.id, f.name ORDER BY approved_count DESC';

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
