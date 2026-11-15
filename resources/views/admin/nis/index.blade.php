
@extends('layouts.app')

@section('title', 'NIS Resmi - SIPEMAL')

@section('content')
    <div class="page-header mb-3">
        <div>
            <h1>Data NIS Resmi</h1>
            <p class="muted">
                Kelola daftar NIS siswa yang berhak melakukan pendaftaran akun.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.nis.create') }}" class="btn btn-primary">
                + Tambah NIS
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h3>Daftar NIS Siswa</h3>
                <p class="small muted">
                    Data NIS yang digunakan untuk verifikasi pendaftaran siswa.
                </p>
            </div>
        </div>

        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Status Penggunaan</th>
                        <th>Status NIS</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($nis as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong>{{ $item->nis }}</strong>
                            </td>

                            <td>{{ $item->nama_siswa }}</td>

                            <td>
                                @if ($item->is_used)
                                    <span class="badge badge-info">
                                        Sudah Digunakan
                                    </span>
                                @else
                                    <span class="badge badge-neutral">
                                        Belum Digunakan
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($item->is_active)
                                    <span class="badge badge-ok">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge badge-neutral">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                <form
                                    action="{{ route('admin.nis.toggle', $item) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin mengubah status NIS ini?')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    @if ($item->is_active)
                                        <button type="submit" class="btn btn-sm btn-light">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Aktifkan
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <p class="muted">
                                    Belum ada data NIS resmi.
                                </p>

                                <a href="{{ route('admin.nis.create') }}">
                                    Tambahkan NIS sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($nis, 'links'))
            <div class="mt-3">
                {{ $nis->links() }}
            </div>
        @endif
    </div>
@endsection
