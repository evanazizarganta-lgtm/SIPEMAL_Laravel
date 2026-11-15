
@extends('layouts.auth')

@section('title', 'Daftar - SIPEMAL')

@section('content')

<div class="auth-card wide">

    <h2>Buat akun siswa</h2>

    <p class="lead">
        Daftar menggunakan NIS resmi yang sudah didaftarkan Admin.
    </p>

    @if($errors->any())
        <div class="alert alert-error" role="alert">
            <x-icon name="alert"/>
            <div>
                <strong>Beberapa isian belum sesuai.</strong>
                Periksa kembali formulir Anda.
            </div>
        </div>
    @endif

    <form
        id="register-form"
        method="POST"
        action="{{ route('register.store') }}"
        novalidate
    >

        @csrf

        <div class="form-grid">

            {{-- NIS --}}
            <div class="full">

                <x-field
                    name="nis"
                    label="Nomor Induk Siswa (NIS)"
                    icon="id"
                    required
                    maxlength="30"
                    placeholder="Masukkan NIS resmi"
                />

                <button
                    type="button"
                    id="check-nis"
                    class="btn btn-sm"
                    style="margin-top:10px"
                >
                    <x-icon name="search"/>
                    Periksa NIS
                </button>

                <p
                    id="nis-message"
                    class="field-hint"
                    role="status"
                    aria-live="polite"
                >
                    Masukkan NIS kemudian tekan Periksa NIS.
                </p>

            </div>

            {{-- NAMA RESMI --}}
            <div class="full">

                <div class="field">

                    <label for="name">
                        Nama lengkap
                        <span class="req">*</span>
                    </label>

                    <div class="control has-icon">
                        <x-icon name="user"/>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value=""
                            placeholder="Nama akan terisi otomatis"
                            readonly
                            required
                            autocomplete="name"
                        >
                    </div>

                    <p class="field-hint">
                        Nama diambil dari database resmi dan tidak dapat diedit.
                    </p>

                </div>

            </div>

            {{-- KELAS --}}
            <div class="full">

                <x-field
                    name="kelas"
                    label="Pilih kelas"
                    icon="school"
                    as="select"
                    required
                >
                    <option value="">-- Pilih kelas --</option>

                    @foreach($kelas as $item)
                        <option
                            value="{{ $item->nama_kelas }}"
                            @selected(old('kelas') === $item->nama_kelas)
                        >
                            {{ $item->nama_kelas }}
                        </option>
                    @endforeach

                </x-field>

            </div>

            {{-- EMAIL --}}
            <div class="full">

                <x-field
                    name="email"
                    type="email"
                    label="Email"
                    icon="mail"
                    required
                    maxlength="150"
                    placeholder="nama@email.com"
                    autocomplete="email"
                />

            </div>

            {{-- PASSWORD --}}
            <x-field
                name="password"
                type="password"
                label="Kata sandi"
                icon="lock"
                toggle
                strength
                required
                hint="Minimal 8 karakter."
                placeholder="Buat kata sandi"
                autocomplete="new-password"
            />

            {{-- KONFIRMASI PASSWORD --}}
            <x-field
                name="password_confirmation"
                type="password"
                label="Ulangi kata sandi"
                icon="lock"
                toggle
                required
                placeholder="Ketik ulang kata sandi"
                autocomplete="new-password"
            />

        </div>

        <button
            class="btn btn-lg btn-block"
            type="submit"
        >
            <x-icon name="check-circle"/>
            Buat akun
        </button>

    </form>

    <p
        class="small muted"
        style="text-align:center;margin-top:20px"
    >
        Sudah punya akun?
        <a class="fw-6" href="{{ route('login') }}">
            Masuk di sini
        </a>
    </p>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const nisInput = document.getElementById('nis');
    const nameInput = document.getElementById('name');
    const kelasInput = document.getElementById('kelas');
    const checkButton = document.getElementById('check-nis');
    const message = document.getElementById('nis-message');
    const form = document.getElementById('register-form');

    let verifiedNis = '';

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content');

    nameInput.readOnly = true;

    function resetVerification() {

        verifiedNis = '';

        nameInput.value = '';

        message.textContent =
            'Periksa NIS untuk mengambil nama resmi siswa.';

        message.style.color = '';

    }

    nisInput.addEventListener('input', function () {

        resetVerification();

    });

    checkButton.addEventListener('click', async function () {

        const nis = nisInput.value.trim();

        if (!nis) {

            message.textContent = 'NIS wajib diisi.';

            nisInput.focus();

            return;

        }

        checkButton.disabled = true;

        checkButton.textContent = 'Memeriksa...';

        nameInput.value = '';

        verifiedNis = '';

        message.textContent = 'Sedang memeriksa NIS...';

        try {

            const response = await fetch(
                @json(route('register.check-nis')),
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },

                    body: JSON.stringify({
                        nis: nis
                    })
                }
            );

            const result = await response.json();

            // Pastikan input tidak berubah saat request berjalan.
            if (nisInput.value.trim() !== nis) {
                return;
            }

            if (!response.ok || !result.valid) {

                message.textContent =
                    result.message || 'NIS tidak valid.';

                message.style.color = '#dc2626';

                return;

            }

            nameInput.value = result.nama_siswa;

            verifiedNis = nis;

            message.textContent =
                'NIS valid. Nama resmi berhasil ditemukan.';

            message.style.color = '#16a34a';

        } catch (error) {

            message.textContent =
                'Gagal memeriksa NIS. Periksa koneksi dan coba lagi.';

            message.style.color = '#dc2626';

        } finally {

            checkButton.disabled = false;

            checkButton.innerHTML =
                '<svg class="icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg> Periksa NIS';

        }

    });

    form.addEventListener('submit', function (event) {

        if (
            !verifiedNis ||
            verifiedNis !== nisInput.value.trim() ||
            !nameInput.value
        ) {

            event.preventDefault();

            message.textContent =
                'Periksa NIS terlebih dahulu sebelum membuat akun.';

            message.style.color = '#dc2626';

            nisInput.focus();

            return;

        }

        if (!kelasInput.value) {

            event.preventDefault();

            kelasInput.focus();

        }

    });

});
</script>

@endsection
