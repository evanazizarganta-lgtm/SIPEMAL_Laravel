@extends('layouts.app')
@section('title', 'Edit Kategori - SIPEMAL')
@section('content')
<x-page-header title="Edit kategori" :subtitle="$kategori->nama_kategori"/>
<form class="card" style="max-width:680px" method="POST" action="{{ route('admin.kategori.update', $kategori) }}">@csrf @method('PUT') @include('admin.kategori._form')</form>
@endsection
