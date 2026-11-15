<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Alat extends Model
{
    protected $table = 'alats';

    protected $fillable = [
        'kategori_id',
        'nama_alat',
        'kode_alat',
        'jumlah',
        'kondisi',
        'deskripsi',
        'gambar',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'alat_id');
    }

    public function getTersediaAttribute(): int
    {
        $dipinjam = $this->detailPeminjaman()
            ->whereHas('peminjaman', function ($query) {
                $query->where('status_peminjaman', 'Disetujui');
            })
            ->sum('jumlah_pinjam');

        return max(0, $this->jumlah - (int) $dipinjam);
    }

    /**
     * Kata kunci -> jenis ilustrasi (public/images/alat/{jenis}.svg).
     * Hanya dipakai untuk tampilan; tidak menambah kolom database.
     */
    public const JENIS_KATA_KUNCI = [
        'tripod'    => ['tripod', 'monopod'],
        'kamera'    => ['kamera', 'camera', 'dslr', 'mirrorless', 'webcam', 'camcorder', 'handycam'],
        'proyektor' => ['proyektor', 'projector', 'infocus', 'lcd'],
        'jaringan'  => ['router', 'switch', 'jaringan', 'network', 'access point', 'mikrotik', 'lan tester', 'crimping', 'kabel lan', 'utp', 'wifi', 'modem'],
        'rpl'       => ['arduino', 'raspberry', 'mikrokontroler', 'microcontroller', 'breadboard', 'sensor', 'rpl', 'esp32', 'esp8266', 'solder', 'multimeter', 'sirkuit', 'iot'],
        'lab'       => ['mikroskop', 'laboratorium', 'beaker', 'gelas ukur', 'tabung reaksi', 'pipet', 'erlenmeyer', 'bunsen', 'lab '],
        'audio'     => ['speaker', 'mikrofon', 'microphone', 'audio', 'sound', 'headset', 'amplifier'],
        'perkakas'  => ['obeng', 'tang', 'palu', 'bor', 'kunci', 'gergaji', 'perkakas', 'toolkit', 'tool kit', 'tools'],
        'laptop'    => ['laptop', 'notebook', 'komputer', 'computer', 'pc ', 'chromebook', 'tablet', 'ipad', 'monitor'],
    ];

    public static function jenisDari(?string $nama, ?string $kategori = null): string
    {
        $haystack = ' ' . Str::lower(trim(($nama ?? '') . ' ' . ($kategori ?? ''))) . ' ';
        foreach (self::JENIS_KATA_KUNCI as $jenis => $kata) {
            foreach ($kata as $k) {
                if (Str::contains($haystack, $k)) {
                    return $jenis;
                }
            }
        }
        return 'default';
    }

    public function getJenisAttribute(): string
    {
        return self::jenisDari($this->nama_alat, optional($this->kategori)->nama_kategori);
    }

    /** Jenis yang punya ilustrasi lokal (public/images/ilustrasi-alat/{jenis}.svg). */
    public const JENIS_ILUSTRASI = ['laptop', 'proyektor', 'kamera', 'perkakas'];

    /** URL foto unggahan Admin, atau null bila belum ada / file hilang. */
    public function fotoUrl(): ?string
    {
        $path = $this->gambar;
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }
        // Memakai symlink public/storage bila ada (php artisan storage:link); jika tidak,
        // foto disajikan lewat route 'alat.gambar' agar tetap tampil tanpa symlink.
        $url = file_exists(public_path('storage'))
            ? asset('storage/' . $path)
            : route('alat.gambar', ['file' => basename($path)]);
        // ?v= memastikan foto terbaru langsung tampil setelah Admin menggantinya.
        return $url . '?v=' . ($this->updated_at?->timestamp ?? 0);
    }

    /** Jenis ilustrasi cadangan: laptop | proyektor | kamera | perkakas | default. */
    public function getJenisIlustrasiAttribute(): string
    {
        $jenis = $this->jenis === 'tripod' ? 'kamera' : $this->jenis;
        return in_array($jenis, self::JENIS_ILUSTRASI, true) ? $jenis : 'default';
    }

    /** Ilustrasi lokal bila foto belum diunggah (hanya tampilan, tidak disimpan ke database). */
    public function getIlustrasiUrlAttribute(): string
    {
        return $this->jenis_ilustrasi === 'default'
            ? self::gambarFallback()
            : asset('images/ilustrasi-alat/' . $this->jenis_ilustrasi . '.svg');
    }

    /** Foto Admin jika ada; kalau tidak, ilustrasi/placeholder. */
    public function getGambarUrlAttribute(): string
    {
        return $this->fotoUrl() ?? $this->ilustrasi_url;
    }

    /** true hanya bila Admin sudah mengunggah foto (dan filenya masih ada). */
    public function getAdaGambarAttribute(): bool
    {
        return $this->fotoUrl() !== null;
    }

    public static function gambarFallback(): string
    {
        return asset('images/placeholder-alat.svg');
    }

    /** habis | menipis | tersedia */
    public function getStatusStokAttribute(): string
    {
        $t = $this->tersedia;
        if ($t <= 0) return 'habis';
        if ($this->jumlah > 0 && ($t <= 2 || $t / $this->jumlah <= 0.25)) return 'menipis';
        return 'tersedia';
    }
}
