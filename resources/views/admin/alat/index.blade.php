@extends('layouts.app')
@section('title', 'Data Alat - SIPEMAL')
@section('content')
<x-page-header title="Data alat" subtitle="Kelola inventaris alat: stok, kondisi, dan kategori.">
    <x-slot:actions><a class="btn" href="{{ route('admin.alat.create') }}"><x-icon name="plus"/>Tambah alat</a></x-slot:actions>
</x-page-header>

@if($alats->isEmpty())
    <div class="card"><x-empty-state icon="box" title="Belum ada data alat" text="Tambahkan alat pertama agar siswa dapat meminjamnya." :href="route('admin.alat.create')" label="Tambah alat"/></div>
@else
<div class="table-card" data-table data-page-size="8">
    <x-table-toolbar placeholder="Cari nama atau kode alat…" key="alat">
        <select data-filter-select="kategori" aria-label="Filter kategori"><option value="">Semua kategori</option>
            @foreach($alats->pluck('kategori.nama_kategori')->unique()->filter()->sort() as $nk)<option value="{{ \Illuminate\Support\Str::lower($nk) }}">{{ $nk }}</option>@endforeach
        </select>
        <select data-filter-select="kondisi" aria-label="Filter kondisi"><option value="">Semua kondisi</option>@foreach(['Baik','Rusak Ringan','Rusak Berat'] as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach</select>
    </x-table-toolbar>
    <div class="table-scroll"><table>
        <thead><tr><th>Alat</th><th>Kategori</th><th>Ketersediaan</th><th>Kondisi</th><th class="r">Aksi</th></tr></thead>
        <tbody>
        @foreach($alats as $alat)
            @php $persen = $alat->jumlah > 0 ? round($alat->tersedia / $alat->jumlah * 100) : 0; $kls = $alat->tersedia <= 0 ? 'zero' : ($alat->status_stok === 'menipis' ? 'low' : ''); @endphp
            <tr data-row data-kategori="{{ \Illuminate\Support\Str::lower($alat->kategori->nama_kategori ?? '') }}" data-kondisi="{{ $alat->kondisi }}">
                <td><div class="cell-main"><span class="thumb skeleton-img"><x-alat-image :alat="$alat"/></span><span><b>{{ $alat->nama_alat }}</b><small>{{ $alat->kode_alat }}</small></span></div></td>
                <td><span class="badge badge-info"><x-icon name="tag"/>{{ $alat->kategori->nama_kategori ?? '—' }}</span></td>
                <td style="min-width:170px"><div class="row" style="gap:10px"><div class="progress {{ $kls }} grow" style="--w:{{ $persen }}%"><i></i></div><span class="small fw-6 nowrap">{{ $alat->tersedia }} / {{ $alat->jumlah }}</span></div></td>
                <td><x-kondisi-badge :kondisi="$alat->kondisi"/></td>
                <td class="r"><div class="row-actions">
                    <a class="btn btn-light btn-sm" href="{{ route('admin.alat.edit', $alat) }}" title="Edit {{ $alat->nama_alat }}" aria-label="Edit {{ $alat->nama_alat }}"><x-icon name="edit"/><span>Edit</span></a>
                    <x-delete-form :action="route('admin.alat.destroy', $alat)" :name="$alat->nama_alat" title="Hapus alat?" :message="'Alat “' . $alat->nama_alat . '” akan dihapus dari inventaris beserta fotonya. Tindakan ini tidak dapat dibatalkan.'"/>
                </div></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <x-table-empty/>
    <x-table-foot/>
</div>
@endif
@endsection
