
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nis_resmi', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 30)->unique();
            $table->string('nama_siswa', 100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_used')->default(false);
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });

        // Migrasikan NIS akun siswa yang sudah ada.
        $siswaLama = DB::table('users')
            ->where('role', 'siswa')
            ->whereNotNull('nis')
            ->get();

        foreach ($siswaLama as $siswa) {
            DB::table('nis_resmi')->insertOrIgnore([
                'nis' => $siswa->nis,
                'nama_siswa' => $siswa->name,
                'is_active' => true,
                'is_used' => true,
                'user_id' => $siswa->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Pastikan akun lama tetap ditandai sudah menggunakan NIS.
            DB::table('nis_resmi')
                ->where('nis', $siswa->nis)
                ->update([
                    'is_used' => true,
                    'user_id' => $siswa->id,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nis_resmi');
    }
};
