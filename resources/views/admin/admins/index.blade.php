@extends('layouts.app')
@section('title', 'Manajemen Admin - SIPEMAL')
@section('content')
<x-page-header title="Manajemen admin" subtitle="Daftar akun Admin yang dapat mengelola SIPEMAL.">
    <x-slot:actions><a class="btn" href="{{ route('admin.admins.create') }}"><x-icon name="plus"/>Tambah admin</a></x-slot:actions>
</x-page-header>
<div class="table-card" data-table data-page-size="10">
    <x-table-toolbar placeholder="Cari nama atau email…" key="admins"/>
    <div class="table-scroll"><table>
        <thead><tr><th>Admin</th><th>Email</th><th>Role</th><th>Dibuat</th></tr></thead>
        <tbody>
        @foreach($admins as $a)
            <tr data-row>
                <td><div class="cell-main"><x-avatar :name="$a->name" class="avatar-sm"/><b>{{ $a->name }}</b>@if($a->id === auth()->id())<span class="badge badge-accent">Anda</span>@endif</div></td>
                <td>{{ $a->email }}</td>
                <td><span class="badge badge-info"><x-icon name="shield"/>Admin</span></td>
                <td class="nowrap">{{ $a->created_at?->translatedFormat('d M Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <x-table-empty title="Admin tidak ditemukan"/>
    <x-table-foot/>
</div>
@endsection
