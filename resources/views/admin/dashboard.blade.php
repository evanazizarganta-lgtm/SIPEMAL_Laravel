@extends('layouts.app')
@section('title', 'Dashboard Admin - SIPEMAL')
@section('content')
@php
    $maxChart = max(1, collect($chart)->flatMap(fn ($c) => [$c['pengajuan'], $c['kembali']])->max());
    $totalStatus = max(1, $statusCounts->sum());
    $warna = ['Menunggu' => '#f59e0b', 'Disetujui' => '#16a34a', 'Dikembalikan' => '#0d9488', 'Ditolak' => '#ef4444'];
    $acc = 0; $stops = [];
    foreach ($warna as $st => $w) {
        $n = (int) ($statusCounts[$st] ?? 0); if (! $n) continue;
        $from = $acc / $totalStatus * 100; $acc += $n; $to = $acc / $totalStatus * 100;
        $stops[] = "$w {$from}% {$to}%";
    }
    $donut = $statusCounts->sum() ? 'conic-gradient(' . implode(',', $stops) . ')' : 'var(--line-2)';
    $act = [
        'Menunggu'     => ['warn', 'clock', 'mengajukan peminjaman'],
        'Disetujui'    => ['ok', 'check-circle', 'peminjaman disetujui'],
        'Ditolak'      => ['bad', 'x-circle', 'pengajuan ditolak'],
        'Dikembalikan' => ['info', 'undo', 'mengembalikan alat'],
    ];
@endphp

<x-page-header title="Halo, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}" subtitle="Ringkasan operasional peminjaman alat sekolah hari ini.">
    <x-slot:actions>
        <a class="btn btn-light" href="{{ route('admin.laporan.index') }}"><x-icon name="file-text"/>Lihat laporan</a>
        <a class="btn" href="{{ route('admin.alat.create') }}"><x-icon name="plus"/>Tambah alat</a>
    </x-slot:actions>
</x-page-header>

@if($peminjamanMenunggu > 0 || $peminjamanTerlambat > 0)
<div class="alert alert-warn rise" role="status">
    <x-icon name="bell"/>
    <div>
        @if($peminjamanMenunggu > 0)<strong>{{ $peminjamanMenunggu }} pengajuan</strong> menunggu keputusan Anda.@endif
        @if($peminjamanTerlambat > 0)<strong>{{ $peminjamanTerlambat }} peminjaman</strong> melewati tanggal rencana kembali.@endif
        <a class="fw-6" href="{{ $peminjamanMenunggu > 0 ? route('admin.peminjaman.index') : route('admin.pengembalian.index') }}">Tindak lanjuti</a>
    </div>
</div>
@endif

<div class="grid grid-5 mb-3">
    <x-stat-card tone="hero" icon="box" label="Total alat" :value="$totalAlat" :href="route('admin.alat.index')" foot="Kelola alat"/>
    <x-stat-card tone="blue" icon="layers" label="Kategori" :value="$totalKategori" :href="route('admin.kategori.index')" foot="Lihat kategori" class="d1"/>
    <x-stat-card tone="mint" icon="swap" label="Peminjaman aktif" :value="$peminjamanAktif" :href="route('admin.pengembalian.index')" foot="Catat pengembalian" class="d2"/>
    <x-stat-card tone="amber" icon="clock" label="Menunggu persetujuan" :value="$peminjamanMenunggu" :href="route('admin.peminjaman.index')" foot="Periksa pengajuan" class="d3"/>
    <x-stat-card tone="lime" icon="undo" label="Pengembalian" :value="$totalPengembalian" :href="route('admin.laporan.index')" foot="Lihat laporan" class="d4"/>
</div>

<div class="dash">
    <section class="card span-8">
        <div class="card-head">
            <h2><x-icon name="trending-up"/>Aktivitas peminjaman</h2>
            <div class="legend"><span><i></i>Pengajuan</span><span><i class="l2"></i>Dikembalikan</span></div>
        </div>
        <div class="chart" role="img" aria-label="Grafik pengajuan dan pengembalian 6 bulan terakhir">
            @foreach($chart as $c)
                <div class="chart-col">
                    <div class="chart-bars">
                        <span class="bar" style="--h:{{ $c['pengajuan'] / $maxChart * 100 }}%;animation-delay:{{ $loop->index * 70 }}ms" data-v="{{ $c['pengajuan'] }}" title="{{ $c['pengajuan'] }} pengajuan"></span>
                        <span class="bar b2" style="--h:{{ $c['kembali'] / $maxChart * 100 }}%;animation-delay:{{ $loop->index * 70 + 40 }}ms" data-v="{{ $c['kembali'] }}" title="{{ $c['kembali'] }} dikembalikan"></span>
                    </div>
                    <span class="chart-x">{{ $c['label'] }}</span>
                </div>
            @endforeach
        </div>
        @if(collect($chart)->sum('pengajuan') + collect($chart)->sum('kembali') === 0)
            <p class="small muted" style="text-align:center;margin-top:.625rem">Belum ada aktivitas dalam 6 bulan terakhir. Grafik terisi saat siswa mulai mengajukan peminjaman.</p>
        @endif
    </section>

    <section class="card span-4">
        <div class="card-head"><h2><x-icon name="activity"/>Status peminjaman</h2></div>
        <div class="row wrap" style="gap:1.25rem;flex:1;justify-content:center">
            <div class="donut" style="--donut:{{ $donut }}" role="img" aria-label="Komposisi status peminjaman"><b>{{ $statusCounts->sum() }}<small>total</small></b></div>
            <div class="dl">
                @foreach($warna as $st => $w)<div><i style="background:{{ $w }}"></i>{{ $st }}<span>{{ (int) ($statusCounts[$st] ?? 0) }}</span></div>@endforeach
            </div>
        </div>
    </section>

    <section class="table-card span-8">
        <div class="card-head" style="padding:1.25rem 1.375rem 0;margin-bottom:.75rem">
            <h2><x-icon name="swap"/>Peminjaman terbaru</h2>
            <a class="btn btn-light btn-sm" href="{{ route('admin.peminjaman.index') }}">Lihat semua</a>
        </div>
        @if($peminjamanTerbaru->isEmpty())
            <x-empty-state icon="swap" title="Belum ada transaksi peminjaman" text="Pengajuan dari siswa akan muncul di sini."/>
        @else
        <div class="table-scroll"><table>
            <thead><tr><th>Siswa</th><th>Alat</th><th>Tanggal</th><th>Status</th><th class="r">Aksi</th></tr></thead>
            <tbody>
            @foreach($peminjamanTerbaru as $p)
                <tr>
                    <td><div class="cell-main"><x-avatar :name="$p->siswa->name ?? '?'" class="avatar-sm"/><b>{{ $p->siswa->name ?? 'Siswa dihapus' }}</b></div></td>
                    <td class="small">{{ $p->details->map(fn ($d) => ($d->alat->nama_alat ?? 'Alat dihapus') . ' ×' . $d->jumlah_pinjam)->implode(', ') }}</td>
                    <td class="nowrap">{{ $p->tanggal_pengajuan->translatedFormat('d M Y') }}</td>
                    <td><x-status-badge :status="$p->status_peminjaman"/></td>
                    <td class="r"><a class="btn btn-light btn-sm" href="{{ route('admin.peminjaman.show', $p) }}">Detail</a></td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        @endif
    </section>

    <section class="card span-4">
        <div class="card-head"><h2><x-icon name="history"/>Aktivitas terbaru</h2></div>
        @if($aktivitas->isEmpty())
            <p class="muted small">Belum ada aktivitas.</p>
        @else
        <ul class="feed">
            @foreach($aktivitas as $p)
                @php [$tone, $ico, $txt] = $act[$p->status_peminjaman] ?? ['info', 'info', $p->status_peminjaman]; @endphp
                <li>
                    <span class="dd-ico {{ $tone }}"><x-icon :name="$ico"/></span>
                    <div><b>{{ $p->siswa->name ?? 'Siswa' }}</b> {{ $txt }}<br><span class="small muted">{{ $p->details->map(fn ($d) => $d->alat->nama_alat ?? 'Alat')->implode(', ') }}</span><br><time>{{ $p->updated_at?->diffForHumans() }}</time></div>
                </li>
            @endforeach
        </ul>
        @endif
    </section>

    <section class="card span-4">
        <div class="card-head"><h2><x-icon name="alert"/>Stok menipis</h2></div>
        @forelse($stokMenipis as $a)
            <a class="alat-mini" href="{{ route('admin.alat.edit', $a) }}" style="margin-bottom:.625rem">
                <span class="thumb skeleton-img"><x-alat-image :alat="$a"/></span>
                <span class="grow"><b>{{ $a->nama_alat }}</b><small>{{ $a->kategori->nama_kategori ?? '—' }} · {{ $a->kode_alat }}</small></span>
                <span class="badge {{ $a->tersedia <= 0 ? 'badge-bad' : 'badge-warn' }}">{{ $a->tersedia }}/{{ $a->jumlah }}</span>
            </a>
        @empty
            <x-empty-state icon="shield" title="Stok aman" text="Belum ada alat dengan stok tersedia 2 unit atau kurang."/>
        @endforelse
    </section>

    <section class="card span-4">
        <div class="card-head"><h2><x-icon name="trending-up"/>Paling sering dipinjam</h2></div>
        @forelse($alatPopuler as $d)
            <a class="alat-mini" href="{{ route('admin.alat.edit', $d->alat) }}" style="margin-bottom:.625rem">
                <span class="thumb skeleton-img"><x-alat-image :alat="$d->alat"/></span>
                <span class="grow"><b>{{ $d->alat->nama_alat }}</b><small>{{ $d->alat->kategori->nama_kategori ?? '—' }}</small></span>
                <span class="badge badge-accent">{{ $d->total }} unit</span>
            </a>
        @empty
            <x-empty-state icon="trending-up" title="Belum ada data" text="Peringkat alat muncul setelah ada peminjaman."/>
        @endforelse
    </section>

    <section class="card span-4">
        <div class="card-head"><h2><x-icon name="sparkles"/>Aksi cepat</h2></div>
        <div class="quick">
            <a href="{{ route('admin.alat.create') }}"><span class="q-ico"><x-icon name="plus"/></span><span>Tambah alat<small>Daftarkan alat baru</small></span><x-icon name="chevron-right" class="chev"/></a>
            <a href="{{ route('admin.kategori.index') }}"><span class="q-ico accent"><x-icon name="layers"/></span><span>Kelola kategori<small>{{ $totalKategori }} kategori aktif</small></span><x-icon name="chevron-right" class="chev"/></a>
            <a href="{{ route('admin.peminjaman.index') }}"><span class="q-ico amber"><x-icon name="clock"/></span><span>Periksa pengajuan<small>{{ $peminjamanMenunggu }} menunggu keputusan</small></span><x-icon name="chevron-right" class="chev"/></a>
        </div>
    </section>
</div>
@endsection
