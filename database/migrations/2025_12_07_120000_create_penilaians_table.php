<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->string('semester', 10)->nullable();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('mengajar_id')->constrained('mengajars')->cascadeOnDelete();
            $table->decimal('nilai_sumatif', 8, 2)->nullable();
            $table->decimal('nilai_sts', 8, 2)->nullable();
            $table->timestamps();

            $table->unique([
                'tahun_ajaran_id',
                'semester',
                'kelas_id',
                'siswa_id',
                'mata_pelajaran_id',
                'guru_id',
            ], 'penilaians_unique_per_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};
