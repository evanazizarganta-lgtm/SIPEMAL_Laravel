@props(['alat'])
<img src="{{ $alat->gambar_url }}" data-fallback="{{ \App\Models\Alat::gambarFallback() }}" alt="{{ $alat->ada_gambar ? 'Foto ' . $alat->nama_alat : 'Ilustrasi ' . $alat->nama_alat . ' (belum ada foto)' }}" loading="lazy" width="400" height="300" decoding="async">
