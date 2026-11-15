<?php
namespace App\Http\Controllers;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function export(Request $request) {
        $from = $request->date('from');
        $to = $request->date('to');
        $query = Peminjaman::with(['siswa', 'details.alat', 'pengembalian'])->latest();
        if ($from) $query->whereDate('tanggal_pengajuan', '>=', $from);
        if ($to) $query->whereDate('tanggal_pengajuan', '<=', $to);
        $rows = $query->get();
        $filename = 'laporan-sipemal-' . now()->format('Ymd-His') . '.csv';
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM agar karakter Bahasa Indonesia terbaca dengan baik di Excel.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal Pengajuan', 'Nama Siswa', 'Email', 'Alat', 'Jumlah', 'Status', 'Tanggal Rencana Kembali', 'Tanggal Pengembalian', 'Denda']);
            foreach ($rows as $p) {
                $alat = $p->details->map(fn ($detail) => ($detail->alat->nama_alat ?? 'Alat dihapus') . ' ('. $detail->jumlah_pinjam . ')')->implode('; ');
                fputcsv($out, [
                    optional($p->tanggal_pengajuan)->format('Y-m-d'), $p->siswa->name ?? '-', $p->siswa->email ?? '-', $alat,
                    $p->details->sum('jumlah_pinjam'), $p->status_peminjaman,
                    optional($p->tanggal_rencana_kembali)->format('Y-m-d'), optional(optional($p->pengembalian)->tanggal_pengembalian)->format('Y-m-d'),
                    (float) optional($p->pengembalian)->denda,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function index(Request $request) {
        $from=$request->date('from');
        $to=$request->date('to');
        $query=Peminjaman::with(['siswa','details.alat','pengembalian'])->latest();
        if($from) $query->whereDate('tanggal_pengajuan','>=',$from);
        if($to) $query->whereDate('tanggal_pengajuan','<=',$to);
        $peminjamans=$query->get();
        return view('admin.laporan.index',[
            'peminjamans'=>$peminjamans,
            'totalAlat'=>Alat::count(),
            'totalPengembalian'=>Pengembalian::count(),
            'totalDenda'=>Pengembalian::sum('denda'),
            'from'=>$request->input('from'),
            'to'=>$request->input('to'),
        ]);
    }
}
