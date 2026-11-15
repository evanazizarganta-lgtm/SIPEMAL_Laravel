@extends('layouts.app')
@section('title', 'Profil - SIPEMAL')
@section('content')
@php $isAdmin = auth()->user()->role === 'admin'; @endphp
<x-page-header title="Profil saya" subtitle="Perbarui informasi akun dan kata sandi."/>
<div class="split split-profile">
    <aside class="card card-flush profile-card">
        <div class="profile-hero"></div>
        <div class="body">
            <x-avatar :name="$user->name" class="avatar-lg"/>
            <h2 style="margin-top:12px;font-size:1.3rem">{{ $user->name }}</h2>
            <span class="badge {{ $isAdmin ? 'badge-info' : 'badge-accent' }} mt-1">{{ $isAdmin ? 'Administrator' : 'Siswa' }}</span>
            <div class="kv mt-2">
                <div><span>Email</span><b>{{ $user->email }}</b></div>
                @unless($isAdmin)<div><span>NIS</span><b>{{ $user->nis ?: '—' }}</b></div><div><span>Kelas</span><b>{{ $user->kelas ?: '—' }}</b></div>@endunless
                <div><span>Bergabung</span><b>{{ $user->created_at?->translatedFormat('d M Y') }}</b></div>
            </div>
        </div>
    </aside>
    <form class="card" method="POST" action="{{ $isAdmin ? route('admin.profil.update') : route('siswa.profil.update') }}">@csrf @method('PUT')
        <div class="card-head"><h2><x-icon name="edit"/>Ubah data akun</h2></div>
        <x-field name="name" label="Nama" icon="user" required :value="$user->name"/>
        <x-field name="email" type="email" label="Email" icon="mail" required :value="$user->email"/>
        <div class="form-grid">
            <x-field name="password" type="password" label="Kata sandi baru (opsional)" icon="lock" toggle strength placeholder="Kosongkan jika tidak diganti" hint="Minimal 8 karakter." autocomplete="new-password"/>
            <x-field name="password_confirmation" type="password" label="Ulangi kata sandi baru" icon="lock" toggle autocomplete="new-password"/>
        </div>
        <div class="form-actions"><button type="submit" class="btn"><x-icon name="save"/>Simpan profil</button></div>
    </form>
</div>
@endsection
