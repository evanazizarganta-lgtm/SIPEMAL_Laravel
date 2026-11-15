<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('peminjamans',function(Blueprint $table){$table->id();$table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();$table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();$table->date('tanggal_pengajuan');$table->date('tanggal_pinjam')->nullable();$table->date('tanggal_rencana_kembali');$table->string('status_peminjaman',30)->default('Menunggu');$table->text('catatan')->nullable();$table->timestamps();});}
 public function down(): void { Schema::dropIfExists('peminjamans');}
};
