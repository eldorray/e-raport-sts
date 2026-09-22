<?php

use App\Models\Mengajar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Memastikan kolom uuid pada tabel mengajars benar-benar ada.
 *
 * Migrasi 2025_12_26_042637_add_uuid_to_mengajars_table tercatat sudah berjalan
 * pada sebagian database (termasuk hasil restore backup yang tidak menyertakan
 * tabel migrations), tetapi kolomnya tidak ada sehingga pembuatan jadwal
 * mengajar gagal dengan error "Unknown column 'uuid'". Migrasi ini idempoten:
 * bila kolom sudah ada, tidak ada perubahan yang dilakukan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('mengajars', 'uuid')) {
            return;
        }

        Schema::table('mengajars', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        Mengajar::query()->whereNull('uuid')->each(function (Mengajar $mengajar): void {
            $mengajar->uuid = Str::uuid()->toString();
            $mengajar->save();
        });

        Schema::table('mengajars', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Tidak ada rollback: kolom uuid dibutuhkan aplikasi (dipakai route model binding),
     * sehingga kolom sengaja tidak dihapus meski migrasi ini dibatalkan.
     */
    public function down(): void {}
};
