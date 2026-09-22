<?php

class User
{
    /** Cari user berdasarkan email (dipakai saat login & cek unique). */
    public static function findByEmail(string $email): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Registrasi mandiri: selalu role USER, verification_status PENDING. */
    public static function createSelfRegister(string $name, string $email, string $hashedPassword): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO users (name, email, password, role, verification_status)
             VALUES (?, ?, ?, \'USER\', \'PENDING\')'
        );
        $stmt->execute([$name, $email, $hashedPassword]);
        return (int) getDB()->lastInsertId();
    }

    /** Dibuat langsung oleh admin: role & verification_status bebas ditentukan admin. */
    public static function createByAdmin(string $name, string $email, string $hashedPassword, string $role, string $verificationStatus): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO users (name, email, password, role, verification_status)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hashedPassword, $role, $verificationStatus]);
        return (int) getDB()->lastInsertId();
    }

    /** Daftar user dengan filter opsional verification_status, untuk admin. */
    public static function all(?string $verificationStatus = null): array
    {
        if ($verificationStatus) {
            $stmt = getDB()->prepare('SELECT * FROM users WHERE verification_status = ? ORDER BY created_at DESC');
            $stmt->execute([$verificationStatus]);
        } else {
            $stmt = getDB()->query('SELECT * FROM users ORDER BY created_at DESC');
        }
        return $stmt->fetchAll();
    }

    public static function updateVerificationStatus(int $id, string $status): void
    {
        $stmt = getDB()->prepare('UPDATE users SET verification_status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function countByRole(string $role): int
    {
        $stmt = getDB()->prepare('SELECT COUNT(*) AS total FROM users WHERE role = ?');
        $stmt->execute([$role]);
        return (int) $stmt->fetch()['total'];
    }
}
