# FACILIA — Sistem Reservasi & Pelaporan Fasilitas Kampus

Implementasi **Modul 1: Auth & Admin** sesuai dokumen *Workflow End-to-End PPK*.
Struktur folder mengikuti kontrak dokumen (PHP-MVC ringan, bukan framework Laravel penuh) agar bisa langsung di-merge dengan Modul 2 dan Modul 3 milik rekan tim.

## Yang sudah selesai (Modul 1)

**Fase 3 — Auth & Role Security**
- Register mandiri → `role=USER`, `verification_status=PENDING`
- Password di-hash dengan `password_hash()`, verifikasi dengan `password_verify()`
- Login menolak akun PENDING/REJECTED
- Session `user_id`, `role`, `name` sesuai kontrak
- Logout menghancurkan session
- `AuthMiddleware`, `AdminMiddleware`, `PetugasMiddleware` (cek server-side, bukan cuma sembunyikan tombol)

**Fase 8 — Admin Master Data & Analytics**
- Daftar user + filter `verification_status`
- Approve/reject user PENDING
- Admin membuat akun (USER/PETUGAS/ADMIN) langsung — auto VERIFIED
- CRUD fasilitas: create, edit, "hapus" = set `INACTIVE` (data historis tidak pernah di-DELETE)
- Validasi capacity integer > 0, status & role whitelist di server
- Analytics: occupancy dari reservasi **APPROVED** saja, frekuensi kerusakan per fasilitas/kategori, filter tanggal
- Export CSV untuk kedua analytics

**Fase 1–2 (bagian yang dibutuhkan Modul 1)**
- `database/schema.sql`: tabel `users`, `facilities` (lengkap) + `reservations`, `reports` (stub minimal untuk FK & analytics — dikembangkan penuh oleh Modul 2/3)
- `database/seed.sql`: 1 admin, 1 petugas, 2 user, 5 fasilitas

**Fase 4 — Public Facility & Availability**
- Browse fasilitas ACTIVE/MAINTENANCE dengan search nama/lokasi, filter tipe, filter kapasitas minimum
- Detail fasilitas + grid availability visual per 30 menit untuk tanggal terpilih

**Fase 5 — Reservation Lifecycle (rentang jam bebas)**
- **User memilih jam mulai & jam selesai sendiri** (bukan centang slot 30 menit satu-satu), sehingga satu reservasi bisa mencakup beberapa slot sekaligus — contoh: 07:00–09:00 = 2 jam dalam satu pengajuan
- Validasi server tetap penuh mengikuti kontrak: tanggal tidak boleh lampau, 07:00 ≤ jam ≤ 20:00, menit hanya 00/30, `start < end`, durasi kelipatan 30 menit, dan tidak boleh overlap dengan reservasi lain yang sudah **APPROVED** — overlap dicek langsung terhadap rentang yang dipilih (`app/helpers/slots.php` + `Reservation::hasApprovedOverlap()`), bukan per slot tunggal, jadi rentang panjang tetap valid selama tidak menabrak jadwal lain
- Riwayat reservasi per user (`/reservation/history`) + cancel dengan ownership check (403 jika bukan pemilik)
- Fasilitas berstatus MAINTENANCE/INACTIVE tidak bisa dipesan (ditolak di server, bukan cuma disembunyikan di UI)

## Belum dikerjakan (di luar scope Modul 1 & 2)
- Modul 3: queue petugas, approve/reject reservasi (dengan re-check overlap), laporan kerusakan, maintenance
- Fase 9–11: integrasi, QA, fitur WOW

Route `/petugas/*` **belum** didaftarkan di `routes/web.php` — sengaja dikosongkan agar tidak bentrok saat rekan tim menambahkan route Modul 3 mereka sendiri. Model `Reservation::pendingQueue()` sudah disiapkan sebagai titik awal untuk Modul 3.

## Setup lokal (XAMPP)

1. Salin folder `facilia/` ke `htdocs/`.
2. Buka phpMyAdmin, jalankan `database/schema.sql` lalu `database/seed.sql`.
3. Cek kredensial di `config/database.php` (default: `root` tanpa password).
4. Sesuaikan `BASE_URL` di `config/app.php` dengan path akses lokal Anda, mis. `/facilia/public`.
5. Pastikan `mod_rewrite` Apache aktif (dipakai `.htaccess` di folder `public/`).
6. Akses `http://localhost/facilia/public/login`.

**Akun demo** (password semua: `password123`):
| Role    | Email                  |
|---------|------------------------|
| ADMIN   | admin@facilia.test     |
| PETUGAS | petugas@facilia.test   |
| USER (verified) | user1@facilia.test |
| USER (pending)  | user2@facilia.test |

## Kontrak yang harus tetap sama dengan Modul 2 & 3
- Nama field/enum status: lihat `database/schema.sql`
- Session key: `user_id`, `role`, `name`
- Model `Facility.php` ini **satu-satunya versi canonical** — jangan dibuat ulang di modul lain
- Middleware role ada di `app/middleware/` — Modul 3 tinggal pakai `PetugasMiddleware`
