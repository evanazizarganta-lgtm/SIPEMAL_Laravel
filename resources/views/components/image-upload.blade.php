@props(['name' => 'gambar', 'label' => 'Foto alat', 'alat' => null, 'maxMb' => 2])
@php
    $punya = $alat && $alat->ada_gambar;
    $err = $errors->first($name);
@endphp
<div class="field {{ $err ? 'invalid' : '' }}" data-upload data-max-mb="{{ $maxMb }}" data-initial="{{ $punya ? $alat->gambar_url : '' }}" data-placeholder="{{ \App\Models\Alat::gambarFallback() }}">
    <span class="label" id="{{ $name }}-label">{{ $label }}</span>
    <div class="upload">
        <div class="upload-preview skeleton-img"><img data-up-img src="{{ $punya ? $alat->gambar_url : \App\Models\Alat::gambarFallback() }}" data-fallback="{{ \App\Models\Alat::gambarFallback() }}" alt="Pratinjau foto alat" width="400" height="300"></div>
        <div class="upload-body">
            <input class="sr-only" type="file" id="{{ $name }}" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" aria-describedby="{{ $name }}-hint" aria-labelledby="{{ $name }}-label">
            <div class="row wrap" style="gap:.5rem">
                <label for="{{ $name }}" class="btn btn-light" data-up-pick><x-icon name="upload"/><span data-up-pick-text>{{ $punya ? 'Ganti foto' : 'Pilih foto' }}</span></label>
                <button type="button" class="btn btn-ghost" data-up-cancel hidden><x-icon name="x"/>Batalkan pilihan</button>
            </div>
            <p class="field-hint" id="{{ $name }}-hint">Format JPG, JPEG, PNG, atau WebP. Maksimal {{ $maxMb }} MB. Disarankan rasio 4:3.</p>
            <p class="small fw-6 muted" data-up-name hidden></p>
            <p class="field-error" data-up-error role="alert" hidden></p>
            @if($punya)
                <label class="check"><input type="checkbox" name="hapus_gambar" value="1" data-up-remove @checked(old('hapus_gambar'))> Hapus foto saat ini</label>
            @endif
            @if($err)<p class="field-error" role="alert"><x-icon name="alert" class="icon-sm"/>{{ $err }}</p>@endif
        </div>
    </div>
</div>
