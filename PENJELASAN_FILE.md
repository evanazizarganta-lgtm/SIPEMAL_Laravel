# Penjelasan File yang Diubah (untuk belajar)

Semua route lama dan fungsi lama **tetap ada**. Penambahan route baru: `admin.admins.index/create/store` (Manajemen Admin) dan `alat.gambar` (penyaji foto cadangan).

## A. Tema & tampilan
| File | Fungsi |
|---|---|
| `public/css/sipemal.css` | Seluruh gaya. Bagian `:root` di atas = **token warna** (biru `#2563EB`, biru muda `#DBEAFE`, latar `#F5F9FF`, teks `#172B4D`, aksen teal `#0D9488`). Ubah warna cukup di sana. Ada juga grid dashboard (`.dash`, `.span-8`), aturan hamburger, dan gaya login/register. |
| `public/js/sipemal.js` | Interaksi: sidebar mobile, dropdown, modal konfirmasi, toast, cari/filter/paginasi tabel, tombol loading, toggle password, pratinjau upload foto. Titik pindah desktop↔mobile = **1023 px**. |
| `resources/views/layouts/app.blade.php` | Kerangka halaman setelah login: sidebar, navbar (breadcrumb, notifikasi, profil), navigasi bawah HP. Item menu **Manajemen Admin** ada di sini. |
| `resources/views/layouts/auth.blade.php` | Kerangka login/register (panel ilustrasi + kartu formulir). |
| `resources/views/components/*.blade.php` | Komponen berulang: `x-icon`, `x-logo`, `x-status-badge`, `x-alat-card`, `x-field`, `x-modal`, `x-image-upload`, `x-delete-form`, dll. |
| `resources/views/admin/dashboard.blade.php`, `siswa/dashboard.blade.php` | Dashboard disusun ulang dengan grid 12 kolom supaya kartu satu baris sama tinggi. |
| `resources/views/auth/login.blade.php`, `register.blade.php` | Formulir login/register (fungsi & validasi tidak berubah). |
| `resources/views/errors/*.blade.php` | Halaman 403/404/413/419/429/500/503 bertema biru. |
| `public/images/ilustrasi/hero-siswa.svg`, `public/favicon.svg` | Ilustrasi banner/login dan ikon tab (logo huruf **S**). |

## B. Manajemen Admin (fitur baru)
| File | Fungsi |
|---|---|
| `app/Http/Controllers/AdminController.php` | `index` (daftar admin), `create` (form), `store` (simpan). Hanya menerima nama, email, kata sandi. **Role `admin` diisi server**; password di-hash otomatis oleh model `User` (cast `hashed`); email harus unik. |
| `routes/web.php` | 3 route baru di dalam grup `role:admin` — siswa dan tamu otomatis ditolak (403 / diarahkan ke login). |
| `resources/views/admin/admins/index.blade.php`, `create.blade.php` | Daftar dan formulir tambah admin. |
| `app/View/Composers/LayoutComposer.php` | Menambah judul/breadcrumb untuk halaman baru. |

## C. Foto alat
| File | Fungsi |
|---|---|
| `database/migrations/2026_10_21_000001_add_gambar_to_alats_table.php` | Menambah kolom `alats.gambar` (nullable, aman untuk data lama). |
| `app/Models/Alat.php` | `fotoUrl()`/`gambar_url`: foto Admin → ilustrasi lokal → placeholder. `ada_gambar` = true hanya bila foto asli ada. |
| `app/Http/Controllers/AlatController.php` | Simpan/ganti/hapus foto di `storage/app/public/alat`, validasi, bersihkan file lama; `gambar()` penyaji cadangan; katalog 12 kartu per halaman. |
| `resources/views/components/image-upload.blade.php`, `alat-card.blade.php`, `alat-image.blade.php` | Input upload + pratinjau, kartu katalog, tag gambar dengan fallback. |
| `public/images/ilustrasi-alat/*.svg`, `public/images/placeholder-alat.svg` | Ilustrasi lokal sederhana & placeholder netral (hanya dipakai bila belum ada foto). |

## D. Lain-lain
| File | Fungsi |
|---|---|
| `lang/id/validation.php` | Semua pesan validasi berbahasa Indonesia + nama kolom ramah. |
| `app/Http/Controllers/DashboardController.php` | Data dashboard (jumlah daftar aktivitas/populer disesuaikan tata letak baru). |
| `database/update_tambah_kolom_gambar.sql`, `database/sipemal.sql` | SQL alternatif untuk kolom `gambar`. |
| `tests/Feature/UiPagesTest.php`, `tests/TestCase.php`, `phpunit.xml` | 30 tes otomatis. |
| `bootstrap/cache/`, `storage/...` | Folder runtime Laravel yang wajib ada (berisi `.gitignore`). |

## E. Daftar pengujian
**Sudah diuji (nyata dijalankan):**
- PHPUnit 30 tes / 228 assertion pada SQLite: login, akses Admin vs Siswa, CRUD alat & kategori, pinjam→setuju→kembali, laporan & ekspor, upload/ganti/hapus foto, validasi, Manajemen Admin (hash password, role dipaksa admin, email unik, siswa ditolak 403, tamu diarahkan login), pesan validasi Indonesia, token tema.
- Browser Chromium headless: 23 halaman × 16 ukuran layar (320–1920 px, landscape HP, zoom 125/150/200%) tanpa overflow horizontal; hamburger tersembunyi ≥1024 px dan tampil ≤1023 px; drawer tablet/HP; login/register muat tanpa gulir di 1024×600 sampai 1920×969; upload foto di browser; dialog hapus; alur tambah admin; akses siswa ke endpoint admin = 403.
- Pembaruan dari database lama yang berisi data: migrasi menambah kolom tanpa menghapus data.

**Belum diuji:**
- **MySQL/XAMPP di Windows** (pengujian memakai SQLite di Linux). Langkah uji di Windows: ikuti README bagian 2, login dengan akun demo, tambah alat berfoto, tambah admin, ajukan→setujui→kembalikan satu peminjaman.
- Browser selain Chromium (Safari, Firefox), HP fisik, dan hasil cetak/PDF dari tombol Cetak laporan.
- Font Plus Jakarta Sans (dimuat dari internet; tanpa internet dipakai font sistem).
