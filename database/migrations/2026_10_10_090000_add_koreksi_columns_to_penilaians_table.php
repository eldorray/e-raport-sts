<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak koreksi nilai oleh admin.
 *
 * `dikoreksi_oleh` menyimpan user admin yang terakhir mengubah baris nilai
 * dan `dikoreksi_pada` waktunya. Keduanya dikosongkan lagi ketika guru
 * pengampu mengubah baris tersebut (perubahan terakhir milik guru).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaians', function (Blueprint $table): void {
            $table->foreignId('dikoreksi_oleh')
                ->nullable()
                ->after('materi_tp')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('dikoreksi_pada')->nullable()->after('dikoreksi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('penilaians', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('dikoreksi_oleh');
            $table->dropColumn('dikoreksi_pada');
        });
    }
};
