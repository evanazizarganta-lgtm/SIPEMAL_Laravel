
@extends('layouts.app')

@section('title', 'Manajemen Kelas - SIPEMAL')

@section('content')

<x-page-header
    title="Manajemen Kelas"
    subtitle="Kelola daftar kelas resmi SIPEMAL."
/>

<div class="grid grid-3 mb-3">

    <div class="card">
        <span class="small muted">Total kelas</span>
        <h2>{{ $kelas->count() }}</h2>
    </div>

    <div class="card">
        <span class="small muted">Kelas aktif</span>
        <h2>{{ $kelas->where('is_active', true)->count() }}</h2>
    </div>

    <div class="card">
        <span class="small muted">Kelas nonaktif</span>
        <h2>{{ $kelas->where('is_active', false)->count() }}</h2>
    </div>

</div>

<div class="card mb-3">

    <h3 class="mb-2">Tambah kelas baru</h3>

    <form
        method="POST"
        action="{{ route('admin.kelas.store') }}"
    >

        @csrf

        <div class="form-grid">

            <div class="full">

                <x-field
                    name="nama_kelas"
                    label="Nama kelas"
                    icon="school"
                    required
                    maxlength="50"
                    placeholder="Contoh: X RPL"
                />

            </div>

        </div>

        <button class="btn" type="submit">
            <x-icon name="plus"/>
            Tambah kelas
        </button>

    </form>

</div>

<div class="table-card">

    <div class="card-head">
        <h2>Daftar kelas resmi</h2>
    </div>

    <div class="table-scroll">

        <table>

            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama kelas</th>
                    <th>Status</th>
                    <th class="r">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($kelas as $item)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <b>{{ $item->nama_kelas }}</b>
                        </td>

                        <td>

                            @if($item->is_active)

                                <span class="badge badge-ok">
                                    Aktif
                                </span>

                            @else

                                <span class="badge badge-neutral">
                                    Nonaktif
                                </span>

                            @endif

                        </td>

                        <td class="r">

                            <form
                                method="POST"
                                action="{{ route('admin.kelas.toggle', $item) }}"
                            >

                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-sm"
                                >
                                    {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4">
                            Belum ada kelas.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
