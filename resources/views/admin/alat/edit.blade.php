@extends('layouts.app')
@section('title', 'Edit Alat - SIPEMAL')
@section('content')
<x-page-header title="Edit alat" :subtitle="$alat->nama_alat . ' · ' . $alat->kode_alat"/>
<form class="card form-card" method="POST" action="{{ route('admin.alat.update', $alat) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.alat._form')</form>
@endsection
