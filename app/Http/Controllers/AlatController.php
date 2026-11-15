<?php
namespace App\Http\Controllers;
use App\Models\Alat;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AlatController extends Controller
{
    public function katalog(Request $request) {
        $query = Alat::with('kategori')->where('jumlah', '>', 0);
        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($builder) use ($term) {
                $builder->where('nama_alat', 'like', '%'.$term.'%')
                    ->orWhere('kode_alat', 'like', '%'.$term.'%');
            });
        }
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->integer('kategori_id'));
        if ($request->filled('kondisi')) $query->where('kondisi', $request->input('kondisi'));
        $alats = $query->orderBy('nama_alat')->paginate(12)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('siswa.katalog', compact('alats', 'kategoris'));
    }
    public function index() { $alats=Alat::with('kategori')->orderBy('nama_alat')->get(); return view('admin.alat.index',compact('alats')); }
    public function create() { $kategoris=Kategori::orderBy('nama_kategori')->get(); return view('admin.alat.create',compact('kategoris')); }

    /** Pesan validasi foto dalam bahasa Indonesia. */
    private function pesanGambar(): array
    {
        return [
            'gambar.uploaded' => 'Foto gagal diunggah. Ukuran file kemungkinan melebihi batas server; coba foto yang lebih kecil (maks. 2 MB).',
            'gambar.file'     => 'Foto yang dipilih tidak valid. Pilih file gambar lalu coba lagi.',
            'gambar.image'    => 'File harus berupa gambar (JPG, JPEG, PNG, atau WebP).',
            'gambar.mimes'    => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
            'gambar.max'      => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    /** Hapus file foto lama bila tidak lagi dipakai alat mana pun. */
    private function hapusFileJikaTidakDipakai(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'alat/') || str_contains($path, '..')) return;
        if (Alat::where('gambar', $path)->exists()) return; // masih dipakai data lain
        Storage::disk('public')->delete($path);
    }

    public function store(Request $request) {
        $data=$request->validate([
            'kategori_id'=>'required|exists:kategoris,id','nama_alat'=>'required|string|max:120',
            'kode_alat'=>'required|string|max:50|unique:alats,kode_alat','jumlah'=>'required|integer|min:0',
            'kondisi'=>['required',Rule::in(['Baik','Rusak Ringan','Rusak Berat'])],'deskripsi'=>'nullable|string|max:1000',
            'gambar'=>['nullable','file','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ], $this->pesanGambar());
        unset($data['gambar']);
        // store() memberi nama acak unik (hash) + ekstensi aman, sehingga tidak menimpa file lain.
        if ($request->hasFile('gambar')) $data['gambar']=$request->file('gambar')->store('alat','public');
        try {
            Alat::create($data);
        } catch (\Throwable $e) {
            if (!empty($data['gambar'])) Storage::disk('public')->delete($data['gambar']);
            throw $e;
        }
        return redirect()->route('admin.alat.index')->with('success','Alat berhasil ditambahkan.');
    }
    public function edit(Alat $alat) { $kategoris=Kategori::orderBy('nama_kategori')->get(); return view('admin.alat.edit',compact('alat','kategoris')); }
    public function update(Request $request,Alat $alat) {
        $data=$request->validate([
            'kategori_id'=>'required|exists:kategoris,id','nama_alat'=>'required|string|max:120',
            'kode_alat'=>['required','string','max:50',Rule::unique('alats')->ignore($alat->id)],'jumlah'=>'required|integer|min:0',
            'kondisi'=>['required',Rule::in(['Baik','Rusak Ringan','Rusak Berat'])],'deskripsi'=>'nullable|string|max:1000',
            'gambar'=>['nullable','file','image','mimes:jpg,jpeg,png,webp','max:2048'],'hapus_gambar'=>'nullable|boolean',
        ], $this->pesanGambar());
        unset($data['gambar'], $data['hapus_gambar']);
        $lama = $alat->gambar;
        $baru = null;
        if ($request->hasFile('gambar')) {                 // ganti foto
            $baru = $request->file('gambar')->store('alat','public');
            $data['gambar'] = $baru;
        } elseif ($request->boolean('hapus_gambar')) {     // hapus foto saja
            $data['gambar'] = null;
        }                                                  // selain itu: foto lama dipertahankan
        try {
            $alat->update($data);
        } catch (\Throwable $e) {
            if ($baru) Storage::disk('public')->delete($baru);
            throw $e;
        }
        if ($lama && $lama !== $alat->gambar) $this->hapusFileJikaTidakDipakai($lama);
        return redirect()->route('admin.alat.index')->with('success','Data alat berhasil diperbarui.');
    }
    public function destroy(Alat $alat) {
        if($alat->detailPeminjaman()->exists()) return back()->withErrors(['alat'=>'Alat memiliki riwayat peminjaman sehingga tidak dapat dihapus.']);
        $path = $alat->gambar;
        $alat->delete();
        $this->hapusFileJikaTidakDipakai($path);
        return back()->with('success','Alat berhasil dihapus.');
    }

    /** Menyajikan foto alat bila symlink public/storage belum dibuat (fallback storage:link). */
    public function gambar(string $file) {
        abort_if($file !== basename($file) || str_contains($file, '..'), 404);
        $path = 'alat/' . $file;
        abort_unless(Storage::disk('public')->exists($path), 404);
        return Storage::disk('public')->response($path, null, ['Cache-Control' => 'public, max-age=604800']);
    }
}
