
@extends('layouts.app')

@section('title', 'Tambah NIS - SIPEMAL')

@section('content')

<x-page-header
    title="Tambah NIS Resmi"
    subtitle="Masukkan NIS dan nama sesuai data resmi sekolah."
/>

<div class="card" style="max-width:680px">

    <form
        method="POST"
        action="{{ route('admin.nis.store') }}"
    >

        @csrf

        <div class="form-grid">

            <div class="full">

                <x-field
                    name="nis"
                    label="Nomor Induk Siswa"
                    icon="id"
                    required
                    maxlength="30"
                    placeholder="Contoh: 20260001"
                />

            </div>

            <div class="full">

                <x-field
                    name="nama_siswa"
                    label="Nama lengkap siswa"
                    icon="user"
                    required
                    maxlength="100"
                    placeholder="Nama sesuai data sekolah"
                />

            </div>

        </div>

        <div class="row wrap" style="margin-top:20px">

            <button class="btn" type="submit">
                <x-icon name="save"/>
                Simpan NIS
            </button>

            <a
                href="{{ route('admin.nis.index') }}"
                class="btn btn-light"
            >
                Kembali
            </a>

        </div>

    </form>

</div>

@endsection
