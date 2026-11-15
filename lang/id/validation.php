<?php

/*
| Pesan validasi berbahasa Indonesia untuk seluruh aplikasi.
| Aktif otomatis karena APP_LOCALE=id di file .env.
| Ingin mengubah kalimat? Edit di sini. Nama kolom yang tampil di pesan diatur di bagian 'attributes' (paling bawah).
*/
return [
    'accepted'  => ':attribute harus disetujui.',
    'after'     => ':attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
    'array'     => ':attribute harus berupa array.',
    'before'    => ':attribute harus berupa tanggal sebelum :date.',
    'between'   => [
        'numeric' => ':attribute harus di antara :min dan :max.',
        'file'    => ':attribute harus berukuran antara :min dan :max KB.',
        'string'  => ':attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean'   => ':attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak sama.',
    'date'      => ':attribute bukan tanggal yang valid.',
    'email'     => 'Format :attribute tidak valid.',
    'exists'    => ':attribute yang dipilih tidak valid.',
    'file'      => ':attribute harus berupa berkas.',
    'image'     => ':attribute harus berupa gambar (JPG, JPEG, PNG, atau WebP).',
    'in'        => ':attribute yang dipilih tidak valid.',
    'integer'   => ':attribute harus berupa bilangan bulat.',
    'max'       => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file'    => ':attribute tidak boleh lebih dari :max KB.',
        'string'  => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes'     => ':attribute harus berformat: :values.',
    'min'       => [
        'numeric' => ':attribute minimal :min.',
        'file'    => ':attribute minimal berukuran :min KB.',
        'string'  => ':attribute minimal :min karakter.',
    ],
    'numeric'   => ':attribute harus berupa angka.',
    'required'  => ':attribute wajib diisi.',
    'string'    => ':attribute harus berupa teks.',
    'unique'    => ':attribute sudah terdaftar. Gunakan yang lain.',
    'uploaded'  => ':attribute gagal diunggah. Ukuran file mungkin melebihi batas server.',

    'custom' => [],

    // Nama kolom yang tampil ramah di pesan error
    'attributes' => [
        'name'                    => 'Nama lengkap',
        'email'                   => 'Email',
        'password'                => 'Kata sandi',
        'password_confirmation'   => 'Konfirmasi kata sandi',
        'nis'                     => 'NIS',
        'kelas'                   => 'Kelas',
        'nama_alat'               => 'Nama alat',
        'kode_alat'               => 'Kode alat',
        'kategori_id'             => 'Kategori',
        'nama_kategori'           => 'Nama kategori',
        'jumlah'                  => 'Jumlah',
        'kondisi'                 => 'Kondisi',
        'deskripsi'               => 'Deskripsi',
        'gambar'                  => 'Foto alat',
        'alat_id'                 => 'Alat',
        'jumlah_pinjam'           => 'Jumlah dipinjam',
        'tanggal_rencana_kembali' => 'Tanggal rencana kembali',
        'catatan'                 => 'Catatan',
        'kondisi_alat'            => 'Kondisi alat',
        'denda'                   => 'Denda',
        'keterangan'              => 'Keterangan',
        'status_peminjaman'       => 'Status peminjaman',
        'peminjaman_id'           => 'Peminjaman',
        'from'                    => 'Tanggal awal',
        'to'                      => 'Tanggal akhir',
    ],
];
