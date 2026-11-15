<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kategori;
use App\Models\Alat;
use App\Models\NisResmi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat kelas awal tanpa duplikasi.
        $this->call(KelasSeeder::class);

        // Admin demo.
        User::updateOrCreate(
            ['email' => 'admin@sipemal.test'],
            [
                'name' => 'Admin SIPEMAL',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
                'nis' => null,
                'kelas' => null,
            ]
        );

        // Siswa demo.
        User::updateOrCreate(
            ['email' => 'siswa@sipemal.test'],
            [
                'name' => 'Siswa Demo',
                'password' => Hash::make('siswa12345'),
                'role' => 'siswa',
                'nis' => 'DEMO001',
                'kelas' => 'XII RPL',
            ]
        );

        // Sinkronisasi NIS seluruh siswa.
        User::where('role', 'siswa')
            ->whereNotNull('nis')
            ->where('nis', '<>', '')
            ->get()
            ->each(function ($siswa) {

                $nis = NisResmi::firstOrCreate(
                    ['nis' => $siswa->nis],
                    [
                        'nama_siswa' => $siswa->name,
                        'is_active' => true,
                        'is_used' => true,
                        'user_id' => $siswa->id,
                    ]
                );

                // Jangan mengambil alih NIS milik akun lain.
                if (
                    $nis->user_id === null ||
                    (int) $nis->user_id === (int) $siswa->id
                ) {
                    $nis->update([
                        'is_used' => true,
                        'user_id' => $siswa->id,
                    ]);
                }
            });

        // Kategori.
        $kategori = Kategori::firstOrCreate(
            ['nama_kategori' => 'Elektronik'],
            ['deskripsi' => 'Peralatan elektronik']
        );

        Kategori::firstOrCreate(
            ['nama_kategori' => 'Perkakas'],
            ['deskripsi' => 'Peralatan praktik']
        );

        // Alat demo utama.
        Alat::firstOrCreate(
            ['kode_alat' => 'ALT-001'],
            [
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Laptop Praktik',
                'jumlah' => 10,
                'kondisi' => 'Baik',
                'deskripsi' => 'Laptop untuk praktik pembelajaran',
            ]
        );

        Alat::firstOrCreate(
            ['kode_alat' => 'ALT-002'],
            [
                'kategori_id' => $kategori->id,
                'nama_alat' => 'Proyektor',
                'jumlah' => 4,
                'kondisi' => 'Baik',
                'deskripsi' => 'Proyektor untuk presentasi',
            ]
        );

        // Kategori tambahan.
        $kat = fn ($nama, $desk) =>
            Kategori::firstOrCreate(
                ['nama_kategori' => $nama],
                ['deskripsi' => $desk]
            );

        $multimedia = $kat(
            'Multimedia',
            'Kamera, tripod, dan perlengkapan dokumentasi'
        );

        $jaringan = $kat(
            'Jaringan',
            'Perangkat dan alat praktik jaringan komputer'
        );

        $lab = $kat(
            'Laboratorium',
            'Alat laboratorium dan pengukuran'
        );

        $rpl = $kat(
            'Praktik RPL',
            'Perlengkapan praktik rekayasa perangkat lunak dan IoT'
        );

        $perkakas = Kategori::where(
            'nama_kategori',
            'Perkakas'
        )->first();

        $demo = [
            [
                'ALT-003', $multimedia, 'Kamera DSLR',
                3, 'Baik',
                'Kamera untuk dokumentasi kegiatan dan praktik fotografi'
            ],
            [
                'ALT-004', $multimedia, 'Tripod Kamera',
                5, 'Baik',
                'Tripod aluminium dengan kepala 3 arah'
            ],
            [
                'ALT-005', $jaringan, 'Router Wi-Fi',
                6, 'Baik',
                'Router untuk praktik konfigurasi jaringan'
            ],
            [
                'ALT-006', $jaringan, 'Kit Crimping & LAN Tester',
                4, 'Baik',
                'Tang crimping, konektor RJ45, dan LAN tester'
            ],
            [
                'ALT-007', $lab, 'Mikroskop Digital',
                3, 'Baik',
                'Mikroskop dengan kamera untuk praktik laboratorium'
            ],
            [
                'ALT-008', $rpl, 'Arduino Uno Starter Kit',
                12, 'Baik',
                'Papan Arduino, breadboard, dan sensor dasar'
            ],
            [
                'ALT-009', $rpl, 'Multimeter Digital',
                8, 'Baik',
                'Pengukur tegangan, arus, dan hambatan'
            ],
            [
                'ALT-010', $multimedia, 'Speaker Portabel',
                2, 'Rusak Ringan',
                'Speaker untuk acara dan presentasi'
            ],
            [
                'ALT-011', $perkakas, 'Toolkit Obeng Presisi',
                6, 'Baik',
                'Set obeng presisi untuk perawatan perangkat'
            ],
        ];

        foreach (
            $demo as [$kode, $k, $nama, $jml, $kondisi, $desk]
        ) {
            if (! $k) {
                continue;
            }

            Alat::firstOrCreate(
                ['kode_alat' => $kode],
                [
                    'kategori_id' => $k->id,
                    'nama_alat' => $nama,
                    'jumlah' => $jml,
                    'kondisi' => $kondisi,
                    'deskripsi' => $desk,
                ]
            );
        }
    }
}
