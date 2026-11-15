<!doctype html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
<main class="err-page" id="main">
    <span class="err-shape" style="width:320px;height:320px;left:-120px;bottom:-120px;background:var(--accent-100)"></span>
    <span class="err-shape" style="width:140px;height:140px;right:8%;top:12%;background:var(--brand-100);border-radius:38px;transform:rotate(18deg)"></span>
    <div class="err-box">
        <x-logo :href="url('/')" sub="Sistem Peminjaman Alat"/>
        <div class="err-code" aria-hidden="true">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <div class="row wrap" style="justify-content:center">
            @hasSection('actions')@yield('actions')@else
                <a class="btn" href="{{ url('/') }}"><x-icon name="home"/>Kembali ke beranda</a>
                <button class="btn btn-outline" type="button" onclick="history.length>1?history.back():location.href='{{ url('/') }}'"><x-icon name="arrow-left"/>Halaman sebelumnya</button>
            @endif
        </div>
    </div>
</main>
</body>
</html>
