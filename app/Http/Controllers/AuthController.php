<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\NisResmi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        $kelas = Kelas::where('is_active', true)
            ->orderBy('nama_kelas')
            ->get();

        return view('auth.register', compact('kelas'));
    }

    /**
     * Memeriksa NIS dan mengambil nama resmi.
     */
    public function checkNis(Request $request)
    {
        $request->validate([
            'nis' => ['required', 'string', 'max:30'],
        ]);

        $nis = trim($request->nis);

        $data = NisResmi::where('nis', $nis)
            ->where('is_active', true)
            ->where('is_used', false)
            ->first();

        if (! $data) {
            return response()->json([
                'valid' => false,
                'message' => 'NIS tidak terdaftar, tidak aktif, atau sudah digunakan.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'nama_siswa' => $data->nama_siswa,
        ]);
    }

    /**
     * Login tetap sama seperti sistem lama.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route(
                Auth::user()->role === 'admin'
                    ? 'admin.dashboard'
                    : 'siswa.dashboard'
            );
        }

        return back()
            ->withErrors([
                'email' => 'Email atau kata sandi tidak cocok.',
            ])
            ->onlyInput('email');
    }

    /**
     * Registrasi siswa baru.
     */
    public function storeRegister(Request $request)
    {
        $data = $request->validate([
            'nis' => [
                'required',
                'string',
                'max:30',
            ],

            'kelas' => [
                'required',
                'string',
                'exists:kelas,nama_kelas',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'kelas.required' => 'Silakan pilih kelas.',
            'kelas.exists' => 'Kelas tidak terdaftar.',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $nisInput = trim($data['nis']);

        $user = DB::transaction(function () use ($data, $nisInput) {

            // Mengunci data NIS selama proses registrasi.
            $nisResmi = NisResmi::where('nis', $nisInput)
                ->lockForUpdate()
                ->first();

            if (
                ! $nisResmi ||
                ! $nisResmi->is_active ||
                $nisResmi->is_used ||
                $nisResmi->user_id !== null
            ) {
                throw ValidationException::withMessages([
                    'nis' => 'NIS tidak valid, tidak aktif, atau sudah digunakan.',
                ]);
            }

            // Memastikan kelas benar-benar aktif.
            $kelas = Kelas::where(
                'nama_kelas',
                $data['kelas']
            )
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $kelas) {
                throw ValidationException::withMessages([
                    'kelas' => 'Kelas sudah tidak aktif. Silakan pilih kelas lain.',
                ]);
            }

            // Nama berasal dari database resmi, bukan input browser.
            $user = User::create([
                'name' => $nisResmi->nama_siswa,
                'nis' => $nisResmi->nis,
                'kelas' => $kelas->nama_kelas,
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'siswa',
            ]);

            // Menandai NIS sudah digunakan.
            $nisResmi->update([
                'is_used' => true,
                'user_id' => $user->id,
            ]);

            return $user;
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('siswa.dashboard')
            ->with(
                'success',
                'Pendaftaran berhasil. Selamat datang di SIPEMAL!'
            );
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil keluar.');
    }
}
