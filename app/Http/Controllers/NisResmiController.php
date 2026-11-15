<?php

namespace App\Http\Controllers;

use App\Models\NisResmi;
use Illuminate\Http\Request;

class NisResmiController extends Controller
{
    public function index()
    {
        $nis = NisResmi::with('user')
            ->orderBy('nis')
            ->get();

        return view('admin.nis.index', compact('nis'));
    }

    public function create()
    {
        return view('admin.nis.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'nis' => trim($request->input('nis', '')),
            'nama_siswa' => trim(
                $request->input('nama_siswa', '')
            ),
        ]);

        $data = $request->validate([
            'nis' => [
                'required',
                'string',
                'max:30',
                'unique:nis_resmi,nis',
                'unique:users,nis',
            ],

            'nama_siswa' => [
                'required',
                'string',
                'max:100',
            ],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar atau digunakan.',
            'nama_siswa.required' => 'Nama siswa wajib diisi.',
        ]);

        NisResmi::create([
            'nis' => $data['nis'],
            'nama_siswa' => $data['nama_siswa'],
            'is_active' => true,
            'is_used' => false,
            'user_id' => null,
        ]);

        return redirect()
            ->route('admin.nis.index')
            ->with('success', 'NIS resmi berhasil ditambahkan.');
    }

    public function toggle(NisResmi $nisResmi)
    {
        $nisResmi->update([
            'is_active' => ! $nisResmi->is_active,
        ]);

        return back()->with(
            'success',
            'Status NIS berhasil diperbarui.'
        );
    }
}
