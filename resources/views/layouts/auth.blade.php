<!doctype html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
<div class="page-loading" aria-hidden="true"></div>
<div class="auth">
    <aside class="auth-art" aria-label="Tentang SIPEMAL">
        <x-logo :href="route('login')" sub="Sistem Peminjaman Alat"/>
        <div class="auth-copy">
            <h1>Alat siap, belajar jalan terus.</h1>
            <p>Pinjam laptop, proyektor, kamera, dan perlengkapan praktik sekolah tanpa antre dan tanpa catatan kertas.</p>
        </div>
        <div class="auth-visual" aria-hidden="true"><img src="{{ asset('images/ilustrasi/hero-siswa.svg') }}" alt="" width="480" height="330"></div>
        <div class="auth-points">
            <span><x-icon name="check-circle" class="icon-sm"/>Stok real-time</span>
            <span><x-icon name="clock" class="icon-sm"/>Persetujuan cepat</span>
            <span><x-icon name="file-text" class="icon-sm"/>Riwayat tercatat</span>
        </div>
    </aside>
    <main class="auth-main" id="main">
        @yield('content')
    </main>
</div>
@include('partials.toasts')
<script src="{{ asset('js/sipemal.js') }}?v={{ @filemtime(public_path('js/sipemal.js')) }}" defer></script>
</body>
</html>
