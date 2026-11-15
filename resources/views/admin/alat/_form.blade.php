<x-image-upload :alat="$alat ?? null"/>
<div class="form-grid">
    <x-field name="nama_alat" label="Nama alat" icon="box" required :value="$alat->nama_alat ?? ''" placeholder="mis. Proyektor Epson EB-X500"/>
    <x-field name="kode_alat" label="Kode alat" icon="tag" required :value="$alat->kode_alat ?? ''" placeholder="mis. ALT-014" hint="Kode unik untuk setiap alat."/>
    <x-field name="kategori_id" label="Kategori" icon="layers" as="select" required>
        <option value="">Pilih kategori</option>
        @foreach($kategoris as $k)<option value="{{ $k->id }}" @selected(old('kategori_id', $alat->kategori_id ?? '') == $k->id)>{{ $k->nama_kategori }}</option>@endforeach
    </x-field>
    <x-field name="jumlah" type="number" label="Jumlah total" icon="list" required min="0" :value="$alat->jumlah ?? 0" hint="Stok tersedia dihitung otomatis dari peminjaman aktif."/>
    <div class="full">
        <x-field name="kondisi" label="Kondisi" icon="shield" as="select" required>
            @foreach(['Baik','Rusak Ringan','Rusak Berat'] as $k)<option value="{{ $k }}" @selected(old('kondisi', $alat->kondisi ?? 'Baik') === $k)>{{ $k }}</option>@endforeach
        </x-field>
    </div>
    <div class="full"><x-field name="deskripsi" as="textarea" label="Deskripsi" icon="file-text" :value="$alat->deskripsi ?? ''" placeholder="Spesifikasi atau catatan singkat tentang alat ini"/></div>
</div>
<div class="form-actions">
    <button type="submit" class="btn"><x-icon name="save"/>Simpan</button>
    <a class="btn btn-outline" href="{{ route('admin.alat.index') }}">Batal</a>
</div>
