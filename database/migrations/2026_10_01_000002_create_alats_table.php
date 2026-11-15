<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('alats',function(Blueprint $table){$table->id();$table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnUpdate()->restrictOnDelete();$table->string('nama_alat',120);$table->string('kode_alat',50)->unique();$table->unsignedInteger('jumlah')->default(0);$table->string('kondisi',30)->default('Baik');$table->text('deskripsi')->nullable();$table->timestamps();});}
 public function down(): void { Schema::dropIfExists('alats');}
};
