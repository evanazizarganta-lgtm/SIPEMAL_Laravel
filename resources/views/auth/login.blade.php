@extends('layouts.auth')
@section('title', 'Masuk - SIPEMAL')
@section('content')
<div class="auth-card">
    <h2>Selamat datang kembali</h2>
    <p class="lead">Masuk untuk meminjam atau mengelola peralatan sekolah.</p>

    @if($errors->any())
        <div class="alert alert-error" role="alert"><x-icon name="x-circle"/><div><strong>Gagal masuk</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
    @endif

    <form method="POST" action="{{ route('login.authenticate') }}" novalidate>
        @csrf
        <x-field name="email" type="email" label="Email" icon="mail" required placeholder="nama@email.com" autocomplete="username" autofocus/>
        <x-field name="password" type="password" label="Kata sandi" icon="lock" toggle required placeholder="Masukkan kata sandi" autocomplete="current-password"/>
        <button class="btn btn-lg btn-block" type="submit"><x-icon name="logout" style="transform:scaleX(-1)"/>Masuk</button>
    </form>

    <div class="divider">atau</div>
    <p class="small muted" style="text-align:center;margin:0">Belum punya akun? <a class="fw-6" href="{{ route('register') }}">Daftar sebagai siswa</a></p>

    <div class="demo">
        <b><x-icon name="sparkles" class="icon-sm"/>Akun demo (klik untuk mengisi)</b>
        <button type="button" data-fill-email="admin@sipemal.test" data-fill-pass="admin12345"><span>Admin</span><span>admin@sipemal.test</span></button>
        <button type="button" data-fill-email="siswa@sipemal.test" data-fill-pass="siswa12345"><span>Siswa</span><span>siswa@sipemal.test</span></button>
    </div>
</div>
@endsection
