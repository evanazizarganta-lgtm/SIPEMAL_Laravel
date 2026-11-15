@extends('layouts.app')
@section('title', 'Data Siswa - SIPEMAL')
@section('content')
<x-page-header title="Data siswa" subtitle="Daftar akun siswa yang terdaftar di SIPEMAL."/>
@if($siswas->isEmpty())
    <div class="card"><x-empty-state icon="users" title="Belum ada siswa terdaftar" text="Siswa dapat mendaftar sendiri lewat halaman register."/></div>
@else
<div class="table-card" data-table data-page-size="10">
    <x-table-toolbar placeholder="Cari nama, NIS, atau email…" key="siswa">
        <select data-filter-select="kelas" aria-label="Filter kelas"><option value="">Semua kelas</option>@foreach($siswas->pluck('kelas')->filter()->unique()->sort() as $k)<option value="{{ \Illuminate\Support\Str::lower($k) }}">{{ $k }}</option>@endforeach</select>
    </x-table-toolbar>
    <div class="table-scroll"><table>
        <thead><tr><th>Siswa</th><th>NIS</th><th>Kelas</th><th>Email</th><th>Terdaftar</th></tr></thead>
        <tbody>
        @foreach($siswas as $s)
            <tr data-row data-kelas="{{ \Illuminate\Support\Str::lower($s->kelas ?? '') }}">
                <td><div class="cell-main"><x-avatar :name="$s->name" class="avatar-sm"/><b>{{ $s->name }}</b></div></td>
                <td>{{ $s->nis ?: '—' }}</td>
                <td>@if($s->kelas)<span class="badge badge-info">{{ $s->kelas }}</span>@else — @endif</td>
                <td>{{ $s->email }}</td>
                <td class="nowrap">{{ $s->created_at->translatedFormat('d M Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <x-table-empty title="Siswa tidak ditemukan"/>
    <x-table-foot/>
</div>
@endif
@endsection
