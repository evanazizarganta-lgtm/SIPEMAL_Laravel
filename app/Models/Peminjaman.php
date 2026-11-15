<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    protected $table = 'peminjamans';

    protected $fillable = [
        'user_id',
        'admin_id',
        'tanggal_pengajuan',
        'tanggal_pinjam',
        'tanggal_rencana_kembali',
        'status_peminjaman',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_pinjam' => 'date',
            'tanggal_rencana_kembali' => 'date',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'peminjaman_id');
    }

    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class, 'peminjaman_id');
    }
}