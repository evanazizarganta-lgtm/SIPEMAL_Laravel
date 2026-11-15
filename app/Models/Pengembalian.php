<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengembalian extends Model
{
    protected $table = 'pengembalians';

    protected $fillable = [
        'peminjaman_id',
        'admin_id',
        'tanggal_pengembalian',
        'kondisi_alat',
        'denda',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengembalian' => 'date',
            'denda' => 'decimal:2',
        ];
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}