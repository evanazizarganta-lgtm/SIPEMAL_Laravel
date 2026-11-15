@php
    $user    = auth()->user();
    $isAdmin = $user->role === 'admin';
    $home    = route($isAdmin ? 'admin.dashboard' : 'siswa.dashboard');
    $groups = $isAdmin ? [
        'Utama' => [
            ['Dashboard', 'dashboard', 'admin.dashboard', 'admin.dashboard', null],
        ],
        'Inventaris' => [
    ['Data Alat', 'box', 'admin.alat.index', 'admin.alat.*', null],
    ['Kategori', 'layers', 'admin.kategori.index', 'admin.kategori.*', null],
],

'Data Master' => [
    ['Manajemen Kelas', 'school', 'admin.kelas.index', 'admin.kelas.*', null],
    ['NIS Resmi', 'id', 'admin.nis.index', 'admin.nis.*', null],
],
        'Transaksi' => [
            ['Peminjaman', 'swap', 'admin.peminjaman.index', 'admin.peminjaman.*', $navBadge ?? 0],
            ['Pengembalian', 'undo', 'admin.pengembalian.index', 'admin.pengembalian.*', null],
            ['Laporan', 'file-text', 'admin.laporan.index', 'admin.laporan.*', null],
        ],
        'Pengguna' => [
            ['Data Siswa', 'users', 'admin.siswa.index', 'admin.siswa.*', null],
            ['Manajemen Admin', 'shield', 'admin.admins.index', 'admin.admins.*', null],
            ['Profil', 'user', 'admin.profil', 'admin.profil*', null],
        ],
    ] : [
        'Menu siswa' => [
            ['Dashboard', 'dashboard', 'siswa.dashboard', 'siswa.dashboard', null],
            ['Katalog Alat', 'box', 'siswa.katalog', 'siswa.katalog', null],
            ['Peminjaman Saya', 'swap', 'siswa.peminjaman.index', 'siswa.peminjaman.*', $navBadge ?? 0],
            ['Profil', 'user', 'siswa.profil', 'siswa.profil*', null],
        ],
    ];
    $bottom = $isAdmin ? [
        ['Beranda', 'dashboard', 'admin.dashboard', 'admin.dashboard', null],
        ['Alat', 'box', 'admin.alat.index', 'admin.alat.*', null],
        ['Pinjam', 'swap', 'admin.peminjaman.index', 'admin.peminjaman.*', $navBadge ?? 0],
        ['Kembali', 'undo', 'admin.pengembalian.index', 'admin.pengembalian.*', null],
    ] : [
        ['Beranda', 'dashboard', 'siswa.dashboard', 'siswa.dashboard', null],
        ['Katalog', 'box', 'siswa.katalog', 'siswa.katalog', null],
        ['Pinjaman', 'swap', 'siswa.peminjaman.index', 'siswa.peminjaman.*', null],
        ['Profil', 'user', 'siswa.profil', 'siswa.profil*', null],
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
<div class="page-loading" aria-hidden="true"></div>
<div class="overlay" aria-hidden="true"></div>

<div class="app">
    <aside class="sidebar" id="sidebar" aria-label="Menu utama">
        <x-logo :href="$home" :sub="$isAdmin ? 'Panel Administrasi' : 'Portal Siswa'"/>
        <div class="nav-scroll">
            <nav class="nav">
                @foreach($groups as $label => $items)
                    <div class="nav-label">{{ $label }}</div>
                    @foreach($items as [$text, $icon, $routeName, $pattern, $count])
                        <a href="{{ route($routeName) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>
                            <x-icon :name="$icon"/><span>{{ $text }}</span>
                            @if($count)<span class="count">{{ $count }}</span>@endif
                        </a>
                    @endforeach
                @endforeach
            </nav>
        </div>
        <div class="side-card">
            @if($isAdmin)
                <h4>Ada pengajuan baru?</h4><p>Periksa dan proses permintaan siswa.</p>
                <a class="btn btn-accent btn-sm" href="{{ route('admin.peminjaman.index') }}">Periksa sekarang</a>
            @else
                <h4>Butuh alat praktik?</h4><p>Cari di katalog lalu ajukan dalam hitungan detik.</p>
                <a class="btn btn-accent btn-sm" href="{{ route('siswa.katalog') }}">Buka katalog</a>
            @endif
        </div>
    </aside>

    <div class="main-col">
        <header class="topbar">
            <button type="button" class="icon-btn menu-toggle" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu"><x-icon name="menu"/></button>
            <div class="crumbs">
                <nav aria-label="Breadcrumb"><ol>
                    @foreach($crumbs ?? [] as $c)
                        <li>@if($c['url'])<a href="{{ $c['url'] }}">{{ $c['label'] }}</a>@else<span @if($loop->last) aria-current="page" @endif>{{ $c['label'] }}</span>@endif</li>
                    @endforeach
                </ol></nav>
                <div class="top-title">{{ $pageTitle ?? 'SIPEMAL' }}</div>
            </div>
            <div class="top-spacer"></div>
            <div class="top-date"><x-icon name="calendar" class="icon-sm"/>{{ now()->translatedFormat('l, d F Y') }}</div>

            <div class="dropdown">
                <button type="button" class="icon-btn" data-dropdown-toggle aria-haspopup="true" aria-expanded="false" aria-label="Notifikasi{{ ($navNotifCount ?? 0) ? ', ' . $navNotifCount . ' baru' : '' }}">
                    <x-icon name="bell"/>@if(($navNotifCount ?? 0) > 0)<span class="dot">{{ $navNotifCount > 9 ? '9+' : $navNotifCount }}</span>@endif
                </button>
                <div class="dropdown-panel">
                    <div class="dd-head"><span>Notifikasi</span>@if(($navNotifCount ?? 0) > 0)<span class="badge badge-warn">{{ $navNotifCount }} baru</span>@endif</div>
                    @forelse($navNotifs ?? [] as $n)
                        <a class="dd-item" href="{{ $n['url'] }}">
                            <span class="dd-ico {{ $n['tone'] }}"><x-icon :name="$n['icon']"/></span>
                            <span class="grow"><b class="small" style="display:block">{{ $n['title'] }}</b><span class="small muted">{{ \Illuminate\Support\Str::limit($n['text'], 60) }}</span>@if($n['time'])<span class="xs muted" style="display:block">{{ $n['time'] }}</span>@endif</span>
                        </a>
                    @empty
                        <div class="dd-empty"><x-icon name="bell" class="icon-lg"/><br>Belum ada notifikasi.</div>
                    @endforelse
                    <a class="dd-link dd-foot" href="{{ route($isAdmin ? 'admin.peminjaman.index' : 'siswa.peminjaman.index') }}">Lihat semua peminjaman</a>
                </div>
            </div>

            <div class="dropdown">
                <button type="button" class="profile-btn" data-dropdown-toggle aria-haspopup="true" aria-expanded="false" aria-label="Menu profil">
                    <x-avatar :name="$user->name"/>
                    <span class="profile-meta"><b>{{ $user->name }}</b><span>{{ $isAdmin ? 'Administrator' : 'Siswa' . ($user->kelas ? ' · ' . $user->kelas : '') }}</span></span>
                </button>
                <div class="dropdown-panel dd-narrow">
                    <a class="dd-link" href="{{ route($isAdmin ? 'admin.profil' : 'siswa.profil') }}"><x-icon name="user"/>Profil saya</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="dd-link danger"><x-icon name="logout"/>Keluar</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="content" id="main">
            @if($errors->any())
                <div class="alert alert-error" role="alert">
                    <x-icon name="alert"/>
                    <div><strong>Periksa kembali isian Anda</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<nav class="bottom-nav" style="--n:{{ count($bottom) + ($isAdmin ? 1 : 0) }}" aria-label="Navigasi cepat">
    @foreach($bottom as [$text, $icon, $routeName, $pattern, $count])
        <a href="{{ route($routeName) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}">
            <x-icon :name="$icon"/>{{ $text }}@if($count)<span class="bn-dot">{{ $count }}</span>@endif
        </a>
    @endforeach
    @if($isAdmin)<button type="button" data-sidebar-toggle aria-label="Menu lainnya"><x-icon name="menu"/>Menu</button>@endif
</nav>

@include('partials.toasts')
@include('partials.confirm-modal')
@stack('modals')
<script src="{{ asset('js/sipemal.js') }}?v={{ @filemtime(public_path('js/sipemal.js')) }}" defer></script>
@stack('scripts')
</body>
</html>
