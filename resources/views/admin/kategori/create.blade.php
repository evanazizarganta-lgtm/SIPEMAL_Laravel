@extends('layouts.app')
@section('title', 'Tambah Kategori - SIPEMAL')
@section('content')
<x-page-header title="Tambah kategori" subtitle="Kelompokkan alat agar mudah dicari di katalog."/>
<form class="card" style="max-width:680px" method="POST" action="{{ route('admin.kategori.store') }}">@csrf @include('admin.kategori._form')</form>
@endsection
