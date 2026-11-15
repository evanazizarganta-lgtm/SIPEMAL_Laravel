<?php
namespace App\Http\Controllers;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index() { $kategoris=Kategori::withCount('alats')->orderBy('nama_kategori')->get(); return view('admin.kategori.index',compact('kategoris')); }
    public function create() { return view('admin.kategori.create'); }
    public function store(Request $request) {
        $data=$request->validate(['nama_kategori'=>'required|string|max:100|unique:kategoris,nama_kategori','deskripsi'=>'nullable|string|max:1000']);
        Kategori::create($data);
        return redirect()->route('admin.kategori.index')->with('success','Kategori berhasil ditambahkan.');
    }
    public function edit(Kategori $kategori) { return view('admin.kategori.edit',compact('kategori')); }
    public function update(Request $request,Kategori $kategori) {
        $data=$request->validate(['nama_kategori'=>['required','string','max:100',Rule::unique('kategoris')->ignore($kategori->id)],'deskripsi'=>'nullable|string|max:1000']);
        $kategori->update($data);
        return redirect()->route('admin.kategori.index')->with('success','Kategori berhasil diperbarui.');
    }
    public function destroy(Kategori $kategori) {
        if($kategori->alats()->exists()) return back()->withErrors(['kategori'=>'Kategori masih digunakan oleh alat.']);
        $kategori->delete();
        return back()->with('success','Kategori berhasil dihapus.');
    }
}
