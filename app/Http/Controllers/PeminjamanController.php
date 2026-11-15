<?php
namespace App\Http\Controllers;
use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeminjamanController extends Controller
{
    public function index() { $peminjamans=Peminjaman::with(['siswa','admin','details.alat','pengembalian'])->latest()->get(); return view('admin.peminjaman.index',compact('peminjamans')); }
    public function show(Peminjaman $peminjaman) { $peminjaman->load(['siswa','admin','details.alat.kategori','pengembalian']); return view('admin.peminjaman.show',compact('peminjaman')); }
    public function indexSiswa() { $peminjamans=Peminjaman::with(['details.alat','pengembalian'])->where('user_id',auth()->id())->latest()->get(); return view('siswa.peminjaman.index',compact('peminjamans')); }
    public function create(Request $request) { $alats=Alat::with('kategori')->where('jumlah','>',0)->orderBy('nama_alat')->get(); return view('siswa.peminjaman.create', ['alats'=>$alats, 'selectedAlat'=>$request->integer('alat_id')]); }
    public function store(Request $request) {
        $data=$request->validate(['alat_id'=>'required|exists:alats,id','jumlah_pinjam'=>'required|integer|min:1','tanggal_rencana_kembali'=>'required|date|after:today','catatan'=>'nullable|string|max:1000']);
        $peminjaman=DB::transaction(function() use($data) {
            $alat=Alat::whereKey($data['alat_id'])->lockForUpdate()->firstOrFail();
            $dipinjam=(int)DetailPeminjaman::where('alat_id',$alat->id)->whereHas('peminjaman',fn($q)=>$q->where('status_peminjaman','Disetujui'))->sum('jumlah_pinjam');
            if($alat->jumlah-$dipinjam < $data['jumlah_pinjam']) return null;
            $p=Peminjaman::create(['user_id'=>auth()->id(),'tanggal_pengajuan'=>now()->toDateString(),'tanggal_rencana_kembali'=>$data['tanggal_rencana_kembali'],'status_peminjaman'=>'Menunggu','catatan'=>$data['catatan']??null]);
            $p->details()->create(['alat_id'=>$alat->id,'jumlah_pinjam'=>$data['jumlah_pinjam']]);
            return $p;
        });
        if(!$peminjaman) return back()->withInput()->withErrors(['alat_id'=>'Stok tersedia tidak mencukupi.']);
        return redirect()->route('siswa.peminjaman.index')->with('success','Pengajuan dikirim dan menunggu persetujuan Admin.');
    }
    public function showSiswa(Peminjaman $peminjaman) {
        abort_unless($peminjaman->user_id===auth()->id(),403);
        $peminjaman->load(['details.alat.kategori','pengembalian','admin']);
        return view('siswa.peminjaman.show',compact('peminjaman'));
    }
    public function updateStatus(Request $request,Peminjaman $peminjaman) {
        $data=$request->validate(['status_peminjaman'=>['required',Rule::in(['Disetujui','Ditolak'])]]);
        $ok=DB::transaction(function() use($peminjaman,$data) {
            $p=Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();
            if($p->status_peminjaman!=='Menunggu') return false;
            if($data['status_peminjaman']==='Disetujui') {
                foreach($p->details as $detail) {
                    $alat=Alat::whereKey($detail->alat_id)->lockForUpdate()->firstOrFail();
                    $dipinjam=(int)DetailPeminjaman::where('alat_id',$alat->id)->whereHas('peminjaman',fn($q)=>$q->where('status_peminjaman','Disetujui'))->sum('jumlah_pinjam');
                    if($alat->jumlah-$dipinjam < $detail->jumlah_pinjam) return false;
                }
                $p->tanggal_pinjam=now()->toDateString();
            }
            $p->status_peminjaman=$data['status_peminjaman'];
            $p->admin_id=auth()->id();
            $p->save();
            return true;
        });
        if(!$ok) return back()->withErrors(['status_peminjaman'=>'Pengajuan tidak dapat diproses; periksa status dan stok alat.']);
        return redirect()->route('admin.peminjaman.index')->with('success','Status pengajuan berhasil diperbarui.');
    }
}
