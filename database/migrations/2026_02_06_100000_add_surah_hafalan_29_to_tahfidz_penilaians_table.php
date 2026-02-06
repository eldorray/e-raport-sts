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
        Schema::table('tahfidz_penilaians', function (Blueprint $table) {
            // Hafalan Surah Juz 29 (11 surah) - disimpan sebagai JSON array
            $table->json('surah_hafalan_29')->nullable()->after('surah_hafalan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahfidz_penilaians', function (Blueprint $table) {
            $table->dropColumn('surah_hafalan_29');
        });
    }
};
