@extends('layouts.app')
@section('title', 'Tambah Admin - SIPEMAL')
@section('content')
<x-page-header title="Tambah admin" subtitle="Buat akun Admin baru. Akun ini akan memiliki akses penuh ke panel administrasi."/>
<form class="card form-card" method="POST" action="{{ route('admin.admins.store') }}" novalidate>
    @csrf
    <div class="form-grid">
        <div class="full"><x-field name="name" label="Nama lengkap" icon="user" required maxlength="100" placeholder="Nama admin"/></div>
        <div class="full"><x-field name="email" type="email" label="Email" icon="mail" required maxlength="150" placeholder="admin@sekolah.sch.id" autocomplete="off"/></div>
        <x-field name="password" type="password" label="Kata sandi" icon="lock" toggle strength required hint="Minimal 8 karakter." autocomplete="new-password"/>
        <x-field name="password_confirmation" type="password" label="Ulangi kata sandi" icon="lock" toggle required autocomplete="new-password"/>
    </div>
    <div class="note" style="margin-bottom:1rem"><b>Catatan:</b> role akun otomatis diatur sebagai <b>Admin</b> oleh sistem. Akun yang sudah ada tidak diubah.</div>
    <div class="form-actions">
        <button type="submit" class="btn"><x-icon name="save"/>Simpan admin</button>
        <a class="btn btn-outline" href="{{ route('admin.admins.index') }}">Batal</a>
    </div>
</form>
@endsection
