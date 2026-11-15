@extends('layouts.app')
@section('title', 'Laporan - SIPEMAL')
@section('content')
@php $hit = $peminjamans->groupBy('status_peminjaman')->map->count(); @endphp
<x-page-header title="Laporan" subtitle="Ringkasan transaksi peminjaman dan pengembalian.">
    <x-slot:actions>
        <a class="btn btn-light no-print" href="{{ route('admin.laporan.export', request()->only('from', 'to')) }}"><x-icon name="download"/>Ekspor CSV</a>
        <button type="button" class="btn no-print" data-print data-no-loading><x-icon name="printer"/>Cetak / Simpan PDF</button>
    </x-slot:actions>
</x-page-header>
<p class="print-only small" style="margin-bottom:12px">Laporan SIPEMAL · dicetak {{ now()->translatedFormat('d F Y H:i') }}@if($from) · dari {{ $from }}@endif @if($to) · sampai {{ $to }}@endif</p>

<div class="grid grid-4 mb-3">
    <x-stat-card tone="hero" icon="box" label="Total jenis alat" :value="$totalAlat"/>
    <x-stat-card tone="mint" icon="swap" label="Transaksi pada rentang ini" :value="$peminjamans->count()" class="d1"/>
    <x-stat-card tone="lime" icon="undo" label="Total pengembalian" :value="$totalPengembalian" class="d2"/>
    <x-stat-card tone="amber" icon="wallet" label="Total denda" :value="'Rp ' . number_format((float) $totalDenda, 0, ',', '.')" class="d3"/>
</div>

<form method="GET" class="filter-bar is-dates no-print">
    <div class="field"><label for="from">Dari tanggal</label><div class="control has-icon"><x-icon name="calendar"/><input id="from" type="date" name="from" value="{{ $from }}"></div></div>
    <div class="field"><label for="to">Sampai tanggal</label><div class="control has-icon"><x-icon name="calendar"/><input id="to" type="date" name="to" value="{{ $to }}"></div></div>
    <div class="actions row"><button class="btn" type="submit"><x-icon name="filter"/>Filter</button><a class="btn btn-outline" href="{{ route('admin.laporan.index') }}">Reset</a></div>
</form>

@if($peminjamans->isEmpty())
    <div class="card"><x-empty-state icon="file-text" title="Tidak ada data pada rentang ini" text="Ubah rentang tanggal atau reset filter untuk melihat semua transaksi."/></div>
@else
<div class="table-card" data-table data-page-size="10">
    <x-table-toolbar placeholder="Cari siswa atau alat…" key="laporan">
        <div class="chips" role="group" aria-label="Filter status">
            <button type="button" class="chip active" data-filter="status" data-value="" aria-pressed="true">Semua <small>{{ $peminjamans->count() }}</small></button>
            @foreach(['Menunggu','Disetujui','Dikembalikan','Ditolak'] as $s)<button type="button" class="chip" data-filter="status" data-value="{{ $s }}" aria-pressed="false">{{ $s }} <small>{{ $hit[$s] ?? 0 }}</small></button>@endforeach
        </div>
    </x-table-toolbar>
    <div class="table-scroll"><table>
        <thead><tr><th>Tanggal</th><th>Siswa</th><th>Alat</th><th>Status</th><th class="r">Denda</th></tr></thead>
        <tbody>
        @foreach($peminjamans as $p)
            <tr data-row data-status="{{ $p->status_peminjaman }}">
                <td class="nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td>
                <td><b>{{ $p->siswa->name ?? 'Siswa dihapus' }}</b></td>
                <td class="small">{{ $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat dihapus') . ' ×' . $d->jumlah_pinjam)->implode(', ') }}</td>
                <td><x-status-badge :status="$p->status_peminjaman"/></td>
                <td class="r nowrap">Rp {{ number_format((float) optional($p->pengembalian)->denda, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <x-table-empty title="Data tidak ditemukan"/>
    <x-table-foot/>
</div>
@endif
@endsection
