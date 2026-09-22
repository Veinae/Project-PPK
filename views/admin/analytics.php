<?php $pageTitle = 'Analytics'; require __DIR__ . '/../layouts/header.php'; ?>

<h1>Analytics</h1>

<form method="GET" class="filter-bar card">
    <div class="form-group">
        <label>Dari Tanggal</label>
        <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Sampai Tanggal</label>
        <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    </div>
    <button type="submit">Filter</button>
</form>

<div class="card">
    <h2>Occupancy Fasilitas <span style="font-weight:400;font-size:0.8rem;color:#6b7280;">(hanya reservasi APPROVED)</span></h2>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/analytics/export?<?= http_build_query($_GET) ?>">Export CSV</a>
    <table style="margin-top:12px;">
        <thead><tr><th>Fasilitas</th><th>Jumlah Reservasi Disetujui</th></tr></thead>
        <tbody>
        <?php foreach ($occupancy as $row): ?>
            <tr><td><?= htmlspecialchars($row['name']) ?></td><td><?= (int) $row['approved_count'] ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($occupancy)): ?>
            <tr><td colspan="2" style="color:#9ca3af;">Belum ada data.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Frekuensi Kerusakan per Fasilitas & Kategori</h2>
    <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/analytics/export-damage?<?= http_build_query($_GET) ?>">Export CSV</a>
    <table style="margin-top:12px;">
        <thead><tr><th>Fasilitas</th><th>Kategori</th><th>Jumlah Laporan</th></tr></thead>
        <tbody>
        <?php foreach ($damageFrequency as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['facility_name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= (int) $row['total'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($damageFrequency)): ?>
            <tr><td colspan="3" style="color:#9ca3af;">Belum ada data.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
