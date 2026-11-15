<?php
namespace App\Http\Controllers;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PengembalianController extends Controller
{
    public function index() {
        $peminjamans=Peminjaman::with(['siswa','details.alat','pengembalian'])->where('status_peminjaman','Disetujui')->latest()->get();
        return view('admin.pengembalian.index',compact('peminjamans'));
    }
    public function store(Request $request) {
        $data=$request->validate([
            'peminjaman_id'=>'required|exists:peminjamans,id',
            'kondisi_alat'=>['required',Rule::in(['Baik','Rusak Ringan','Rusak Berat'])],
            'denda'=>'required|numeric|min:0|max:99999999',
            'keterangan'=>'nullable|string|max:1000',
        ]);
        $result=DB::transaction(function() use($data) {
            $p=Peminjaman::with('details')->whereKey($data['peminjaman_id'])->lockForUpdate()->firstOrFail();
            if($p->status_peminjaman!=='Disetujui' || $p->pengembalian()->exists()) return false;
            Pengembalian::create([
                'peminjaman_id'=>$p->id,'admin_id'=>auth()->id(),'tanggal_pengembalian'=>now()->toDateString(),
                'kondisi_alat'=>$data['kondisi_alat'],'denda'=>$data['denda'],'keterangan'=>$data['keterangan']??null,
            ]);
            $p->status_peminjaman='Dikembalikan';
            $p->save();
            return true;
        });
        if(!$result) return back()->withErrors(['peminjaman_id'=>'Peminjaman ini sudah dikembalikan atau tidak aktif.']);
        return back()->with('success','Pengembalian berhasil dicatat.');
    }
}
