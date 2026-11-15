<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('admin.kelas.index', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nama_kelas' => strtoupper(
                trim($request->input('nama_kelas', ''))
            ),
        ]);

        $data = $request->validate([
            'nama_kelas' => [
                'required',
                'string',
                'max:50',
                'unique:kelas,nama_kelas',
            ],
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.unique' => 'Kelas sudah terdaftar.',
        ]);

        Kelas::create([
            'nama_kelas' => $data['nama_kelas'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function toggle(Kelas $kelas)
    {
        $kelas->update([
            'is_active' => ! $kelas->is_active,
        ]);

        return back()->with(
            'success',
            'Status kelas berhasil diperbarui.'
        );
    }
}
