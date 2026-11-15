@extends('layouts.app')
@section('title', 'Detail Pengajuan - SIPEMAL')
@section('content')
@php
    $st = $peminjaman->status_peminjaman;
    $pg = $peminjaman->pengembalian;
    $late = $st === 'Disetujui' && !$pg && $peminjaman->tanggal_rencana_kembali->lt(today());
@endphp
<x-page-header title="Detail pengajuan" subtitle="Pengajuan #{{ $peminjaman->id }} oleh {{ $peminjaman->siswa->name ?? 'siswa' }}">
    <x-slot:actions><a class="btn btn-outline" href="{{ route('admin.peminjaman.index') }}"><x-icon name="arrow-left"/>Kembali</a></x-slot:actions>
</x-page-header>

<div class="split">
    <div class="stack">
        <section class="card">
            <div class="card-head"><h2><x-icon name="box"/>Daftar alat</h2><x-status-badge :status="$st" :late="$late"/></div>
            <div class="stack" style="gap:12px">
                @foreach($peminjaman->details as $d)
                    <div class="item-row">
                        @if($d->alat)<span class="thumb skeleton-img"><x-alat-image :alat="$d->alat"/></span>@endif
                        <div class="grow"><b>{{ $d->alat->nama_alat ?? 'Alat dihapus' }}</b><br><span class="small muted">{{ $d->alat->kategori->nama_kategori ?? '—' }} · {{ $d->alat->kode_alat ?? '' }}@if($d->alat && $st === 'Menunggu') · stok tersedia {{ $d->alat->tersedia }}@endif</span></div>
                        <div class="qty">{{ $d->jumlah_pinjam }}<small>unit</small></div>
                    </div>
                @endforeach
            </div>
            @if($peminjaman->catatan)<div class="note mt-2"><b>Catatan siswa:</b> {{ $peminjaman->catatan }}</div>@endif
        </section>

        @if($st === 'Menunggu')
        <section class="card" style="border-color:var(--amber-100);background:linear-gradient(180deg,#fff,var(--amber-50))">
            <div class="card-head"><h2><x-icon name="clock"/>Keputusan Admin</h2></div>
            <p class="muted small mb-2">Setujui jika stok mencukupi dan alat layak dipinjam. Stok akan terpotong otomatis setelah disetujui.</p>
            <div class="row wrap">
                <form method="POST" action="{{ route('admin.peminjaman.status', $peminjaman) }}" data-confirm="Pengajuan ini akan disetujui dan stok alat dikurangi." data-confirm-title="Setujui pengajuan?" data-confirm-label="Ya, setujui" data-confirm-tone="ok">@csrf @method('PATCH')
                    <input type="hidden" name="status_peminjaman" value="Disetujui"><button type="submit" class="btn btn-lg"><x-icon name="check-circle"/>Setujui</button>
                </form>
                <form method="POST" action="{{ route('admin.peminjaman.status', $peminjaman) }}" data-confirm="Siswa akan diberi tahu bahwa pengajuan ini ditolak." data-confirm-title="Tolak pengajuan?" data-confirm-label="Ya, tolak">@csrf @method('PATCH')
                    <input type="hidden" name="status_peminjaman" value="Ditolak"><button type="submit" class="btn btn-danger-soft btn-lg"><x-icon name="x-circle"/>Tolak</button>
                </form>
            </div>
        </section>
        @elseif($st === 'Disetujui')
            <a class="btn btn-lg" href="{{ route('admin.pengembalian.index') }}"><x-icon name="undo"/>Catat pengembalian alat ini</a>
        @endif
    </div>

    <div class="stack">
        <section class="card">
            <div class="card-head"><h2><x-icon name="user"/>Peminjam</h2></div>
            <div class="row mb-2"><x-avatar :name="$peminjaman->siswa->name ?? '?'" class="avatar-lg"/><div><b style="font-size:1.05rem">{{ $peminjaman->siswa->name ?? '—' }}</b><br><span class="small muted">{{ $peminjaman->siswa->email ?? '' }}</span></div></div>
            <div class="kv">
                <div><span>NIS</span><b>{{ $peminjaman->siswa->nis ?? '—' }}</b></div>
                <div><span>Kelas</span><b>{{ $peminjaman->siswa->kelas ?? '—' }}</b></div>
                <div><span>Tanggal pengajuan</span><b>{{ $peminjaman->tanggal_pengajuan->translatedFormat('d M Y') }}</b></div>
                <div><span>Rencana kembali</span><b>{{ $peminjaman->tanggal_rencana_kembali->translatedFormat('d M Y') }}</b></div>
            </div>
        </section>
        <section class="card">
            <div class="card-head"><h2><x-icon name="history"/>Alur status</h2></div>
            <ol class="timeline">
                <li class="done"><span class="tl-dot"><x-icon name="send" class="icon-sm"/></span><div><b>Diajukan</b><small>{{ $peminjaman->tanggal_pengajuan->translatedFormat('d M Y') }}</small></div></li>
                @if($st === 'Ditolak')
                    <li class="bad"><span class="tl-dot"><x-icon name="x" class="icon-sm"/></span><div><b>Ditolak</b><small>{{ $peminjaman->admin->name ?? 'Admin' }}</small></div></li>
                @else
                    <li class="{{ $st === 'Menunggu' ? 'now' : 'done' }}"><span class="tl-dot"><x-icon name="{{ $st === 'Menunggu' ? 'clock' : 'check' }}" class="icon-sm"/></span><div><b>{{ $st === 'Menunggu' ? 'Menunggu persetujuan' : 'Disetujui' }}</b>@if($st !== 'Menunggu')<small>{{ $peminjaman->admin->name ?? 'Admin' }}</small>@endif</div></li>
                    <li class="{{ $st === 'Dikembalikan' ? 'done' : '' }}"><span class="tl-dot"><x-icon name="undo" class="icon-sm"/></span><div><b>Dikembalikan</b><small>@if($pg){{ $pg->tanggal_pengembalian->translatedFormat('d M Y') }}@else Belum dikembalikan @endif</small></div></li>
                @endif
            </ol>
        </section>
        @if($pg)
        <section class="card">
            <div class="card-head"><h2><x-icon name="undo"/>Data pengembalian</h2></div>
            <div class="kv">
                <div><span>Kondisi alat</span><b><x-kondisi-badge :kondisi="$pg->kondisi_alat"/></b></div>
                <div><span>Denda</span><b>Rp {{ number_format((float) $pg->denda, 0, ',', '.') }}</b></div>
                <div><span>Keterangan</span><b>{{ $pg->keterangan ?: '—' }}</b></div>
            </div>
        </section>
        @endif
    </div>
</div>
@endsection
