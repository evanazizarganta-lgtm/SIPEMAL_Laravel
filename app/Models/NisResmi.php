<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NisResmi extends Model
{
    protected $table = 'nis_resmi';

    protected $fillable = [
        'nis',
        'nama_siswa',
        'is_active',
        'is_used',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_used' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
