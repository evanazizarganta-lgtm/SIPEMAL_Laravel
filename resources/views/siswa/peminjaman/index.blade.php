@extends('layouts.app')
@section('title', 'Peminjaman Saya - SIPEMAL')
@section('content')
@php $hit = $peminjamans->groupBy('status_peminjaman')->map->count(); @endphp
<x-page-header title="Peminjaman saya" subtitle="Pantau status semua pengajuan dan riwayat peminjamanmu.">
    <x-slot:actions><a class="btn" href="{{ route('siswa.peminjaman.create') }}"><x-icon name="plus"/>Ajukan peminjaman</a></x-slot:actions>
</x-page-header>
@if($peminjamans->isEmpty())
    <div class="card"><x-empty-state icon="swap" title="Belum ada pengajuan peminjaman" text="Pilih alat dari katalog lalu ajukan peminjaman pertamamu." :href="route('siswa.katalog')" label="Buka katalog"/></div>
@else
<div class="table-card" data-table data-page-size="8">
    <x-table-toolbar placeholder="Cari alat…" key="saya">
        <div class="chips" role="group" aria-label="Filter status">
            <button type="button" class="chip active" data-filter="status" data-value="" aria-pressed="true">Semua <small>{{ $peminjamans->count() }}</small></button>
            @foreach(['Menunggu','Disetujui','Dikembalikan','Ditolak'] as $s)<button type="button" class="chip" data-filter="status" data-value="{{ $s }}" aria-pressed="false">{{ $s }} <small>{{ $hit[$s] ?? 0 }}</small></button>@endforeach
        </div>
    </x-table-toolbar>
    <div class="table-scroll"><table>
        <thead><tr><th>Alat</th><th>Diajukan</th><th>Rencana kembali</th><th>Status</th><th class="r">Aksi</th></tr></thead>
        <tbody>
        @foreach($peminjamans as $p)
            @php $late = $p->status_peminjaman === 'Disetujui' && !$p->pengembalian && $p->tanggal_rencana_kembali->lt(today()); @endphp
            <tr data-row data-status="{{ $p->status_peminjaman }}">
                <td><div class="cell-main">@if($first = optional($p->details->first())->alat)<span class="thumb thumb-sm skeleton-img"><x-alat-image :alat="$first"/></span>@endif<span class="small"><b>{{ $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat dihapus') . ' (' . $d->jumlah_pinjam . ')')->implode(', ') }}</b></span></div></td>
                <td class="nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td>
                <td class="nowrap">{{ $p->tanggal_rencana_kembali->translatedFormat('d M Y') }}</td>
                <td><x-status-badge :status="$p->status_peminjaman" :late="$late"/></td>
                <td class="r"><a class="btn btn-light btn-sm" href="{{ route('siswa.peminjaman.show', $p) }}">Detail</a></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <x-table-empty title="Peminjaman tidak ditemukan"/>
    <x-table-foot/>
</div>
@endif
@endsection
