@extends('layouts.app')
@section('title', 'Pengembalian - SIPEMAL')
@section('content')
@php $aktif = $peminjamans->filter(fn ($p) => ! $p->pengembalian)->values(); @endphp
<x-page-header title="Pengembalian alat" subtitle="Catat kondisi alat dan denda (jika ada) saat siswa mengembalikan."/>
@if($aktif->isEmpty())
    <div class="card"><x-empty-state icon="undo" title="Tidak ada alat yang perlu dikembalikan" text="Peminjaman yang sudah disetujui dan belum dikembalikan akan muncul di sini."/></div>
@else
<div class="table-card" data-table data-page-size="8">
    <x-table-toolbar placeholder="Cari siswa atau alat…" key="kembali">
        <div class="chips" role="group" aria-label="Filter">
            <button type="button" class="chip active" data-filter="telat" data-value="" aria-pressed="true">Semua <small>{{ $aktif->count() }}</small></button>
            <button type="button" class="chip" data-filter="telat" data-value="1" aria-pressed="false">Terlambat <small>{{ $aktif->filter(fn ($p) => $p->tanggal_rencana_kembali->lt(today()))->count() }}</small></button>
        </div>
    </x-table-toolbar>
    <div class="table-scroll"><table>
        <thead><tr><th>Siswa</th><th>Alat</th><th>Rencana kembali</th><th>Status</th><th class="r">Aksi</th></tr></thead>
        <tbody>
        @foreach($aktif as $p)
            @php $late = $p->tanggal_rencana_kembali->lt(today()); $hari = $late ? $p->tanggal_rencana_kembali->diffInDays(today()) : 0; @endphp
            <tr data-row data-telat="{{ $late ? 1 : 0 }}">
                <td><div class="cell-main"><x-avatar :name="$p->siswa->name ?? '?'" class="avatar-sm"/><span><b>{{ $p->siswa->name ?? 'Siswa dihapus' }}</b><small>{{ $p->siswa->kelas ?? '' }}</small></span></div></td>
                <td class="small">{{ $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat dihapus') . ' (' . $d->jumlah_pinjam . ')')->implode(', ') }}</td>
                <td class="nowrap">{{ $p->tanggal_rencana_kembali->translatedFormat('d M Y') }}</td>
                <td>@if($late)<span class="badge badge-bad"><x-icon name="alert"/>Terlambat {{ $hari }} hari</span>@else<span class="badge badge-ok"><x-icon name="check-circle"/>Sedang dipinjam</span>@endif</td>
                <td class="r"><button type="button" class="btn btn-sm" data-modal-open="kembali-{{ $p->id }}"><x-icon name="undo"/>Catat pengembalian</button></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <x-table-empty title="Data tidak ditemukan"/>
    <x-table-foot/>
</div>
@endif
@endsection
@push('modals')
@foreach($aktif as $p)
    <x-modal id="kembali-{{ $p->id }}" title="Catat pengembalian" :subtitle="($p->siswa->name ?? 'Siswa') . ' · ' . $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat') . ' ×' . $d->jumlah_pinjam)->implode(', ')" icon="undo" :open="(string) old('peminjaman_id') === (string) $p->id">
        <form method="POST" action="{{ route('admin.pengembalian.store') }}">@csrf
            <input type="hidden" name="peminjaman_id" value="{{ $p->id }}">
            <div class="field"><label for="kondisi-{{ $p->id }}">Kondisi alat saat kembali</label><div class="control has-icon"><x-icon name="shield"/>
                <select id="kondisi-{{ $p->id }}" name="kondisi_alat" required>@foreach(['Baik','Rusak Ringan','Rusak Berat'] as $k)<option value="{{ $k }}" @selected((string) old('peminjaman_id') === (string) $p->id && old('kondisi_alat') === $k)>{{ $k }}</option>@endforeach</select></div></div>
            <div class="field"><label for="denda-{{ $p->id }}">Denda (Rp)</label><div class="control has-icon"><x-icon name="wallet"/>
                <input id="denda-{{ $p->id }}" type="number" name="denda" min="0" step="1000" value="{{ (string) old('peminjaman_id') === (string) $p->id ? old('denda', 0) : 0 }}" required></div><p class="field-hint">Isi 0 bila tidak ada denda.</p></div>
            <div class="field"><label for="ket-{{ $p->id }}">Keterangan (opsional)</label><div class="control has-icon"><x-icon name="file-text"/>
                <input id="ket-{{ $p->id }}" name="keterangan" placeholder="mis. kabel charger hilang" value="{{ (string) old('peminjaman_id') === (string) $p->id ? old('keterangan') : '' }}"></div></div>
            <div class="modal-actions"><button type="button" class="btn btn-outline" data-modal-close>Batal</button><button type="submit" class="btn"><x-icon name="save"/>Simpan pengembalian</button></div>
        </form>
    </x-modal>
@endforeach
@endpush
