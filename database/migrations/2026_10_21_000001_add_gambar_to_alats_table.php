<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah kolom path foto alat. Aman untuk data lama:
 * kolom nullable, tidak ada data yang dihapus/diubah, dan dilewati jika kolom sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('alats', 'gambar')) {
            Schema::table('alats', function (Blueprint $table) {
                $table->string('gambar')->nullable()->after('deskripsi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('alats', 'gambar')) {
            Schema::table('alats', function (Blueprint $table) {
                $table->dropColumn('gambar');
            });
        }
    }
};
