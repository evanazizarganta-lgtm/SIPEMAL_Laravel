-- Untuk database SIPEMAL yang SUDAH berisi data (alternatif dari `php artisan migrate`).
-- Aman: hanya menambah satu kolom nullable, tidak menghapus atau mengubah data.
ALTER TABLE alats ADD COLUMN gambar VARCHAR(255) NULL AFTER deskripsi;
