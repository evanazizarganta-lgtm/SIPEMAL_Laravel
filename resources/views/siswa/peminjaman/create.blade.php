@extends('layouts.app')
@section('title', 'Ajukan Peminjaman - SIPEMAL')
@section('content')
<x-page-header title="Ajukan peminjaman" subtitle="Pengajuan akan diperiksa Admin sebelum alat bisa diambil."/>
<div class="split split-form">
    <form class="card" method="POST" action="{{ route('siswa.peminjaman.store') }}">@csrf
        <x-field name="alat_id" as="select" label="Pilih alat" icon="box" required data-alat-select>
            <option value="">Pilih alat</option>
            @foreach($alats as $alat)
                <option value="{{ $alat->id }}" data-nama="{{ $alat->nama_alat }}" data-kat="{{ $alat->kategori->nama_kategori ?? '—' }}" data-stok="{{ $alat->tersedia }}" data-img="{{ $alat->gambar_url }}" @selected(old('alat_id', $selectedAlat ?? null) == $alat->id)>{{ $alat->nama_alat }} — tersedia {{ $alat->tersedia }} unit</option>
            @endforeach
        </x-field>
        <div class="form-grid">
            <x-field name="jumlah_pinjam" type="number" label="Jumlah dipinjam" icon="list" required min="1" :value="1" data-jumlah hint="Maksimal sesuai stok tersedia."/>
            <x-field name="tanggal_rencana_kembali" type="date" label="Rencana tanggal kembali" icon="calendar" required min="{{ now()->addDay()->toDateString() }}"/>
        </div>
        <x-field name="catatan" as="textarea" label="Catatan (opsional)" icon="file-text" placeholder="Keperluan peminjaman, misalnya praktik RPL atau dokumentasi kegiatan"/>
        <div class="form-actions">
            <button type="submit" class="btn btn-lg"><x-icon name="send"/>Kirim pengajuan</button>
            <a class="btn btn-outline btn-lg" href="{{ route('siswa.peminjaman.index') }}">Batal</a>
        </div>
    </form>
    <aside class="card preview-card">
        <div class="card-head"><h2><x-icon name="eye"/>Alat yang dipilih</h2></div>
        <div data-alat-preview hidden>
            <div class="alat-media skeleton-img"><img data-pv-img src="{{ \App\Models\Alat::gambarFallback() }}" data-fallback="{{ \App\Models\Alat::gambarFallback() }}" alt=""></div>
            <span class="alat-cat" data-pv-kat></span>
            <h3 style="font-size:1.15rem;margin:4px 0 12px" data-pv-nama></h3>
            <div class="stock"><div><small>Stok tersedia</small><b><span data-pv-stok></span> unit</b></div><span class="small muted fw-6" data-max-hint></span></div>
        </div>
        <x-empty-state icon="box" title="Pilih alat dulu" text="Pratinjau foto dan stok akan tampil di sini." style="padding:20px 0" data-pv-empty/>
        <div class="note mt-2"><b>Alur:</b> Kirim pengajuan → Admin memeriksa → alat diambil → kembalikan sebelum tanggal rencana.</div>
    </aside>
</div>
@endsection
