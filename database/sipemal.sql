CREATE DATABASE IF NOT EXISTS sipemal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sipemal;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'siswa',
  nis VARCHAR(30) NULL UNIQUE,
  kelas VARCHAR(50) NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE kategoris (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE,
  deskripsi TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE alats (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kategori_id BIGINT UNSIGNED NOT NULL,
  nama_alat VARCHAR(120) NOT NULL,
  kode_alat VARCHAR(50) NOT NULL UNIQUE,
  jumlah INT UNSIGNED NOT NULL DEFAULT 0,
  kondisi VARCHAR(30) NOT NULL DEFAULT 'Baik',
  deskripsi TEXT NULL,
  gambar VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT alats_kategori_fk FOREIGN KEY (kategori_id) REFERENCES kategoris(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE peminjamans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  admin_id BIGINT UNSIGNED NULL,
  tanggal_pengajuan DATE NOT NULL,
  tanggal_pinjam DATE NULL,
  tanggal_rencana_kembali DATE NOT NULL,
  status_peminjaman VARCHAR(30) NOT NULL DEFAULT 'Menunggu',
  catatan TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT peminjamans_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT peminjamans_admin_fk FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE detail_peminjamans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id BIGINT UNSIGNED NOT NULL,
  alat_id BIGINT UNSIGNED NOT NULL,
  jumlah_pinjam INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT detail_peminjamans_peminjaman_fk FOREIGN KEY (peminjaman_id) REFERENCES peminjamans(id) ON DELETE CASCADE,
  CONSTRAINT detail_peminjamans_alat_fk FOREIGN KEY (alat_id) REFERENCES alats(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE pengembalians (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id BIGINT UNSIGNED NOT NULL UNIQUE,
  admin_id BIGINT UNSIGNED NOT NULL,
  tanggal_pengembalian DATE NOT NULL,
  kondisi_alat VARCHAR(30) NOT NULL,
  denda DECIMAL(12,2) NOT NULL DEFAULT 0,
  keterangan TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT pengembalians_peminjaman_fk FOREIGN KEY (peminjaman_id) REFERENCES peminjamans(id) ON DELETE RESTRICT,
  CONSTRAINT pengembalians_admin_fk FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Setelah impor schema, jalankan seeder dengan `php artisan db:seed`
-- Cara paling mudah dan direkomendasikan: `php artisan migrate --seed`.
