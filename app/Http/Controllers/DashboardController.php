<?php
namespace App\Http\Controllers;
use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin()
    {
        $today = now()->startOfDay();

        // Grafik 6 bulan terakhir (dihitung di PHP agar portabel MySQL/SQLite).
        $start = now()->startOfMonth()->subMonths(5);
        $pengajuan = Peminjaman::where('tanggal_pengajuan', '>=', $start->toDateString())->get(['tanggal_pengajuan'])
            ->groupBy(fn ($p) => $p->tanggal_pengajuan->format('Y-m'))->map->count();
        $kembali = Pengembalian::where('tanggal_pengembalian', '>=', $start->toDateString())->get(['tanggal_pengembalian'])
            ->groupBy(fn ($p) => $p->tanggal_pengembalian->format('Y-m'))->map->count();
        $chart = [];
        for ($i = 0; $i < 6; $i++) {
            $m = $start->copy()->addMonths($i);
            $chart[] = [
                'label' => $m->copy()->locale('id')->isoFormat('MMM'),
                'pengajuan' => (int) ($pengajuan[$m->format('Y-m')] ?? 0),
                'kembali' => (int) ($kembali[$m->format('Y-m')] ?? 0),
            ];
        }

        $statusCounts = Peminjaman::select('status_peminjaman', DB::raw('count(*) as total'))
            ->groupBy('status_peminjaman')->pluck('total', 'status_peminjaman');

        $aktivitas = Peminjaman::with(['siswa', 'details.alat'])->latest('updated_at')->take(5)->get();

        $populer = DetailPeminjaman::select('alat_id', DB::raw('SUM(jumlah_pinjam) as total'))
            ->groupBy('alat_id')->orderByDesc('total')->with('alat.kategori')->take(3)->get()
            ->filter(fn ($d) => $d->alat);

        $stokMenipis = Alat::with('kategori')->orderBy('nama_alat')->get()
            ->filter(fn ($a) => $a->jumlah > 0 && $a->tersedia <= 2)->sortBy('tersedia')->take(4)->values();

        return view('admin.dashboard', [
            'totalAlat'=>Alat::count(),
            'totalKategori'=>Kategori::count(),
            'totalSiswa'=>User::where('role','siswa')->count(),
            'peminjamanMenunggu'=>Peminjaman::where('status_peminjaman','Menunggu')->count(),
            'peminjamanTerbaru'=>Peminjaman::with(['siswa','details.alat'])->latest()->take(6)->get(),
            // tambahan visual (data nyata dari database)
            'peminjamanAktif'=>Peminjaman::where('status_peminjaman','Disetujui')->count(),
            'totalPengembalian'=>Pengembalian::count(),
            'peminjamanTerlambat'=>Peminjaman::where('status_peminjaman','Disetujui')->whereDate('tanggal_rencana_kembali','<',$today->toDateString())->count(),
            'chart'=>$chart,
            'statusCounts'=>$statusCounts,
            'aktivitas'=>$aktivitas,
            'alatPopuler'=>$populer,
            'stokMenipis'=>$stokMenipis,
        ]);
    }
    public function siswa()
    {
        $uid = auth()->id();
        $mine = Peminjaman::where('user_id', $uid);

        // Rekomendasi: alat paling sering dipinjam yang masih tersedia, dilengkapi alat terbaru.
        $populerIds = DetailPeminjaman::select('alat_id', DB::raw('SUM(jumlah_pinjam) as total'))
            ->groupBy('alat_id')->orderByDesc('total')->pluck('alat_id');
        $kandidat = Alat::with('kategori')->where('jumlah', '>', 0)->where('kondisi', 'Baik')->get()
            ->filter(fn ($a) => $a->tersedia > 0);
        $rekomendasi = $kandidat->sortBy(function ($a) use ($populerIds) {
            $pos = $populerIds->search($a->id);
            return $pos === false ? 10000 - $a->id : $pos;
        })->take(4)->values();

        return view('siswa.dashboard', [
            'totalAlat'=>Alat::count(),
            'totalPeminjaman'=>(clone $mine)->count(),
            'peminjamanMenunggu'=>(clone $mine)->where('status_peminjaman','Menunggu')->count(),
            // tambahan visual (data nyata dari database)
            'peminjamanAktif'=>(clone $mine)->where('status_peminjaman','Disetujui')->count(),
            'riwayatKembali'=>(clone $mine)->where('status_peminjaman','Dikembalikan')->count(),
            'rekomendasi'=>$rekomendasi,
            'alatTerbaru'=>Alat::with('kategori')->where('jumlah','>',0)->latest('id')->take(6)->get(),
            'peminjamanTerakhir'=>(clone $mine)->with(['details.alat','pengembalian'])->latest()->take(4)->get(),
            'jatuhTempo'=>(clone $mine)->with('details.alat')->where('status_peminjaman','Disetujui')->orderBy('tanggal_rencana_kembali')->first(),
        ]);
    }
    public function siswaList()
    {
        $siswas = User::where('role','siswa')->orderBy('name')->get();
        return view('admin.siswa.index', compact('siswas'));
    }
}
