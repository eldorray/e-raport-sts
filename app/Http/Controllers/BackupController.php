<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller untuk backup dan restore database.
 *
 * Hanya dapat diakses oleh admin.
 */
class BackupController extends Controller
{
    /**
     * Tables to exclude from backup (usually Laravel internal tables).
     */
    private const EXCLUDED_TABLES = [
        'migrations',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /**
     * Menampilkan halaman backup/restore.
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
        $filename = 'backup_' . config('app.name') . '_' . date('Y-m-d_H-i-s') . '.sql';

        return response()->streamDownload(function () {
            $this->generateBackup();
        }, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Restore database dari file SQL yang diupload.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'max:51200'], // Max 50MB
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['sql', 'txt'])) {
            return back()->with('error', __('File harus berformat .sql'));
        }

        try {
            $sql = file_get_contents($file->getRealPath());

            // Validate it looks like SQL
            if (! str_contains($sql, 'INSERT INTO') && ! str_contains($sql, 'CREATE TABLE')) {
                return back()->with('error', __('File tidak valid atau kosong.'));
            }

            // Disable foreign key checks during restore
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop all existing tables first (except excluded ones)
            $existingTables = $this->getTables();
            foreach ($existingTables as $table) {
                if (! in_array($table, self::EXCLUDED_TABLES)) {
                    DB::statement("DROP TABLE IF EXISTS `{$table}`");
                }
            }

            // Remove comments and normalize line endings
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = str_replace("\r\n", "\n", $sql);

            // Split by semicolon followed by newline to avoid splitting on semicolons inside statements
            $statements = preg_split('/;\s*\n/', $sql);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (! empty($statement) && ! str_starts_with($statement, '--')) {
                    // Skip SET statements that might conflict
                    if (preg_match('/^SET\s+/i', $statement)) {
                        continue;
                    }
                    DB::unprepared($statement);
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return back()->with('status', __('Database berhasil di-restore dari backup.'));
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return back()->with('error', __('Gagal restore: ') . $e->getMessage());
        }
    }

    /**
     * Generate SQL backup output.
     */
    private function generateBackup(): void
    {
        $tables = $this->getTables();

        echo "-- E-Raport Database Backup\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Laravel Version: " . app()->version() . "\n\n";
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
            echo $createStatement . ";\n\n";
        }

        // Get table data
        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            echo "-- No data in {$table}\n\n";
            return;
        }

        // Get column names
        $columns = array_keys((array) $rows->first());
        $columnList = '`' . implode('`, `', $columns) . '`';

        echo "-- Data for {$table}\n";

        foreach ($rows->chunk(100) as $chunk) {
            $values = [];

            foreach ($chunk as $row) {
                $rowValues = [];
                foreach ((array) $row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $rowValues[] = $value;
                    } else {
                        $rowValues[] = "'" . addslashes((string) $value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }

            echo "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
            echo implode(",\n", $values) . ";\n";
        }

        echo "\n";
    }

    /**
     * Get all table names in the database.
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
