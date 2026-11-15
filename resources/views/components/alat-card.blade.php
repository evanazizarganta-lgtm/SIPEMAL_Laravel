@props(['alat', 'compact' => false])
@php
$tersedia = $alat->tersedia;
$habis = $tersedia <= 0;
$persen = $alat->jumlah > 0 ? round($tersedia / $alat->jumlah * 100) : 0;
$kelas = $habis ? 'zero' : ($alat->status_stok === 'menipis' ? 'low' : '');
@endphp
<article {{ $attributes->merge(['class' => 'alat-card' . ($habis ? ' is-out' : '')]) }} data-item>
    <div class="alat-media skeleton-img">
        <x-alat-image :alat="$alat"/>
        @if(! $alat->ada_gambar && $alat->jenis_ilustrasi !== 'default')<span class="media-note">Ilustrasi · belum ada foto</span>@endif
        <div class="tl"><x-kondisi-badge :kondisi="$alat->kondisi"/></div>
        <div class="tr">
            @if($habis)<span class="badge badge-bad"><x-icon name="x-circle"/>Habis</span>
            @elseif($alat->status_stok === 'menipis')<span class="badge badge-warn"><x-icon name="alert"/>Hampir habis</span>
            @else<span class="badge badge-accent"><x-icon name="check-circle"/>Tersedia</span>@endif
        </div>
    </div>
    <div class="alat-body">
        <div class="row row-between">
            <span class="alat-cat"><x-icon name="tag" class="icon-sm"/>{{ $alat->kategori->nama_kategori ?? 'Tanpa kategori' }}</span>
            <span class="alat-code">{{ $alat->kode_alat }}</span>
        </div>
        <h3>{{ $alat->nama_alat }}</h3>
        <p class="alat-desc">{{ $alat->deskripsi ?: 'Peralatan untuk mendukung kegiatan pembelajaran dan praktik.' }}</p>
        <div class="stock">
            <div><small>Stok tersedia</small><b>{{ $tersedia }} <span class="small muted" style="font-weight:600">/ {{ $alat->jumlah }} unit</span></b></div>
            <div class="progress {{ $kelas }}" style="--w:{{ $persen }}%" role="progressbar" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100" aria-label="Ketersediaan stok"><i></i></div>
        </div>
        <div class="alat-actions">
            <button type="button" class="btn btn-outline" data-alat-detail
                data-nama="{{ $alat->nama_alat }}" data-kode="{{ $alat->kode_alat }}" data-kat="{{ $alat->kategori->nama_kategori ?? '—' }}"
                data-kondisi="{{ $alat->kondisi }}" data-stok="{{ $tersedia }}" data-total="{{ $alat->jumlah }}"
                data-img="{{ $alat->gambar_url }}" data-desc="{{ $alat->deskripsi ?: 'Peralatan untuk mendukung kegiatan pembelajaran dan praktik.' }}"
                data-url="{{ route('siswa.peminjaman.create', ['alat_id' => $alat->id]) }}" data-habis="{{ $habis ? 1 : 0 }}">
                <x-icon name="eye"/>Detail
            </button>
            @if($habis)
                <button type="button" class="btn" disabled><x-icon name="clock"/>Stok habis</button>
            @else
                <a class="btn" href="{{ route('siswa.peminjaman.create', ['alat_id' => $alat->id]) }}"><x-icon name="send"/>Ajukan pinjam</a>
            @endif
        </div>
    </div>
</article>
