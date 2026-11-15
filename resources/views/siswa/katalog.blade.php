@extends('layouts.app')
@section('title', 'Katalog Alat - SIPEMAL')
@section('content')
<x-page-header title="Katalog alat" subtitle="Cari peralatan praktik yang tersedia dan ajukan peminjaman dengan mudah.">
    <x-slot:actions><a class="btn" href="{{ route('siswa.peminjaman.create') }}"><x-icon name="send"/>Ajukan peminjaman</a></x-slot:actions>
</x-page-header>

<div class="cat-pills" aria-label="Filter kategori">
    <a href="{{ route('siswa.katalog', request()->except('kategori_id', 'page')) }}" class="{{ request('kategori_id') ? '' : 'active' }}"><x-icon name="layers" class="icon-sm"/>Semua</a>
    @foreach($kategoris as $k)
        <a href="{{ route('siswa.katalog', array_merge(request()->except('page'), ['kategori_id' => $k->id])) }}" class="{{ (string) request('kategori_id') === (string) $k->id ? 'active' : '' }}">{{ $k->nama_kategori }}</a>
    @endforeach
</div>

<form method="GET" action="{{ route('siswa.katalog') }}" class="filter-bar">
    <div class="field"><label for="q">Cari alat</label><div class="control has-icon"><x-icon name="search"/><input id="q" name="q" value="{{ request('q') }}" placeholder="Nama atau kode alat…" type="search"></div></div>
    <div class="field"><label for="kategori_id">Kategori</label><select id="kategori_id" name="kategori_id"><option value="">Semua kategori</option>@foreach($kategoris as $kategori)<option value="{{ $kategori->id }}" @selected((string) request('kategori_id') === (string) $kategori->id)>{{ $kategori->nama_kategori }}</option>@endforeach</select></div>
    <div class="field"><label for="kondisi">Kondisi</label><select id="kondisi" name="kondisi"><option value="">Semua kondisi</option>@foreach(['Baik', 'Rusak Ringan', 'Rusak Berat'] as $kondisi)<option value="{{ $kondisi }}" @selected(request('kondisi') === $kondisi)>{{ $kondisi }}</option>@endforeach</select></div>
    <div class="actions row"><button class="btn" type="submit"><x-icon name="filter"/>Terapkan</button><a class="btn btn-outline" href="{{ route('siswa.katalog') }}">Reset</a></div>
</form>

<p class="small muted fw-6 mb-2" aria-live="polite">Menampilkan {{ $alats->firstItem() ?? 0 }}–{{ $alats->lastItem() ?? 0 }} dari {{ $alats->total() }} alat</p>

@if($alats->isEmpty())
    <div class="card"><x-empty-state icon="search" title="Alat tidak ditemukan" text="Coba ubah kata kunci atau filter pencarian.">
        <a class="btn btn-light" href="{{ route('siswa.katalog') }}">Tampilkan semua alat</a>
    </x-empty-state></div>
@else
    <div class="alat-grid">@foreach($alats as $alat)<x-alat-card :alat="$alat"/>@endforeach</div>
    <div class="pagination-wrap">{{ $alats->links() }}</div>
@endif
@endsection
@push('modals')<x-alat-detail-modal/>@endpush
