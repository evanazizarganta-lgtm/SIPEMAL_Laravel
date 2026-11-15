# SIPEMAL — Sistem Peminjaman Alat

Aplikasi web peminjaman alat sekolah berbasis **Laravel 12** (PHP 8.2+, MySQL).
Peran pengguna: **Admin** (kelola alat, kategori, persetujuan, pengembalian, laporan, admin lain) dan **Siswa** (lihat katalog, ajukan dan pantau peminjaman).

---

## 1. Persiapan (sekali saja)

| Kebutuhan | Keterangan |
|---|---|
| **XAMPP** dengan PHP **8.2 atau lebih baru** | Nyalakan modul **Apache** dan **MySQL** di XAMPP Control Panel. |
| **Composer** | Unduh di https://getcomposer.org lalu pasang. Cek di PowerShell: `composer --version` |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `xml` (semua sudah aktif di XAMPP bawaan). |

> Jika `php` tidak dikenali di PowerShell, tambahkan `C:\xampp\php` ke **Environment Variables → Path**, lalu buka PowerShell baru.

## 2. Instalasi

1. **Ekstrak** ZIP. Folder `SIPEMAL_Laravel` (berisi `artisan` dan `composer.json`) boleh diletakkan di mana saja, misalnya `C:\xampp\htdocs\SIPEMAL_Laravel`.
2. **Buat database.** Buka http://localhost/phpmyadmin → **New** → nama `sipemal` → collation `utf8mb4_unicode_ci` → **Create**.
3. Buka **PowerShell di dalam folder proyek** (`cd C:\xampp\htdocs\SIPEMAL_Laravel`), lalu jalankan berurutan:

```powershell
composer install                      # mengunduh folder vendor (butuh internet, beberapa menit)
Copy-Item .env.example .env           # membuat file konfigurasi
php artisan key:generate              # membuat APP_KEY
```

4. **Cek `.env`** (buka dengan Notepad). Bawaan XAMPP sudah cocok:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipemal
DB_USERNAME=root
DB_PASSWORD=
```

5. Lanjutkan di PowerShell:

```powershell
php artisan migrate                   # membuat semua tabel
php artisan db:seed                   # akun demo + contoh kategori & alat
php artisan storage:link              # agar foto alat bisa tampil (lihat catatan di bawah)
php artisan serve                     # menjalankan server
```

6. Buka **http://127.0.0.1:8000** di browser.

> **Jangan** memakai `migrate:fresh` / `migrate:refresh` — perintah itu **menghapus semua data**.
> `storage:link` bersifat opsional: bila gagal di Windows (butuh hak Administrator), foto alat tetap tampil lewat route cadangan `/gambar-alat/...`.
> Seeder memakai `updateOrCreate` untuk akun demo, jadi menjalankannya ulang akan **mengembalikan password dua akun demo** ke bawaan. Jalankan sekali saja di database baru.

## 3. Akun demo (dibuat oleh seeder)

| Peran | Email | Kata sandi |
|---|---|---|
| Admin | `admin@sipemal.test` | `admin12345` |
| Siswa | `siswa@sipemal.test` | `siswa12345` |

**Ganti password akun demo sebelum dipakai sungguhan** (menu Profil). Admin tambahan dibuat lewat menu **Manajemen Admin**; siswa mendaftar sendiri lewat halaman Register.

## 4. Memperbarui dari versi lama (database sudah berisi data)

```powershell
composer install
php artisan migrate        # hanya menambah kolom alats.gambar (jika belum ada); data aman
php artisan storage:link
php artisan view:clear
```
Alternatif tanpa artisan: jalankan isi `database/update_tambah_kolom_gambar.sql` di phpMyAdmin.

## 5. Fitur utama

- **Admin:** dashboard (statistik, grafik, aktivitas), data alat (dengan **upload foto**), kategori, persetujuan/penolakan peminjaman, pengembalian (kondisi & denda), laporan + ekspor CSV + cetak, data siswa, **Manajemen Admin** (tambah admin baru), profil.
- **Siswa:** dashboard, katalog (cari, filter kategori/kondisi, pagination), ajukan peminjaman, pantau status, profil.
- **Foto alat:** diunggah Admin di Tambah/Edit Alat (JPG/JPEG/PNG/WebP, maks. 2 MB). Tanpa foto, kartu menampilkan **ilustrasi lokal** berlabel "Ilustrasi · belum ada foto" (laptop, proyektor, kamera, perkakas) atau placeholder netral. Foto Admin selalu menggantikan ilustrasi.

### Mengubah warna tema
Semua warna ada di bagian `:root` pada `public/css/sipemal.css` (`--brand-*` biru utama, `--sky-*` biru muda, `--accent-*` teal, `--ok/--amber/--red` status). Ganti nilainya di sana, simpan, lalu muat ulang browser (Ctrl+F5).

## 6. Pemecahan masalah

| Gejala | Solusi |
|---|---|
| `composer` / `php` tidak dikenali | Pasang Composer; tambahkan `C:\xampp\php` ke Path; buka PowerShell baru. |
| `could not find driver` | Di `C:\xampp\php\php.ini` pastikan `extension=pdo_mysql` tidak diawali `;`, restart Apache. |
| `Access denied for user 'root'` / `Unknown database` | Cocokkan `DB_*` di `.env`; pastikan database `sipemal` sudah dibuat; jalankan `php artisan config:clear`. |
| `No application encryption key` | Jalankan `php artisan key:generate`. |
| Error "valid cache path" / "bootstrap/cache must be writable" | Pastikan folder `storage` dan `bootstrap\cache` ada dan tidak read-only. |
| Foto tidak muncul | Jalankan `php artisan storage:link` (atau biarkan; ada route cadangan). Pastikan file ada di `storage\app\public\alat`. |
| Upload foto gagal / "berkas terlalu besar" | Di `php.ini` naikkan `upload_max_filesize` dan `post_max_size` (minimal `4M`), restart Apache. |
| Halaman "419 Sesi berakhir" | Muat ulang halaman lalu kirim ulang formulir. |
| Port 8000 dipakai | `php artisan serve --port=8001` lalu buka http://127.0.0.1:8001 |
| Tampilan tidak berubah setelah edit CSS/JS | Tekan **Ctrl+F5**; jalankan `php artisan view:clear`. |

## 7. Menjalankan tes otomatis (opsional)

```powershell
vendor\bin\phpunit
```
Tes memakai SQLite in-memory (butuh ekstensi `pdo_sqlite`, sudah aktif di XAMPP) dan **tidak menyentuh** database MySQL Anda. Ada 30 tes (login, akses per peran, CRUD, alur pinjam→setuju→kembali, upload foto, Manajemen Admin, validasi Indonesia, tema).

## 8. Struktur singkat

```
app/Http/Controllers/   logika halaman (AlatController, AdminController, PeminjamanController, ...)
app/Models/             model database (Alat, User, Peminjaman, ...)
app/View/Composers/     data bersama layout (breadcrumb, notifikasi)
resources/views/        tampilan Blade (layouts/, components/, admin/, siswa/, auth/, errors/)
public/css/sipemal.css  seluruh gaya & warna tema
public/js/sipemal.js    interaksi (sidebar, modal, tabel, upload)
public/images/          ilustrasi lokal & placeholder
lang/id/validation.php  pesan validasi bahasa Indonesia
routes/web.php          semua alamat (route)
database/               migrasi, seeder, SQL
tests/Feature/          tes otomatis
```

Penjelasan tiap file yang diubah ada di **PENJELASAN_FILE.md**.
