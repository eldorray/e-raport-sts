<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tahfidz_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained()->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained()->onDelete('cascade');
            $table->string('semester'); // 'ganjil' / 'genap'
            $table->foreignId('pembimbing_id')->nullable()->constrained('gurus')->nullOnDelete();

            // Penilaian Pengetahuan
            $table->string('predikat_adab')->nullable(); // A/B/C/D
            $table->string('deskripsi_adab')->nullable();
            $table->string('predikat_tajwid')->nullable();
            $table->string('deskripsi_tajwid')->nullable();
            $table->string('predikat_makhorijul')->nullable();
            $table->string('deskripsi_makhorijul')->nullable();

            // Hafalan Surah (38 surah Juz 'Amma) - disimpan sebagai JSON array
            $table->json('surah_hafalan')->nullable(); // ['al-fatihah', 'an-nas', ...]

            $table->timestamps();

            // Unique constraint: satu siswa satu penilaian per semester per tahun
            $table->unique(['siswa_id', 'tahun_ajaran_id', 'semester'], 'tahfidz_siswa_tahun_semester_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahfidz_penilaians');
    }
};
