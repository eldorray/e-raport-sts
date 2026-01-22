<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_ajarans', function (Blueprint $table) {
            // Drop the existing unique constraint on 'nama' column
            $table->dropUnique(['nama']);

            // Add a new composite unique constraint on 'nama' and 'semester'
            $table->unique(['nama', 'semester'], 'tahun_ajarans_nama_semester_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_ajarans', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('tahun_ajarans_nama_semester_unique');

            // Restore the original unique constraint on 'nama'
            $table->unique('nama');
        });
    }
};
