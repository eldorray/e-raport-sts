<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mengajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->string('semester', 20)->nullable();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->unsignedInteger('jtm')->nullable();
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'semester', 'kelas_id', 'mata_pelajaran_id'], 'mengajars_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mengajars');
    }
};
