<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UiPagesTest extends TestCase
{
    private User $admin;
    private User $siswa;
    private Alat $laptop;
    private Alat $kamera;
    private Kategori $kategori;
    private Peminjaman $menunggu;
    private Peminjaman $disetujui;
    private Peminjaman $selesai;

    protected function setUp(): void
    {
        parent::setUp();
        // SQLite in-memory baru untuk setiap tes (tanpa dependensi Mockery).
        Artisan::call('migrate', ['--force' => true]);
        $this->admin = User::forceCreate(['name' => 'Admin Uji', 'email' => 'a@t.test', 'password' => bcrypt('password1'), 'role' => 'admin']);
        $this->siswa = User::forceCreate(['name' => 'Siswa Uji', 'email' => 's@t.test', 'password' => bcrypt('password1'), 'role' => 'siswa', 'nis' => '123', 'kelas' => 'XII RPL']);
        $this->kategori = Kategori::forceCreate(['nama_kategori' => 'Elektronik', 'deskripsi' => 'Elektronik']);
        $this->laptop = Alat::forceCreate(['kategori_id' => $this->kategori->id, 'kode_alat' => 'ALT-001', 'nama_alat' => 'Laptop Praktik', 'jumlah' => 5, 'kondisi' => 'Baik', 'deskripsi' => 'Laptop']);
        $this->kamera = Alat::forceCreate(['kategori_id' => $this->kategori->id, 'kode_alat' => 'ALT-002', 'nama_alat' => 'Kamera DSLR', 'jumlah' => 2, 'kondisi' => 'Baik', 'deskripsi' => null]);

        $mk = function (string $status, $alat, int $qty, string $kembali) {
            $p = Peminjaman::forceCreate(['user_id' => $this->siswa->id, 'tanggal_pengajuan' => now()->subDays(3)->toDateString(), 'tanggal_rencana_kembali' => $kembali, 'status_peminjaman' => $status, 'catatan' => 'uji']);
            DetailPeminjaman::forceCreate(['peminjaman_id' => $p->id, 'alat_id' => $alat->id, 'jumlah_pinjam' => $qty]);
            return $p;
        };
        $this->menunggu  = $mk('Menunggu', $this->laptop, 1, now()->addDays(5)->toDateString());
        $this->disetujui = $mk('Disetujui', $this->kamera, 1, now()->subDay()->toDateString()); // terlambat
        $this->selesai   = $mk('Dikembalikan', $this->laptop, 1, now()->addDay()->toDateString());
        Pengembalian::forceCreate(['peminjaman_id' => $this->selesai->id, 'admin_id' => $this->admin->id, 'tanggal_pengembalian' => now()->toDateString(), 'kondisi_alat' => 'Baik', 'denda' => 0]);
    }

    public function test_halaman_publik_tampil_dengan_desain_baru(): void
    {
        $this->get('/login')->assertOk()->assertSee('Alat siap, belajar jalan terus.')->assertSee('css/sipemal.css', false)->assertSee('data-toggle-password', false);
        $this->get('/register')->assertOk()->assertSee('Buat akun siswa')->assertSee('name="nis"', false);
    }

    public function test_login_tetap_berfungsi_dan_pesan_error_tampil(): void
    {
        $this->post('/login', ['email' => 'a@t.test', 'password' => 'salah'])->assertSessionHasErrors();
        $this->from('/login')->followingRedirects()->post('/login', ['email' => 'a@t.test', 'password' => 'salah'])->assertSee('Gagal masuk');
        $this->post('/login', ['email' => 'a@t.test', 'password' => 'password1'])->assertRedirect();
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_semua_halaman_admin_dirender(): void
    {
        $this->actingAs($this->admin);
        $urls = [
            route('admin.dashboard'), route('admin.alat.index'), route('admin.alat.create'), route('admin.alat.edit', $this->laptop),
            route('admin.kategori.index'), route('admin.kategori.create'), route('admin.kategori.edit', $this->kategori),
            route('admin.siswa.index'), route('admin.peminjaman.index'), route('admin.peminjaman.show', $this->menunggu),
            route('admin.peminjaman.show', $this->disetujui), route('admin.peminjaman.show', $this->selesai),
            route('admin.pengembalian.index'), route('admin.laporan.index'), route('admin.laporan.index', ['from' => '2000-01-01']),
            route('admin.profil'),
        ];
        foreach ($urls as $u) {
            $this->get($u)->assertOk()->assertSee('class="sidebar"', false);
        }
        $this->get(route('admin.dashboard'))->assertSee('Aktivitas peminjaman')->assertSee('Laptop Praktik');
        $this->get(route('admin.alat.index'))->assertSee('images/ilustrasi-alat/laptop.svg', false)->assertSee('images/ilustrasi-alat/kamera.svg', false);
        $this->get(route('admin.pengembalian.index'))->assertSee('Terlambat');
        $this->get(route('admin.laporan.export'))->assertOk();
    }

    public function test_semua_halaman_siswa_dirender(): void
    {
        $this->actingAs($this->siswa);
        foreach ([route('siswa.dashboard'), route('siswa.katalog'), route('siswa.katalog', ['q' => 'xyz-tidak-ada']), route('siswa.peminjaman.index'),
                  route('siswa.peminjaman.create'), route('siswa.peminjaman.create', ['alat_id' => $this->laptop->id]),
                  route('siswa.peminjaman.show', $this->menunggu), route('siswa.peminjaman.show', $this->selesai), route('siswa.profil')] as $u) {
            $this->get($u)->assertOk();
        }
        $this->get(route('siswa.dashboard'))->assertSee('Halo, Siswa')->assertSee('Rekomendasi untukmu');
        $this->get(route('siswa.katalog'))->assertSee('Ajukan pinjam')->assertSee('Detail')->assertSee('placeholder-alat.svg', false);
        $this->get(route('siswa.katalog', ['q' => 'xyz-tidak-ada']))->assertSee('Alat tidak ditemukan');
    }

    public function test_akses_peran_dan_tamu_tetap_dibatasi(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->siswa)->get(route('admin.dashboard'))->assertForbidden()->assertSee('Akses ditolak');
        $this->actingAs($this->admin)->get(route('siswa.katalog'))->assertForbidden();
    }

    public function test_halaman_404_kustom(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }

    public function test_alur_pengajuan_persetujuan_dan_pengembalian_masih_berjalan(): void
    {
        $this->actingAs($this->siswa)->post(route('siswa.peminjaman.store'), [
            'alat_id' => $this->laptop->id, 'jumlah_pinjam' => 2, 'tanggal_rencana_kembali' => now()->addDays(3)->toDateString(), 'catatan' => 'praktik',
        ])->assertSessionHasNoErrors();
        $baru = Peminjaman::latest('id')->first();
        $this->assertSame('Menunggu', $baru->status_peminjaman);

        $this->actingAs($this->admin)->patch(route('admin.peminjaman.status', $baru), ['status_peminjaman' => 'Disetujui'])->assertSessionHasNoErrors();
        $this->assertSame('Disetujui', $baru->fresh()->status_peminjaman);

        $this->post(route('admin.pengembalian.store'), ['peminjaman_id' => $baru->id, 'kondisi_alat' => 'Baik', 'denda' => 0, 'keterangan' => ''])->assertSessionHasNoErrors();
        $this->assertSame('Dikembalikan', $baru->fresh()->status_peminjaman);
        $this->assertDatabaseHas('pengembalians', ['peminjaman_id' => $baru->id]);
    }

    public function test_crud_alat_dan_kategori_masih_berjalan(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('admin.kategori.store'), ['nama_kategori' => 'Jaringan', 'deskripsi' => 'x'])->assertSessionHasNoErrors();
        $k = Kategori::where('nama_kategori', 'Jaringan')->firstOrFail();
        $this->post(route('admin.alat.store'), ['nama_alat' => 'Router Wi-Fi', 'kode_alat' => 'ALT-900', 'kategori_id' => $k->id, 'jumlah' => 3, 'kondisi' => 'Baik', 'deskripsi' => 'r'])->assertSessionHasNoErrors();
        $a = Alat::where('kode_alat', 'ALT-900')->firstOrFail();
        $this->assertNull($a->gambar);
        $this->put(route('admin.alat.update', $a), ['nama_alat' => 'Router Baru', 'kode_alat' => 'ALT-900', 'kategori_id' => $k->id, 'jumlah' => 4, 'kondisi' => 'Baik', 'deskripsi' => 'r'])->assertSessionHasNoErrors();
        $this->delete(route('admin.alat.destroy', $a))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('alats', ['kode_alat' => 'ALT-900']);
    }

    public function test_logo_huruf_s_dan_tanpa_gambar_alat_bawaan(): void
    {
        $this->get('/login')->assertSee('brand-s', false)->assertDontSee('images/alat/', false);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('<span class="brand-s">S</span>', false)->assertDontSee('images/alat/', false);
        $this->assertDirectoryDoesNotExist(public_path('images/alat'));
        $this->assertFileExists(public_path('images/placeholder-alat.svg'));
    }

    // ---------------- Upload foto alat ----------------
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private function png(string $name = 'foto.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG));
    }

    private function dataAlat(array $extra = []): array
    {
        return array_merge(['nama_alat' => 'Tripod', 'kode_alat' => 'ALT-777', 'kategori_id' => $this->kategori->id, 'jumlah' => 3, 'kondisi' => 'Baik', 'deskripsi' => 'x'], $extra);
    }

    public function test_form_alat_punya_upload_multipart(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('admin.alat.create'))->assertOk()->assertSee('enctype="multipart/form-data"', false)->assertSee('Foto alat')->assertSee('Pilih foto');
        $this->get(route('admin.alat.edit', $this->laptop))->assertSee('enctype="multipart/form-data"', false)->assertSee('Pilih foto');
    }

    public function test_upload_saat_tambah_tersimpan_dan_tampil_di_katalog(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin)->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $this->png()]))->assertSessionHasNoErrors();
        $a = Alat::where('kode_alat', 'ALT-777')->firstOrFail();
        $this->assertStringStartsWith('alat/', $a->gambar);
        Storage::disk('public')->assertExists($a->gambar);
        $this->assertTrue($a->ada_gambar);
        $this->actingAs($this->siswa)->get(route('siswa.katalog'))->assertSee(basename($a->gambar))->assertSee('Foto Tripod');
        $this->actingAs($this->admin)->get(route('admin.alat.edit', $a))->assertSee(basename($a->gambar))->assertSee('Ganti foto')->assertSee('Hapus foto saat ini');
    }

    public function test_nama_file_unik_untuk_setiap_unggahan(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);
        $this->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $this->png('sama.png')]));
        $this->post(route('admin.alat.store'), $this->dataAlat(['kode_alat' => 'ALT-778', 'gambar' => $this->png('sama.png')]));
        $p = Alat::whereIn('kode_alat', ['ALT-777', 'ALT-778'])->pluck('gambar');
        $this->assertCount(2, $p->unique());
        $this->assertStringNotContainsString('sama', $p->first());
    }

    public function test_ganti_foto_menghapus_file_lama_dan_tanpa_file_baru_foto_dipertahankan(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);
        $this->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $this->png()]));
        $a = Alat::where('kode_alat', 'ALT-777')->firstOrFail(); $lama = $a->gambar;

        $this->put(route('admin.alat.update', $a), $this->dataAlat(['nama_alat' => 'Tripod 2']))->assertSessionHasNoErrors();
        $this->assertSame($lama, $a->fresh()->gambar);               // tanpa file baru -> foto lama tetap
        Storage::disk('public')->assertExists($lama);

        $this->put(route('admin.alat.update', $a), $this->dataAlat(['gambar' => $this->png('baru.png')]))->assertSessionHasNoErrors();
        $baru = $a->fresh()->gambar;
        $this->assertNotSame($lama, $baru);
        Storage::disk('public')->assertMissing($lama);                // file lama dibersihkan
        Storage::disk('public')->assertExists($baru);
        $this->assertNotSame(Alat::gambarFallback(), $a->fresh()->gambar_url);
    }

    public function test_hapus_foto_saja_dan_hapus_alat_membersihkan_file(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);
        $this->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $this->png()]));
        $a = Alat::where('kode_alat', 'ALT-777')->firstOrFail(); $path = $a->gambar;
        $this->put(route('admin.alat.update', $a), $this->dataAlat(['hapus_gambar' => 1]))->assertSessionHasNoErrors();
        $this->assertNull($a->fresh()->gambar);
        Storage::disk('public')->assertMissing($path);
        $this->assertFalse($a->fresh()->ada_gambar);
        $this->assertSame($a->fresh()->ilustrasi_url, $a->fresh()->gambar_url);   // kembali ke ilustrasi, bukan foto

        $this->put(route('admin.alat.update', $a), $this->dataAlat(['gambar' => $this->png()]));
        $path2 = $a->fresh()->gambar;
        $this->delete(route('admin.alat.destroy', $a))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('alats', ['kode_alat' => 'ALT-777']);
        Storage::disk('public')->assertMissing($path2);
    }

    public function test_file_yang_masih_dipakai_alat_lain_tidak_dihapus(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('alat/bersama.png', base64_decode(self::PNG));
        $x = Alat::forceCreate(['kategori_id' => $this->kategori->id, 'kode_alat' => 'X1', 'nama_alat' => 'X1', 'jumlah' => 1, 'kondisi' => 'Baik', 'gambar' => 'alat/bersama.png']);
        Alat::forceCreate(['kategori_id' => $this->kategori->id, 'kode_alat' => 'X2', 'nama_alat' => 'X2', 'jumlah' => 1, 'kondisi' => 'Baik', 'gambar' => 'alat/bersama.png']);
        $this->actingAs($this->admin)->delete(route('admin.alat.destroy', $x))->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists('alat/bersama.png');
    }

    public function test_validasi_upload_pesan_jelas(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);
        // File SUNGGUHAN (bukan fake) berisi teks tetapi bernama .png: isi file diperiksa, bukan ekstensinya.
        $tmp = tempnam(sys_get_temp_dir(), 'sip'); file_put_contents($tmp, 'ini hanya teks, bukan gambar');
        $palsu = new UploadedFile($tmp, 'palsu.png', 'image/png', null, true);
        $this->from(route('admin.alat.create'))->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $palsu]))->assertSessionHasErrors('gambar');
        $this->from(route('admin.alat.create'))->post(route('admin.alat.store'), $this->dataAlat(['gambar' => UploadedFile::fake()->createWithContent('a.gif', base64_decode('R0lGODlhAQABAAAAACw='))]))->assertSessionHasErrors('gambar');
        $this->from(route('admin.alat.create'))->post(route('admin.alat.store'), $this->dataAlat(['gambar' => UploadedFile::fake()->createWithContent('a.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>')]))->assertSessionHasErrors('gambar');
        $besar = UploadedFile::fake()->createWithContent('besar.png', base64_decode(self::PNG) . str_repeat('0', 3 * 1024 * 1024));
        $r = $this->from(route('admin.alat.create'))->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $besar]));
        $r->assertSessionHasErrors('gambar');
        $this->assertStringContainsString('2 MB', session('errors')->first('gambar'));
        $this->assertDatabaseMissing('alats', ['kode_alat' => 'ALT-777']);
        $this->assertSame([], Storage::disk('public')->allFiles('alat'));
        $tmp2 = tempnam(sys_get_temp_dir(), 'sip'); file_put_contents($tmp2, 'x');
        $this->followingRedirects()->from(route('admin.alat.create'))->post(route('admin.alat.store'), $this->dataAlat(['gambar' => new UploadedFile($tmp2, 'palsu.png', 'image/png', null, true)]))->assertSee('JPG, JPEG, PNG, atau WebP');
    }

    public function test_siswa_dan_tamu_tidak_bisa_mengunggah_foto(): void
    {
        Storage::fake('public');
        $this->actingAs($this->siswa)->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $this->png()]))->assertForbidden();
        $this->actingAs($this->siswa)->put(route('admin.alat.update', $this->laptop), $this->dataAlat(['gambar' => $this->png()]))->assertForbidden();
        $this->assertSame([], Storage::disk('public')->allFiles('alat'));
        auth()->logout();
        $this->post(route('admin.alat.store'), $this->dataAlat(['gambar' => $this->png()]))->assertRedirect(route('login'));
    }

    public function test_ilustrasi_dan_placeholder_saat_belum_ada_foto(): void
    {
        Storage::fake('public');
        $this->assertFalse($this->laptop->ada_gambar);
        $this->assertSame(asset('images/ilustrasi-alat/laptop.svg'), $this->laptop->gambar_url);    // ilustrasi lokal sesuai jenis
        $this->assertSame(asset('images/ilustrasi-alat/kamera.svg'), $this->kamera->gambar_url);
        $aneh = Alat::forceCreate(['kategori_id' => $this->kategori->id, 'kode_alat' => 'Z1', 'nama_alat' => 'Barang aneh', 'jumlah' => 1, 'kondisi' => 'Baik']);
        $this->assertSame(Alat::gambarFallback(), $aneh->gambar_url);                                // jenis tak dikenal -> placeholder netral
        $hilang = Alat::forceCreate(['kategori_id' => $this->kategori->id, 'kode_alat' => 'H1', 'nama_alat' => 'Hilang', 'jumlah' => 1, 'kondisi' => 'Baik', 'gambar' => 'alat/tidak-ada.png']);
        $this->assertSame(Alat::gambarFallback(), $hilang->gambar_url);                              // path di DB tapi file tidak ada
        $this->actingAs($this->siswa)->get(route('siswa.katalog'))->assertOk()
            ->assertSee('Ilustrasi · belum ada foto')->assertSee('Ilustrasi Laptop Praktik (belum ada foto)');
    }

    public function test_foto_unggahan_mengalahkan_ilustrasi(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('alat/foto-asli.png', base64_decode(self::PNG));
        $this->laptop->forceFill(['gambar' => 'alat/foto-asli.png'])->save();
        $this->assertTrue($this->laptop->fresh()->ada_gambar);
        $this->assertStringContainsString('foto-asli.png', $this->laptop->fresh()->gambar_url);
        $this->actingAs($this->siswa)->get(route('siswa.katalog'))->assertSee('Foto Laptop Praktik');
    }

    public function test_route_penyaji_foto_aman(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('alat/a1.png', base64_decode(self::PNG));
        $this->get(route('alat.gambar', ['file' => 'a1.png']))->assertOk();
        $this->get('/gambar-alat/tidak-ada.png')->assertNotFound();
        $this->get('/gambar-alat/..%2F..%2F.env')->assertNotFound();
        $this->get('/gambar-alat/..')->assertNotFound();
    }

    public function test_tombol_hapus_punya_ikon_dan_konfirmasi(): void
    {
        $this->actingAs($this->admin);
        $h = $this->get(route('admin.alat.index'))->assertOk()->getContent();
        $this->assertStringContainsString('data-confirm=', $h);
        $this->assertMatchesRegularExpression('/aria-label="Hapus Laptop Praktik".*?<svg[^>]*class="icon"[^>]*>.*?M10 11v6/s', $h);
        $this->assertStringContainsString('method="POST"', $h);
        $this->assertStringContainsString('name="_method" value="DELETE"', $h);
        $k = $this->get(route('admin.kategori.index'))->getContent();
        $this->assertMatchesRegularExpression('/aria-label="Hapus Elektronik".*?M10 11v6/s', $k);
    }

    // ---------------- Manajemen Admin ----------------
    private function dataAdmin(array $extra = []): array
    {
        return array_merge(['name' => 'Admin Baru', 'email' => 'baru@sekolah.test', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123'], $extra);
    }

    public function test_admin_bisa_melihat_daftar_dan_menambah_admin(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('admin.admins.index'))->assertOk()->assertSee('Manajemen admin')->assertSee('Tambah admin')->assertSee('Admin Uji');
        $this->get(route('admin.admins.create'))->assertOk()->assertSee('Kata sandi')->assertSee('Ulangi kata sandi');
        $this->get(route('admin.dashboard'))->assertSee('Manajemen Admin');   // ada di sidebar

        $this->post(route('admin.admins.store'), $this->dataAdmin())->assertRedirect(route('admin.admins.index'))->assertSessionHas('success');
        $baru = User::where('email', 'baru@sekolah.test')->firstOrFail();
        $this->assertSame('admin', $baru->role);
        $this->assertNotSame('rahasia123', $baru->password);               // tidak disimpan polos
        $this->assertTrue(Hash::check('rahasia123', $baru->password));      // di-hash Laravel
        $this->get(route('admin.admins.index'))->assertSee('Admin Baru')->assertSee('baru@sekolah.test');
    }

    public function test_role_tidak_dipercaya_dari_input_dan_akun_lama_tidak_berubah(): void
    {
        $this->actingAs($this->admin)->post(route('admin.admins.store'), $this->dataAdmin(['role' => 'siswa', 'nis' => '999']));
        $this->assertSame('admin', User::where('email', 'baru@sekolah.test')->value('role'));      // role dipaksa admin oleh server
        $this->assertNull(User::where('email', 'baru@sekolah.test')->value('nis'));
        $this->assertSame('siswa', $this->siswa->fresh()->role);                                    // akun lama aman
        $this->assertSame('admin', $this->admin->fresh()->role);
        $this->assertSame(3, User::count());
    }

    public function test_validasi_form_admin(): void
    {
        $this->actingAs($this->admin);
        $this->from(route('admin.admins.create'))->post(route('admin.admins.store'), $this->dataAdmin(['email' => 'a@t.test']))->assertSessionHasErrors('email');   // email duplikat
        $this->assertStringContainsString('sudah terdaftar', session('errors')->first('email'));
        $this->from(route('admin.admins.create'))->post(route('admin.admins.store'), $this->dataAdmin(['password_confirmation' => 'beda']))->assertSessionHasErrors('password');
        $this->from(route('admin.admins.create'))->post(route('admin.admins.store'), $this->dataAdmin(['password' => 'pendek', 'password_confirmation' => 'pendek']))->assertSessionHasErrors('password');
        $this->from(route('admin.admins.create'))->post(route('admin.admins.store'), ['name' => '', 'email' => 'bukan-email', 'password' => ''])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertSame(2, User::count());
        $this->followingRedirects()->from(route('admin.admins.create'))->post(route('admin.admins.store'), $this->dataAdmin(['email' => 'salah']))->assertSee('Format email tidak valid');
    }

    public function test_siswa_dan_tamu_tidak_bisa_membuat_admin(): void
    {
        $this->actingAs($this->siswa);
        $this->get(route('admin.admins.index'))->assertForbidden();
        $this->get(route('admin.admins.create'))->assertForbidden();
        $this->post(route('admin.admins.store'), $this->dataAdmin())->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'baru@sekolah.test']);
        auth()->logout();
        $this->post(route('admin.admins.store'), $this->dataAdmin())->assertRedirect(route('login'));
        $this->get(route('admin.admins.index'))->assertRedirect(route('login'));
    }

    public function test_admin_baru_bisa_login_ke_dashboard_admin(): void
    {
        $this->actingAs($this->admin)->post(route('admin.admins.store'), $this->dataAdmin());
        auth()->logout();
        $this->post('/login', ['email' => 'baru@sekolah.test', 'password' => 'rahasia123'])->assertRedirect(route('admin.dashboard'));
    }

    public function test_formulir_admin_punya_csrf(): void
    {
        $this->actingAs($this->admin)->get(route('admin.admins.create'))->assertSee('name="_token"', false);
    }

    // ---------------- Tema biru & hamburger ----------------
    public function test_tema_biru_dan_aturan_hamburger_desktop(): void
    {
        $css = file_get_contents(public_path('css/sipemal.css'));
        foreach (['--brand-600:#2563eb', '--brand-100:#dbeafe', '--paper:#f5f9ff', '--ink:#172b4d', '--accent:#0d9488'] as $token) {
            $this->assertStringContainsString($token, $css);
        }
        $this->assertDoesNotMatchRegularExpression('/#(059669|047857|10b981|c6f432)/i', $css);     // tidak ada sisa hijau lama
        $this->assertStringContainsString('.icon-btn.menu-toggle{display:none}', $css);            // hamburger disembunyikan di desktop
        $this->assertMatchesRegularExpression('/@media\(max-width:1023px\)\{[^@]*?\.icon-btn\.menu-toggle\{display:grid/s', $css);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('menu-toggle', false)->assertSee('theme-color" content="#2563EB"', false);
    }

    public function test_pesan_validasi_berbahasa_indonesia(): void
    {
        app()->setLocale('id');
        $this->from('/register')->post('/register', [])->assertSessionHasErrors();
        $semua = collect(session('errors')->all())->implode(' | ');
        $this->assertStringContainsString('Nama lengkap wajib diisi.', $semua);
        $this->assertStringContainsString('NIS wajib diisi.', $semua);
        $this->assertStringNotContainsString('field is required', $semua);
        $this->from('/register')->post('/register', ['name' => 'A', 'nis' => '1', 'kelas' => 'X', 'email' => 'a@t.test', 'password' => 'abcdefgh', 'password_confirmation' => 'x'])->assertSessionHasErrors('email');
        $this->assertStringContainsString('sudah terdaftar', session('errors')->first('email'));
    }
}
