@extends('layouts.app')
@section('title', 'Tambah Alat - SIPEMAL')
@section('content')
<x-page-header title="Tambah alat" subtitle="Daftarkan alat baru ke inventaris sekolah."/>
<form class="card form-card" method="POST" action="{{ route('admin.alat.store') }}" enctype="multipart/form-data">@csrf @include('admin.alat._form')</form>
@endsection
