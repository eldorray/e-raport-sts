<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rapor_metadatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->string('semester', 20);
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('wali_guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->unsignedTinyInteger('sakit')->default(0);
            $table->unsignedTinyInteger('izin')->default(0);
            $table->unsignedTinyInteger('alpa')->default(0);
            $table->text('catatan_wali')->nullable();
            $table->text('tanggapan_ortu')->nullable();
            $table->json('prestasi')->nullable();
            $table->date('tanggal_rapor')->nullable();
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'semester', 'siswa_id'], 'rapor_metadata_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapor_metadatas');
    }
};
