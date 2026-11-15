
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlatController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\NisResmiController;

Route::get('/', fn () => redirect()->route('login'));

// ======================================
// LOGIN DAN REGISTER
// ======================================

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'authenticate'])
        ->name('login.authenticate');

    Route::get('/register', [AuthController::class, 'register'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'storeRegister'])
        ->name('register.store');

    Route::post('/check-nis', [AuthController::class, 'checkNis'])
        ->name('register.check-nis');
});

// ======================================
// GAMBAR ALAT
// ======================================

Route::get('/gambar-alat/{file}', [AlatController::class, 'gambar'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('alat.gambar');

// ======================================
// LOGOUT
// ======================================

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ======================================
// AREA SISWA
// ======================================

Route::middleware(['auth', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'siswa'])
            ->name('dashboard');

        Route::get('/katalog', [AlatController::class, 'katalog'])
            ->name('katalog');

        Route::get('/profil', [ProfilController::class, 'siswa'])
            ->name('profil');

        Route::put('/profil', [ProfilController::class, 'update'])
            ->name('profil.update');

        Route::get('/peminjaman', [PeminjamanController::class, 'indexSiswa'])
            ->name('peminjaman.index');

        Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])
            ->name('peminjaman.create');

        Route::post('/peminjaman', [PeminjamanController::class, 'store'])
            ->name('peminjaman.store');

        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'showSiswa'])
            ->name('peminjaman.show');
    });

// ======================================
// AREA ADMIN
// ======================================

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'admin'])
            ->name('dashboard');

        // Inventaris.
        Route::resource('/alat', AlatController::class)
            ->except(['show'])
            ->names('alat');

        Route::resource('/kategori', KategoriController::class)
            ->except(['show'])
            ->names('kategori');

        // Manajemen kelas.
        Route::get('/kelas', [KelasController::class, 'index'])
            ->name('kelas.index');

        Route::post('/kelas', [KelasController::class, 'store'])
            ->name('kelas.store');

        Route::patch('/kelas/{kelas}/toggle', [KelasController::class, 'toggle'])
            ->name('kelas.toggle');

        // Manajemen NIS resmi.
        Route::get('/nis', [NisResmiController::class, 'index'])
            ->name('nis.index');

        Route::get('/nis/create', [NisResmiController::class, 'create'])
            ->name('nis.create');

        Route::post('/nis', [NisResmiController::class, 'store'])
            ->name('nis.store');

        Route::patch('/nis/{nisResmi}/toggle', [NisResmiController::class, 'toggle'])
            ->name('nis.toggle');

        // Peminjaman.
        Route::get('/peminjaman', [PeminjamanController::class, 'index'])
            ->name('peminjaman.index');

        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])
            ->name('peminjaman.show');

        Route::patch('/peminjaman/{peminjaman}/status', [PeminjamanController::class, 'updateStatus'])
            ->name('peminjaman.status');

        // Pengembalian.
        Route::get('/pengembalian', [PengembalianController::class, 'index'])
            ->name('pengembalian.index');

        Route::post('/pengembalian', [PengembalianController::class, 'store'])
            ->name('pengembalian.store');

        // Data siswa.
        Route::get('/siswa', [DashboardController::class, 'siswaList'])
            ->name('siswa.index');

        // Manajemen Admin.
        Route::get('/admins', [AdminController::class, 'index'])
            ->name('admins.index');

        Route::get('/admins/create', [AdminController::class, 'create'])
            ->name('admins.create');

        Route::post('/admins', [AdminController::class, 'store'])
            ->name('admins.store');

        // Laporan.
        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan.index');

        Route::get('/laporan/ekspor', [LaporanController::class, 'export'])
            ->name('laporan.export');

        // Profil.
        Route::get('/profil', [ProfilController::class, 'admin'])
            ->name('profil');

        Route::put('/profil', [ProfilController::class, 'update'])
            ->name('profil.update');
    });
