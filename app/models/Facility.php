<?php

class Facility
{
    /** Status yang sah — sumber kebenaran tunggal untuk validasi whitelist. */
    public const STATUSES = ['ACTIVE', 'MAINTENANCE', 'INACTIVE'];

    public static function all(): array
    {
        return getDB()->query('SELECT * FROM facilities ORDER BY name ASC')->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM facilities WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Browse publik: hanya ACTIVE/MAINTENANCE yang ditampilkan (INACTIVE disembunyikan),
     * dengan search nama/lokasi, filter tipe, dan filter kapasitas minimum.
     */
    public static function searchPublic(?string $keyword, ?string $type, ?int $minCapacity): array
    {
        $sql = "SELECT * FROM facilities WHERE status IN ('ACTIVE', 'MAINTENANCE')";
        $params = [];

        if ($keyword) {
            $sql .= ' AND (name LIKE ? OR location LIKE ?)';
            $like = '%' . $keyword . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($type) {
            $sql .= ' AND type = ?';
            $params[] = $type;
        }
        if ($minCapacity) {
            $sql .= ' AND capacity >= ?';
            $params[] = $minCapacity;
        }

        $sql .= ' ORDER BY name ASC';

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Daftar nilai distinct kolom type, untuk dropdown filter. */
    public static function distinctTypes(): array
    {
        $stmt = getDB()->query('SELECT DISTINCT type FROM facilities ORDER BY type ASC');
        return array_column($stmt->fetchAll(), 'type');
    }

    public static function create(string $name, string $type, string $location, int $capacity, string $status, ?string $description): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO facilities (name, type, location, capacity, status, description)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $type, $location, $capacity, $status, $description]);
        return (int) getDB()->lastInsertId();
    }

    public static function update(int $id, string $name, string $type, string $location, int $capacity, string $status, ?string $description): void
    {
        $stmt = getDB()->prepare(
            'UPDATE facilities SET name = ?, type = ?, location = ?, capacity = ?, status = ?, description = ?
             WHERE id = ?'
        );
        $stmt->execute([$name, $type, $location, $capacity, $status, $description, $id]);
    }

    /**
     * Admin tidak boleh menghapus data historis yang masih direferensikan.
     * "Nonaktifkan" berarti set status INACTIVE, bukan DELETE baris.
     */
    public static function deactivate(int $id): void
    {
        $stmt = getDB()->prepare('UPDATE facilities SET status = \'INACTIVE\' WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function isReferenced(int $id): bool
    {
        $stmt = getDB()->prepare(
            'SELECT
                (SELECT COUNT(*) FROM reservations WHERE facility_id = ?) +
                (SELECT COUNT(*) FROM reports WHERE facility_id = ?) AS total'
        );
        $stmt->execute([$id, $id]);
        return (int) $stmt->fetch()['total'] > 0;
    }
}
