@extends('layouts.app')
@section('title', 'Dashboard Siswa - SIPEMAL')
@section('content')
@php $nama = \Illuminate\Support\Str::before(auth()->user()->name, ' '); @endphp

<section class="hero rise">
    <div>
        <span class="chip-hero"><x-icon name="sparkles" class="icon-sm"/>Portal peminjaman alat</span>
        <h1>Halo, {{ $nama }}! Alat apa yang kamu butuhkan hari ini?</h1>
        <p>Cari peralatan praktik, ajukan peminjaman, dan pantau statusnya dari satu tempat.</p>
        <div class="row wrap">
            <a class="btn btn-accent btn-lg" href="{{ route('siswa.katalog') }}"><x-icon name="search"/>Buka katalog</a>
            <a class="btn btn-lg" style="--bg:rgba(255,255,255,.16);--bg-h:rgba(255,255,255,.28);box-shadow:none;border-color:rgba(255,255,255,.35)" href="{{ route('siswa.peminjaman.index') }}"><x-icon name="swap"/>Status peminjaman</a>
        </div>
    </div>
    <div class="hero-art" aria-hidden="true"><img src="{{ asset('images/ilustrasi/hero-siswa.svg') }}" alt="" width="480" height="330"></div>
</section>

@if($jatuhTempo)
    @php $telat = $jatuhTempo->tanggal_rencana_kembali->lt(today()); @endphp
    <div class="alert {{ $telat ? 'alert-error' : 'alert-info' }} mt-2" role="status">
        <x-icon :name="$telat ? 'alert' : 'calendar'"/>
        <div>
            @if($telat) <strong>Pengembalian terlambat.</strong> @else <strong>Pengingat:</strong> @endif
            {{ $jatuhTempo->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat') . ' ×' . $d->jumlah_pinjam)->implode(', ') }}
            harus dikembalikan {{ $telat ? 'sejak' : 'pada' }} <strong>{{ $jatuhTempo->tanggal_rencana_kembali->translatedFormat('d F Y') }}</strong>.
            <a class="fw-6" href="{{ route('siswa.peminjaman.show', $jatuhTempo) }}">Lihat detail</a>
        </div>
    </div>
@endif

<div class="grid grid-4 mt-3 mb-3">
    <x-stat-card tone="mint" icon="swap" label="Peminjaman aktif" :value="$peminjamanAktif" :href="route('siswa.peminjaman.index')" foot="Lihat status"/>
    <x-stat-card tone="amber" icon="clock" label="Menunggu persetujuan" :value="$peminjamanMenunggu" :href="route('siswa.peminjaman.index')" foot="Pantau pengajuan" class="d1"/>
    <x-stat-card tone="lime" icon="undo" label="Riwayat pengembalian" :value="$riwayatKembali" :href="route('siswa.peminjaman.index')" foot="Lihat riwayat" class="d2"/>
    <x-stat-card tone="blue" icon="box" label="Jenis alat di katalog" :value="$totalAlat" :href="route('siswa.katalog')" foot="Jelajahi katalog" class="d3"/>
</div>

<section class="mb-3">
    <div class="page-head" style="margin-bottom:16px">
        <div><h2 style="font-size:1.3rem"><x-icon name="sparkles" style="color:var(--brand-600);display:inline;vertical-align:-3px"/> Rekomendasi untukmu</h2><p class="small">Alat dalam kondisi baik yang masih tersedia dan paling sering dipinjam.</p></div>
        <a class="btn btn-light btn-sm" href="{{ route('siswa.katalog') }}">Semua alat <x-icon name="arrow-right" class="icon-sm"/></a>
    </div>
    @if($rekomendasi->isEmpty())
        <div class="card"><x-empty-state icon="box" title="Belum ada alat yang tersedia" text="Katalog akan terisi setelah admin menambahkan alat."/></div>
    @else
        <div class="alat-grid cols-4">@foreach($rekomendasi as $alat)<x-alat-card :alat="$alat"/>@endforeach</div>
    @endif
</section>

<div class="dash">
    <section class="card span-7">
        <div class="card-head"><h2><x-icon name="box"/>Alat terbaru</h2><a class="btn btn-light btn-sm" href="{{ route('siswa.katalog') }}">Katalog</a></div>
        <div class="grid grid-2" style="gap:.625rem">
            @forelse($alatTerbaru as $a)
                <a class="alat-mini" href="{{ route('siswa.peminjaman.create', ['alat_id' => $a->id]) }}">
                    <span class="thumb skeleton-img"><x-alat-image :alat="$a"/></span>
                    <span class="grow"><b>{{ $a->nama_alat }}</b><small>{{ $a->kategori->nama_kategori ?? '—' }} · {{ $a->tersedia }} tersedia</small></span>
                </a>
            @empty
                <x-empty-state class="full" icon="box" title="Belum ada alat" text="Alat yang ditambahkan admin tampil di sini."/>
            @endforelse
        </div>
    </section>
    <section class="card span-5">
        <div class="card-head"><h2><x-icon name="history"/>Peminjaman terakhir</h2><a class="btn btn-light btn-sm" href="{{ route('siswa.peminjaman.index') }}">Semua</a></div>
        @forelse($peminjamanTerakhir as $p)
            <a class="dd-item" style="padding:.75rem 0" href="{{ route('siswa.peminjaman.show', $p) }}">
                <span class="grow"><b class="small" style="display:block">{{ $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat') . ' ×' . $d->jumlah_pinjam)->implode(', ') }}</b><span class="xs muted">Diajukan {{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</span></span>
                <x-status-badge :status="$p->status_peminjaman"/>
            </a>
        @empty
            <x-empty-state icon="swap" title="Belum ada peminjaman" text="Ajukan peminjaman pertamamu dari katalog." :href="route('siswa.peminjaman.create')" label="Ajukan peminjaman"/>
        @endforelse
        <div class="quick mt-2">
            <a href="{{ route('siswa.peminjaman.create') }}"><span class="q-ico"><x-icon name="send"/></span><span>Ajukan peminjaman<small>Pilih alat, jumlah, dan tanggal kembali</small></span><x-icon name="chevron-right" class="chev"/></a>
        </div>
    </section>
</div>
@endsection
@push('modals')<x-alat-detail-modal/>@endpush
