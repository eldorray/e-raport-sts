<?php

declare(strict_types=1);

use App\Http\Controllers\BackupController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Controller backup dengan akses ke method protected untuk pengujian.
 */
function backupControllerUji(): BackupController
{
    return new class extends BackupController
    {
        public function nilai(int|float|string|bool|null $value): string
        {
            return $this->sqlValue($value);
        }

        public function dataTabel(string $table): string
        {
            ob_start();
            $this->writeTableData($table);

            return (string) ob_get_clean();
        }
    };
}

test('nilai null ditulis sebagai NULL', function () {
    expect(backupControllerUji()->nilai(null))->toBe('NULL');
});

test('nilai varchar berangka tetap dikutip sehingga nol di depan tidak hilang', function () {
    expect(backupControllerUji()->nilai('0081234567'))->toBe("'0081234567'")
        ->and(backupControllerUji()->nilai(42))->toBe("'42'");
});

test('string dengan baris baru dan tanda kutip dikutip dengan aman', function () {
    $asli = "Baris satu\nO'Brien \"kutip\" \\ akhir";
    $quoted = backupControllerUji()->nilai($asli);

    expect($quoted)->not->toBe($asli);
    expect(DB::selectOne("SELECT {$quoted} AS v")->v)->toBe($asli);
});

test('data tabel dibaca bertahap dan ditulis per 100 baris yang bisa dipulihkan utuh', function () {
    Schema::create('backup_uji', function (Blueprint $table) {
        $table->id();
        $table->string('nisn')->nullable();
        $table->text('catatan')->nullable();
    });

    $rows = [];
    for ($i = 1; $i <= 150; $i++) {
        $rows[] = [
            'nisn' => sprintf('00%08d', $i),
            'catatan' => $i === 7 ? "Catatan;\n-- bukan komentar\nO'Brien" : null,
        ];
    }
    DB::table('backup_uji')->insert($rows);

    $sql = backupControllerUji()->dataTabel('backup_uji');

    expect(substr_count($sql, 'INSERT INTO `backup_uji`'))->toBe(2);

    DB::table('backup_uji')->delete();
    DB::unprepared($sql);

    expect(DB::table('backup_uji')->count())->toBe(150)
        ->and(DB::table('backup_uji')->where('id', 1)->value('nisn'))->toBe('0000000001')
        ->and(DB::table('backup_uji')->where('id', 7)->value('catatan'))->toBe("Catatan;\n-- bukan komentar\nO'Brien")
        ->and(DB::table('backup_uji')->where('id', 8)->value('catatan'))->toBeNull();
});

test('tabel kosong ditandai tanpa INSERT', function () {
    Schema::create('backup_kosong', function (Blueprint $table) {
        $table->id();
    });

    $sql = backupControllerUji()->dataTabel('backup_kosong');

    expect($sql)->toContain('-- No data in backup_kosong')
        ->and($sql)->not->toContain('INSERT INTO');
});

test('fitur restore dari aplikasi sudah dihapus', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    expect(Route::has('backup.restore'))->toBeFalse();

    $status = $this->actingAs($admin)->post('/backup/restore')->status();
    expect($status)->toBeIn([404, 405]);

    $this->actingAs($admin)
        ->get('/backup')
        ->assertOk()
        ->assertDontSee('backup_file')
        ->assertSee('phpMyAdmin');
});

test('guru tidak dapat mengakses backup', function () {
    $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);

    $this->actingAs($guru)->get('/backup')->assertForbidden();
    $this->actingAs($guru)->get('/backup/download')->assertForbidden();
});
