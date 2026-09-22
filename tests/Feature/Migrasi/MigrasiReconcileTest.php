<?php

use App\Services\MigrasiReconcileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

it('menandai migrasi yang sudah diterapkan pada skema sebagai sudah berjalan', function () {
    $nama = '2025_12_10_120000_add_is_active_to_siswas_table';

    DB::table('migrations')->where('migration', $nama)->delete();

    $this->artisan('migrate:reconcile')
        ->expectsOutputToContain($nama.'  =>  '.MigrasiReconcileService::STATUS_SUDAH)
        ->assertExitCode(0);

    expect(DB::table('migrations')->where('migration', $nama)->exists())->toBeFalse();

    $this->artisan('migrate:reconcile --fix')->assertExitCode(0);

    expect(DB::table('migrations')->where('migration', $nama)->exists())->toBeTrue()
        ->and(Schema::hasColumn('siswas', 'is_active'))->toBeTrue();
});

it('tidak menandai migrasi yang memang belum dijalankan', function () {
    $nama = '2099_01_01_000000_create_tabel_uji_reconcile_table';
    $berkas = database_path('migrations/'.$nama.'.php');

    File::put($berkas, <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabel_uji_reconcile', function (Blueprint $table) {
            $table->id();
        });
    }
};
PHP);

    try {
        $this->artisan('migrate:reconcile')
            ->expectsOutputToContain($nama.'  =>  '.MigrasiReconcileService::STATUS_BELUM)
            ->assertExitCode(0);

        $this->artisan('migrate:reconcile --fix')->assertExitCode(0);

        expect(DB::table('migrations')->where('migration', $nama)->exists())->toBeFalse()
            ->and(Schema::hasTable('tabel_uji_reconcile'))->toBeFalse();
    } finally {
        File::delete($berkas);
    }
});

it('mengenali seluruh migrasi pada repo sebagai sudah diterapkan', function () {
    $service = new MigrasiReconcileService;
    $belumDikenali = [];

    foreach ($service->berkas() as $namaMigrasi) {
        $status = $service->analisa($namaMigrasi)['status'];

        if ($status !== MigrasiReconcileService::STATUS_SUDAH) {
            $belumDikenali[$namaMigrasi] = $status;
        }
    }

    // Hanya migrasi aturan FK yang tidak bisa dinilai otomatis: pada SQLite
    // (dipakai tes) migrasi itu tidak mengubah apa pun, dan operasi foreign key-nya
    // dijalankan lewat variabel sehingga tidak terbaca dari berkas.
    expect($belumDikenali)->toBe([
        '2026_09_22_120000_set_restrict_on_nilai_foreign_keys' => MigrasiReconcileService::STATUS_TIDAK_DIKENALI,
    ]);
});

it('melaporkan catatan migrasi yang berkasnya sudah tidak ada', function () {
    DB::table('migrations')->insert([
        'migration' => '2026_02_26_000000_create_app_licenses_table',
        'batch' => 99,
    ]);

    $this->artisan('migrate:reconcile')
        ->expectsOutputToContain('2026_02_26_000000_create_app_licenses_table')
        ->assertExitCode(0);
});
