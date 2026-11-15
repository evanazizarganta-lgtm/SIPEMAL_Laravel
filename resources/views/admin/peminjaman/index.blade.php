@extends('layouts.app')
@section('title', 'Peminjaman - SIPEMAL')
@section('content')
@php $hit = $peminjamans->groupBy('status_peminjaman')->map->count(); @endphp
<x-page-header title="Pengajuan peminjaman" subtitle="Periksa dan proses permintaan peminjaman dari siswa."/>
@if($peminjamans->isEmpty())
    <div class="card"><x-empty-state icon="swap" title="Belum ada pengajuan" text="Pengajuan dari siswa akan tampil di sini untuk Anda periksa."/></div>
@else
<div class="table-card" data-table data-page-size="8">
    <x-table-toolbar placeholder="Cari siswa atau alat…" key="pinjam">
        <div class="chips" role="group" aria-label="Filter status">
            <button type="button" class="chip active" data-filter="status" data-value="" aria-pressed="true">Semua <small>{{ $peminjamans->count() }}</small></button>
            @foreach(['Menunggu','Disetujui','Dikembalikan','Ditolak'] as $st)
                <button type="button" class="chip" data-filter="status" data-value="{{ $st }}" aria-pressed="false">{{ $st }} <small>{{ $hit[$st] ?? 0 }}</small></button>
            @endforeach
        </div>
    </x-table-toolbar>
    <div class="table-scroll"><table>
        <thead><tr><th>Siswa</th><th>Alat</th><th>Pengajuan</th><th>Rencana kembali</th><th>Status</th><th class="r">Aksi</th></tr></thead>
        <tbody>
        @foreach($peminjamans as $p)
            @php $late = $p->status_peminjaman === 'Disetujui' && !$p->pengembalian && $p->tanggal_rencana_kembali->lt(today()); @endphp
            <tr data-row data-status="{{ $p->status_peminjaman }}">
                <td><div class="cell-main"><x-avatar :name="$p->siswa->name ?? '?'" class="avatar-sm"/><span><b>{{ $p->siswa->name ?? 'Siswa dihapus' }}</b><small>{{ $p->siswa->kelas ?? '' }}</small></span></div></td>
                <td class="small">{{ $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat dihapus') . ' (' . $d->jumlah_pinjam . ')')->implode(', ') }}</td>
                <td class="nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td>
                <td class="nowrap">{{ $p->tanggal_rencana_kembali->translatedFormat('d M Y') }}</td>
                <td><x-status-badge :status="$p->status_peminjaman" :late="$late"/></td>
                <td class="r"><a class="btn {{ $p->status_peminjaman === 'Menunggu' ? '' : 'btn-light' }} btn-sm" href="{{ route('admin.peminjaman.show', $p) }}">{{ $p->status_peminjaman === 'Menunggu' ? 'Periksa' : 'Detail' }}</a></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <x-table-empty title="Pengajuan tidak ditemukan"/>
    <x-table-foot/>
</div>
@endif
@endsection
