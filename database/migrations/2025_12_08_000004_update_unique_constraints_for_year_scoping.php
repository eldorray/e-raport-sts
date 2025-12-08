<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropUnique('kelas_nama_unique');
            $table->unique(['tahun_ajaran_id', 'nama'], 'kelas_tahun_nama_unique');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropUnique('siswas_nis_unique');
            $table->dropUnique('siswas_nisn_unique');
            $table->unique(['tahun_ajaran_id', 'nis'], 'siswas_tahun_nis_unique');
            $table->unique(['tahun_ajaran_id', 'nisn'], 'siswas_tahun_nisn_unique');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropUnique('kelas_tahun_nama_unique');
            $table->unique('nama');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropUnique('siswas_tahun_nis_unique');
            $table->dropUnique('siswas_tahun_nisn_unique');
            $table->unique('nis');
            $table->unique('nisn');
        });
    }
};
