@extends('layouts.app')
@section('title', 'Detail Peminjaman - SIPEMAL')
@section('content')
@php
    $st = $peminjaman->status_peminjaman; $pg = $peminjaman->pengembalian;
    $late = $st === 'Disetujui' && !$pg && $peminjaman->tanggal_rencana_kembali->lt(today());
@endphp
<x-page-header title="Detail peminjaman" subtitle="Pengajuan #{{ $peminjaman->id }}">
    <x-slot:actions><a class="btn btn-outline" href="{{ route('siswa.peminjaman.index') }}"><x-icon name="arrow-left"/>Kembali</a></x-slot:actions>
</x-page-header>
<div class="split">
    <section class="card">
        <div class="card-head"><h2><x-icon name="box"/>Alat yang dipinjam</h2><x-status-badge :status="$st" :late="$late"/></div>
        <div class="stack" style="gap:12px">
            @foreach($peminjaman->details as $d)
                <div class="item-row">
                    @if($d->alat)<span class="thumb skeleton-img"><x-alat-image :alat="$d->alat"/></span>@endif
                    <div class="grow"><b>{{ $d->alat->nama_alat ?? 'Alat dihapus' }}</b><br><span class="small muted">{{ $d->alat->kategori->nama_kategori ?? '—' }}</span></div>
                    <div class="qty">{{ $d->jumlah_pinjam }}<small>unit</small></div>
                </div>
            @endforeach
        </div>
        @if($peminjaman->catatan)<div class="note mt-2"><b>Catatanmu:</b> {{ $peminjaman->catatan }}</div>@endif
        @if($st === 'Ditolak')<div class="alert alert-error mt-2" style="margin-bottom:0"><x-icon name="x-circle"/><div>Pengajuan ini ditolak. Kamu bisa mengajukan kembali dengan alat atau jumlah yang berbeda.</div></div>@endif
        @if($late)<div class="alert alert-error mt-2" style="margin-bottom:0"><x-icon name="alert"/><div>Tanggal rencana kembali sudah lewat. Segera kembalikan alat ke Admin.</div></div>@endif
        @if($pg)
            <div class="kv mt-2">
                <div><span>Dikembalikan pada</span><b>{{ $pg->tanggal_pengembalian->translatedFormat('d M Y') }}</b></div>
                <div><span>Kondisi saat kembali</span><b><x-kondisi-badge :kondisi="$pg->kondisi_alat"/></b></div>
                <div><span>Denda</span><b>Rp {{ number_format((float) $pg->denda, 0, ',', '.') }}</b></div>
            </div>
        @endif
    </section>
    <div class="stack">
        <section class="card">
            <div class="card-head"><h2><x-icon name="calendar"/>Jadwal</h2></div>
            <div class="kv">
                <div><span>Tanggal pengajuan</span><b>{{ $peminjaman->tanggal_pengajuan->translatedFormat('d M Y') }}</b></div>
                <div><span>Rencana kembali</span><b>{{ $peminjaman->tanggal_rencana_kembali->translatedFormat('d M Y') }}</b></div>
            </div>
        </section>
        <section class="card">
            <div class="card-head"><h2><x-icon name="history"/>Alur status</h2></div>
            <ol class="timeline">
                <li class="done"><span class="tl-dot"><x-icon name="send" class="icon-sm"/></span><div><b>Diajukan</b><small>{{ $peminjaman->tanggal_pengajuan->translatedFormat('d M Y') }}</small></div></li>
                @if($st === 'Ditolak')
                    <li class="bad"><span class="tl-dot"><x-icon name="x" class="icon-sm"/></span><div><b>Ditolak</b></div></li>
                @else
                    <li class="{{ $st === 'Menunggu' ? 'now' : 'done' }}"><span class="tl-dot"><x-icon name="{{ $st === 'Menunggu' ? 'clock' : 'check' }}" class="icon-sm"/></span><div><b>{{ $st === 'Menunggu' ? 'Menunggu persetujuan' : 'Disetujui' }}</b></div></li>
                    <li class="{{ $st === 'Dikembalikan' ? 'done' : '' }}"><span class="tl-dot"><x-icon name="undo" class="icon-sm"/></span><div><b>Dikembalikan</b></div></li>
                @endif
            </ol>
        </section>
    </div>
</div>
@endsection
