@extends('layouts.app')
@section('title', 'Kategori Alat - SIPEMAL')
@section('content')
@php $ikon = ['laptop'=>'laptop','kamera'=>'camera','tripod'=>'camera','proyektor'=>'projector','jaringan'=>'wifi','rpl'=>'cpu','lab'=>'flask','audio'=>'speaker','perkakas'=>'wrench','default'=>'folder']; @endphp
<x-page-header title="Kategori alat" subtitle="Kelola kelompok peralatan agar katalog tertata.">
    <x-slot:actions><a class="btn" href="{{ route('admin.kategori.create') }}"><x-icon name="plus"/>Tambah kategori</a></x-slot:actions>
</x-page-header>
@if($kategoris->isEmpty())
    <div class="card"><x-empty-state icon="layers" title="Belum ada kategori" text="Tambahkan kategori pertama, misalnya Laptop, Proyektor, atau Perangkat Jaringan." :href="route('admin.kategori.create')" label="Tambah kategori"/></div>
@else
<div data-table data-page-size="100">
    <div class="toolbar" style="border:1px solid var(--line);border-radius:var(--r-lg);margin-bottom:18px"><div class="search"><x-icon name="search"/><label class="sr-only" for="kat-search">Cari kategori</label><input id="kat-search" type="search" data-table-search placeholder="Cari kategori…" autocomplete="off"></div><span class="small muted fw-6" data-table-count></span></div>
    <div class="grid grid-3">
        @foreach($kategoris as $kategori)
            <article class="kat-card t{{ $loop->index % 4 }}" data-item>
                <span class="kat-ico"><x-icon :name="$ikon[\App\Models\Alat::jenisDari($kategori->nama_kategori)]"/></span>
                <h3>{{ $kategori->nama_kategori }}</h3>
                <p>{{ $kategori->deskripsi ?: 'Belum ada deskripsi.' }}</p>
                <div class="kat-foot">
                    <span class="badge badge-neutral"><x-icon name="box"/>{{ $kategori->alats_count }} alat</span>
                    <div class="row-actions">
                        <a class="btn btn-light btn-sm" href="{{ route('admin.kategori.edit', $kategori) }}" title="Edit {{ $kategori->nama_kategori }}" aria-label="Edit {{ $kategori->nama_kategori }}"><x-icon name="edit"/><span>Edit</span></a>
                        <x-delete-form :action="route('admin.kategori.destroy', $kategori)" :name="$kategori->nama_kategori" title="Hapus kategori?" :message="'Kategori “' . $kategori->nama_kategori . '” akan dihapus. Kategori yang masih memiliki alat tidak dapat dihapus.'"/>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <x-table-empty title="Kategori tidak ditemukan"/>
</div>
@endif
@endsection
