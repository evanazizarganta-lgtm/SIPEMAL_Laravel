<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Manajemen Admin: Admin yang sedang login dapat melihat daftar Admin dan menambah Admin baru.
 * Route-nya berada di grup `role:admin` (routes/web.php), jadi Siswa dan tamu otomatis ditolak.
 */
class AdminController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->orderBy('name')->get();
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        // Hanya 4 field ini yang diterima dari formulir. Field "role" sengaja TIDAK divalidasi/dipakai.
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama lengkap maksimal 100 karakter.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah terdaftar. Gunakan email lain.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $admin = new User();
        $admin->name     = $data['name'];
        $admin->email    = $data['email'];
        $admin->password = $data['password']; // otomatis di-hash oleh cast 'hashed' pada model User
        $admin->role     = 'admin';           // ditetapkan server, bukan dari input pengguna
        $admin->save();

        return redirect()->route('admin.admins.index')->with('success', 'Admin baru "' . $admin->name . '" berhasil ditambahkan.');
    }
}
