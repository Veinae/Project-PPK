<?php

/** Validasi format email sederhana. */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Password minimal 8 karakter (sesuaikan kebijakan tim bila perlu). */
function isValidPassword(string $password): bool
{
    return strlen($password) >= 8;
}

/** Cek nilai ada di whitelist (dipakai untuk role, status, dsb). */
function isInWhitelist(string $value, array $whitelist): bool
{
    return in_array($value, $whitelist, true);
}

/** Validasi capacity: harus integer dan > 0. */
function isValidCapacity($value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false && (int) $value > 0;
}

/** Bersihkan string input dasar (trim + strip tags). */
function cleanInput(string $value): string
{
    return trim(strip_tags($value));
}
