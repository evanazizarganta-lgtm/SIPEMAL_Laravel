<x-field name="nama_kategori" label="Nama kategori" icon="layers" required maxlength="100" :value="$kategori->nama_kategori ?? ''" placeholder="mis. Perangkat Jaringan"/>
<x-field name="deskripsi" as="textarea" label="Deskripsi" icon="file-text" :value="$kategori->deskripsi ?? ''" placeholder="Jelaskan kelompok alat dalam kategori ini"/>
<div class="form-actions">
    <button type="submit" class="btn"><x-icon name="save"/>Simpan</button>
    <a class="btn btn-outline" href="{{ route('admin.kategori.index') }}">Batal</a>
</div>
