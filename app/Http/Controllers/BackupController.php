<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller untuk backup database.
 *
 * Restore tidak dilakukan dari aplikasi, melainkan dari panel hosting
 * (phpMyAdmin / mysql import) memakai file hasil download.
 *
 * Hanya dapat diakses oleh admin.
 */
class BackupController extends Controller
{
    /**
     * Tables to exclude from backup (usually Laravel internal tables).
     *
     * Tabel `migrations` sengaja ikut di-backup agar database hasil restore
     * mengetahui versi skemanya.
     */
    private const EXCLUDED_TABLES = [
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /**
     * Menampilkan halaman backup.
     */
    public function index(): View
    {
        return view('backup.index');
    }

    /**
     * Download backup database sebagai SQL file.
     */
    public function download(): StreamedResponse
    {
        $filename = 'backup_'.config('app.name').'_'.date('Y-m-d_H-i-s').'.sql';

        return response()->streamDownload(function () {
            $this->generateBackup();
        }, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Generate SQL backup output.
     */
    private function generateBackup(): void
    {
        $tables = $this->getTables();

        echo "-- E-Raport Database Backup\n";
        echo '-- Generated: '.date('Y-m-d H:i:s')."\n";
        echo '-- Laravel Version: '.app()->version()."\n\n";
        echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            if (in_array($table, self::EXCLUDED_TABLES)) {
                continue;
            }

            $this->backupTable($table);
        }

        echo "SET FOREIGN_KEY_CHECKS=1;\n";
    }

    /**
     * Backup a single table.
     */
    private function backupTable(string $table): void
    {
        echo "-- Table: {$table}\n";

        // Get create table statement
        $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
        if (! empty($createTable)) {
            $createStatement = $createTable[0]->{'Create Table'} ?? '';
            echo "DROP TABLE IF EXISTS `{$table}`;\n";
            echo $createStatement.";\n\n";
        }

        $this->writeTableData($table);
    }

    /**
     * Tulis data tabel sebagai INSERT per 100 baris.
     *
     * Baris dibaca dengan cursor agar seluruh tabel tidak dimuat ke memori.
     */
    protected function writeTableData(string $table): void
    {
        $columnList = null;
        $values = [];

        foreach (DB::table($table)->cursor() as $row) {
            $row = (array) $row;

            if ($columnList === null) {
                $columnList = '`'.implode('`, `', array_keys($row)).'`';
                echo "-- Data for {$table}\n";
            }

            $values[] = '('.implode(', ', array_map($this->sqlValue(...), array_values($row))).')';

            if (count($values) === 100) {
                $this->writeInsert($table, $columnList, $values);
                $values = [];
            }
        }

        if ($columnList === null) {
            echo "-- No data in {$table}\n\n";

            return;
        }

        if ($values !== []) {
            $this->writeInsert($table, $columnList, $values);
        }

        echo "\n";
    }

    /**
     * Format satu nilai kolom menjadi literal SQL.
     *
     * Semua nilai non-null dikutip lewat PDO agar varchar berangka (NISN/NIP
     * dengan nol di depan) tidak berubah menjadi angka, dan karakter khusus
     * (baris baru, NUL, kutip) di-escape sesuai driver database.
     */
    protected function sqlValue(int|float|string|bool|null $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return DB::getPdo()->quote((string) $value);
    }

    /**
     * @param  list<string>  $values
     */
    private function writeInsert(string $table, string $columnList, array $values): void
    {
        echo "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
        echo implode(",\n", $values).";\n";
    }

    /**
     * Get all table names in the database.
     *
     * @return list<string>
     */
    private function getTables(): array
    {
        $tables = [];
        $result = DB::select('SHOW TABLES');

        foreach ($result as $row) {
            $tables[] = array_values((array) $row)[0];
        }

        return $tables;
    }
}
