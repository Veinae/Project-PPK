<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?><?= isset($pageTitle) ? ' — ' . $pageTitle : '' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <span class="brand">🏫 FACILIA</span>
    <div>
        <a href="<?= BASE_URL ?>/facilities">Fasilitas</a>
        <?php if (isLoggedIn()): ?>
            <?php if (currentRole() === 'ADMIN'): ?>
                <a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a>
                <a href="<?= BASE_URL ?>/admin/users">Users</a>
                <a href="<?= BASE_URL ?>/admin/facilities">Kelola Fasilitas</a>
                <a href="<?= BASE_URL ?>/admin/analytics">Analytics</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/reservation/history">Riwayat Reservasi</a>
            <a href="<?= BASE_URL ?>/logout">Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login">Login</a>
            <a href="<?= BASE_URL ?>/register">Register</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container">
    <?php $flash = getFlash(); ?>
    <?php if (!empty($flash['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($flash['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash['errors'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($flash['errors']) ?></div>
    <?php endif; ?>
