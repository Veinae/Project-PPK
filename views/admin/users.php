<?php $pageTitle = 'Manajemen User'; require __DIR__ . '/../layouts/header.php'; ?>

<h1>Manajemen User</h1>

<div class="filter-bar">
    <a class="btn <?= empty($filter) ? '' : 'btn-secondary' ?>" href="<?= BASE_URL ?>/admin/users">Semua</a>
    <a class="btn <?= $filter === 'PENDING' ? '' : 'btn-secondary' ?>" href="<?= BASE_URL ?>/admin/users?status=PENDING">Pending</a>
    <a class="btn <?= $filter === 'VERIFIED' ? '' : 'btn-secondary' ?>" href="<?= BASE_URL ?>/admin/users?status=VERIFIED">Verified</a>
    <a class="btn <?= $filter === 'REJECTED' ? '' : 'btn-secondary' ?>" href="<?= BASE_URL ?>/admin/users?status=REJECTED">Rejected</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><span class="badge badge-<?= $u['verification_status'] ?>"><?= $u['verification_status'] ?></span></td>
                <td>
                    <?php if ($u['verification_status'] === 'PENDING'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/users/verify" style="display:inline">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="decision" value="VERIFIED">
                            <button type="submit">Approve</button>
                        </form>
                        <form method="POST" action="<?= BASE_URL ?>/admin/users/verify" style="display:inline">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="decision" value="REJECTED">
                            <button type="submit" class="btn-danger">Reject</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" style="max-width:420px;">
    <h2>Buat Akun Langsung</h2>
    <form method="POST" action="<?= BASE_URL ?>/admin/users/create">
        <div class="form-group"><label>Nama</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" minlength="8" required></div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                <option value="USER">USER</option>
                <option value="PETUGAS">PETUGAS</option>
                <option value="ADMIN">ADMIN</option>
            </select>
        </div>
        <button type="submit">Buat Akun</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
