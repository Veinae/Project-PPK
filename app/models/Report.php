<?php

/**
 * Stub untuk Modul 1 (Admin Analytics).
 * Lifecycle laporan penuh (upload/proses/resolve) dikerjakan di Modul 3.
 */
class Report
{
    /** Frekuensi kerusakan per fasilitas & kategori, dengan filter periode opsional. */
    public static function frequencyByFacilityAndCategory(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "SELECT f.name AS facility_name, rp.category, COUNT(*) AS total
                FROM reports rp
                JOIN facilities f ON f.id = rp.facility_id";

        $conditions = [];
        $params = [];

        if ($dateFrom) {
            $conditions[] = 'rp.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $conditions[] = 'rp.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY f.name, rp.category ORDER BY total DESC';

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
