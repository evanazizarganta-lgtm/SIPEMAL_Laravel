<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $tingkat = ['X', 'XI', 'XII'];

        $jurusan = [
            'TKP',
            'DPIB',
            'TPM',
            'TKR',
            'TEI',
            'TOI',
            'TSM',
            'TITL',
            'RPL',
        ];

        foreach ($tingkat as $kelas) {
            foreach ($jurusan as $kode) {
                Kelas::firstOrCreate(
                    [
                        'nama_kelas' => $kelas . ' ' . $kode,
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
