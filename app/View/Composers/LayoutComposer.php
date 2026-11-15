<?php

namespace App\View\Composers;

use App\Models\Peminjaman;
use Illuminate\View\View;

/**
 * Menyuplai data bersama untuk layout aplikasi:
 * breadcrumb, judul halaman, jumlah/isi notifikasi.
 */
class LayoutComposer
{
    /** route name => [[label, route-name-induk|null], ...] (crumb terakhir = halaman aktif) */
    private const CRUMBS = [
        'admin.dashboard'          => [['Dashboard', null]],
        'admin.alat.index'         => [['Data Alat', null]],
        'admin.alat.create'        => [['Data Alat', 'admin.alat.index'], ['Tambah alat', null]],
        'admin.alat.edit'          => [['Data Alat', 'admin.alat.index'], ['Edit alat', null]],
        'admin.kategori.index'     => [['Kategori', null]],
        'admin.kategori.create'    => [['Kategori', 'admin.kategori.index'], ['Tambah kategori', null]],
        'admin.kategori.edit'      => [['Kategori', 'admin.kategori.index'], ['Edit kategori', null]],
        'admin.peminjaman.index'   => [['Peminjaman', null]],
        'admin.peminjaman.show'    => [['Peminjaman', 'admin.peminjaman.index'], ['Detail pengajuan', null]],
        'admin.pengembalian.index' => [['Pengembalian', null]],
        'admin.siswa.index'        => [['Data Siswa', null]],
        'admin.admins.index'       => [['Manajemen Admin', null]],
        'admin.admins.create'      => [['Manajemen Admin', 'admin.admins.index'], ['Tambah admin', null]],
        'admin.laporan.index'      => [['Laporan', null]],
        'admin.profil'             => [['Profil', null]],
        'siswa.dashboard'          => [['Dashboard', null]],
        'siswa.katalog'            => [['Katalog Alat', null]],
        'siswa.peminjaman.index'   => [['Peminjaman Saya', null]],
        'siswa.peminjaman.create'  => [['Peminjaman Saya', 'siswa.peminjaman.index'], ['Ajukan peminjaman', null]],
        'siswa.peminjaman.show'    => [['Peminjaman Saya', 'siswa.peminjaman.index'], ['Detail', null]],
        'siswa.profil'             => [['Profil', null]],
        'admin.kelas.index' => [
    ['Manajemen Kelas', null],
],

'admin.nis.index' => [
    ['NIS Resmi', null],
],

'admin.nis.create' => [
    ['NIS Resmi', 'admin.nis.index'],
    ['Tambah NIS', null],
],
    ];

    public function compose(View $view): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $isAdmin = $user->role === 'admin';
        $route   = request()->route()?->getName() ?? '';

        // ---- breadcrumb ----
        $crumbs = [];
        $def = self::CRUMBS[$route] ?? [[ucfirst((string) last(explode('.', $route))) ?: 'SIPEMAL', null]];
        $home = route($isAdmin ? 'admin.dashboard' : 'siswa.dashboard');
        if (! in_array($route, ['admin.dashboard', 'siswa.dashboard'], true)) {
            $crumbs[] = ['label' => 'Beranda', 'url' => $home];
        }
        foreach ($def as [$label, $parent]) {
            $crumbs[] = ['label' => $label, 'url' => $parent ? route($parent) : null];
        }
        $pageTitle = end($crumbs)['label'];

        // ---- notifikasi ----
        $notifs = [];
        $count  = 0;
        $badge  = 0; // lencana menu sidebar (Peminjaman)
        $today  = now()->startOfDay();

        if ($isAdmin) {
            $menunggu = Peminjaman::where('status_peminjaman', 'Menunggu');
            $badge = (clone $menunggu)->count();
            foreach ((clone $menunggu)->with('siswa', 'details.alat')->latest()->take(4)->get() as $p) {
                $notifs[] = [
                    'tone' => 'warn', 'icon' => 'clock',
                    'title' => ($p->siswa->name ?? 'Siswa') . ' mengajukan peminjaman',
                    'text' => $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat') . ' ×' . $d->jumlah_pinjam)->implode(', '),
                    'time' => $p->created_at?->diffForHumans(), 'url' => route('admin.peminjaman.show', $p),
                ];
            }
            $late = Peminjaman::with('siswa')->where('status_peminjaman', 'Disetujui')->whereDate('tanggal_rencana_kembali', '<', $today);
            $lateCount = (clone $late)->count();
            foreach ((clone $late)->orderBy('tanggal_rencana_kembali')->take(3)->get() as $p) {
                $notifs[] = [
                    'tone' => 'bad', 'icon' => 'alert',
                    'title' => 'Pengembalian terlambat',
                    'text' => ($p->siswa->name ?? 'Siswa') . ' · seharusnya ' . $p->tanggal_rencana_kembali->translatedFormat('d M Y'),
                    'time' => null, 'url' => route('admin.pengembalian.index'),
                ];
            }
            $count = $badge + $lateCount;
        } else {
            $mine = Peminjaman::where('user_id', $user->id);
            $recent = (clone $mine)->whereIn('status_peminjaman', ['Disetujui', 'Ditolak', 'Dikembalikan'])
                ->where('updated_at', '>=', now()->subDays(7));
            $count = (clone $recent)->count();
            $map = [
                'Disetujui'    => ['ok', 'check-circle', 'Pengajuan disetujui'],
                'Ditolak'      => ['bad', 'x-circle', 'Pengajuan ditolak'],
                'Dikembalikan' => ['info', 'undo', 'Alat sudah dikembalikan'],
            ];
            foreach ((clone $recent)->with('details.alat')->latest('updated_at')->take(5)->get() as $p) {
                [$tone, $icon, $title] = $map[$p->status_peminjaman];
                $notifs[] = [
                    'tone' => $tone, 'icon' => $icon, 'title' => $title,
                    'text' => $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat') . ' ×' . $d->jumlah_pinjam)->implode(', '),
                    'time' => $p->updated_at?->diffForHumans(), 'url' => route('siswa.peminjaman.show', $p),
                ];
            }
            $badge = (clone $mine)->where('status_peminjaman', 'Disetujui')->count();
        }

        $view->with([
            'crumbs'     => $crumbs,
            'pageTitle'  => $pageTitle,
            'navNotifs'  => $notifs,
            'navNotifCount' => $count,
            'navBadge'   => $badge,
        ]);
    }
}
