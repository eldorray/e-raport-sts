<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah aturan hapus foreign key data nilai dari CASCADE menjadi RESTRICT.
 *
 * Sebelumnya nilai ikut terhapus otomatis ketika siswa, guru, kelas, mata
 * pelajaran, jadwal mengajar, atau tahun ajaran dihapus — termasuk nilai milik
 * tahun ajaran lain yang sedang tidak aktif. Dengan RESTRICT, database menolak
 * penghapusan data induk yang masih punya nilai sehingga penghapusan data nilai
 * harus dilakukan secara sadar lewat aplikasi.
 */
return new class extends Migration
{
    /**
     * Kolom foreign key data nilai yang harus memakai aturan RESTRICT.
     *
     * @var array<string, array<string, string>> Tabel => [kolom => tabel induk]
     */
    private const RESTRICTED_KEYS = [
        'penilaians' => [
            'tahun_ajaran_id' => 'tahun_ajarans',
            'kelas_id' => 'kelas',
            'siswa_id' => 'siswas',
            'mata_pelajaran_id' => 'mata_pelajarans',
            'guru_id' => 'gurus',
            'mengajar_id' => 'mengajars',
        ],
        'rapor_metadatas' => [
            'tahun_ajaran_id' => 'tahun_ajarans',
            'siswa_id' => 'siswas',
            'kelas_id' => 'kelas',
        ],
        'tahfidz_penilaians' => [
            'siswa_id' => 'siswas',
            'tahun_ajaran_id' => 'tahun_ajarans',
        ],
    ];

    public function up(): void
    {
        $this->applyDeleteRules(useRestrict: true);
    }

    public function down(): void
    {
        $this->applyDeleteRules(useRestrict: false);
    }

    /**
     * Menerapkan aturan hapus pada seluruh foreign key data nilai.
     *
     * @param  bool  $useRestrict  True untuk RESTRICT, false untuk mengembalikan CASCADE
     */
    private function applyDeleteRules(bool $useRestrict): void
    {
        // SQLite (dipakai saat menjalankan tes) tidak mendukung drop foreign key
        // lewat query builder, sehingga perubahan hanya berlaku untuk MySQL.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::RESTRICTED_KEYS as $tableName => $columns) {
            foreach ($columns as $column => $referencedTable) {
                Schema::table($tableName, function (Blueprint $table) use ($column, $referencedTable, $useRestrict): void {
                    $table->dropForeign([$column]);

                    $foreignKey = $table->foreign($column)->references('id')->on($referencedTable);

                    if ($useRestrict) {
                        $foreignKey->restrictOnDelete();
                    } else {
                        $foreignKey->cascadeOnDelete();
                    }
                });
            }
        }
    }
};
