<?php

namespace App\Console\Commands;

use App\Services\MigrasiReconcileService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Menyelaraskan tabel migrations dengan skema database yang sebenarnya.
 *
 * Dipakai setelah restore backup lama (tabel migrations tidak ikut di-backup):
 * migrasi yang sebenarnya sudah diterapkan ditandai sudah berjalan, sehingga
 * `php artisan migrate` berikutnya tidak gagal dengan "Duplicate column name".
 */
class ReconcileMigrations extends Command
{
    /**
     * Nama dan argumen perintah.
     *
     * @var string
     */
    protected $signature = 'migrate:reconcile
                            {--fix : Tandai migrasi yang terbukti sudah diterapkan sebagai sudah berjalan}';

    /**
     * Deskripsi perintah.
     *
     * @var string
     */
    protected $description = 'Menyelaraskan tabel migrations dengan skema database setelah restore backup';

    /**
     * Menjalankan perintah.
     */
    public function handle(MigrasiReconcileService $service): int
    {
        $tertunda = $service->tertunda();
        $tanpaBerkas = $service->tanpaBerkas();

        if ($tertunda->isEmpty()) {
            $this->info('Tabel migrations sudah sinkron dengan folder database/migrations.');
            $this->peringatkanTanpaBerkas($tanpaBerkas);

            return self::SUCCESS;
        }

        $this->info('Memeriksa '.$tertunda->count().' migrasi yang belum tercatat...');
        $this->newLine();

        $sudahDiterapkan = [];
        $manual = [];

        foreach ($tertunda as $namaMigrasi) {
            $hasil = $service->analisa($namaMigrasi);

            match ($hasil['status']) {
                MigrasiReconcileService::STATUS_SUDAH => $sudahDiterapkan[] = $namaMigrasi,
                MigrasiReconcileService::STATUS_BELUM => null,
                default => $manual[] = $namaMigrasi,
            };

            $this->line($namaMigrasi.'  =>  '.$hasil['status']);

            foreach ($hasil['rincian'] as $rincian) {
                $this->line('    '.$rincian);
            }
        }

        $this->newLine();

        if ($sudahDiterapkan !== []) {
            $this->table(
                ['No', 'Nama migrasi'],
                collect($sudahDiterapkan)
                    ->values()
                    ->map(fn (string $nama, int $urutan): array => [$urutan + 1, $nama])
                    ->all()
            );
        }

        if ($manual !== []) {
            $this->warn('Migrasi berikut belum bisa dinilai otomatis dan akan diproses oleh `php artisan migrate`:');
            foreach ($manual as $nama) {
                $this->line('  - '.$nama);
            }
            $this->newLine();
        }

        $belumDiterapkan = $tertunda->count() - count($sudahDiterapkan) - count($manual);

        $this->info('Ringkasan: '.count($sudahDiterapkan).' sudah diterapkan, '.count($manual).' perlu dicek manual, '.$belumDiterapkan.' belum diterapkan.');

        if (! $this->option('fix')) {
            $this->newLine();
            $this->comment('Jalankan ulang dengan --fix untuk menandai '.count($sudahDiterapkan).' migrasi di atas sebagai sudah berjalan.');
            $this->peringatkanTanpaBerkas($tanpaBerkas);

            return self::SUCCESS;
        }

        foreach ($sudahDiterapkan as $nama) {
            $service->tandaiSudahDijalankan($nama);
        }

        $this->newLine();
        $this->info(count($sudahDiterapkan).' migrasi ditandai sudah berjalan. Lanjutkan dengan: php artisan migrate --force');
        $this->peringatkanTanpaBerkas($tanpaBerkas);

        return self::SUCCESS;
    }

    /**
     * Menampilkan peringatan untuk catatan migrasi yang berkasnya sudah tidak ada.
     *
     * @param  Collection<int, string>  $tanpaBerkas
     */
    private function peringatkanTanpaBerkas(Collection $tanpaBerkas): void
    {
        if ($tanpaBerkas->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->warn('Catatan migrasi tanpa berkas (sisa restore backup lama, tidak berdampak pada skema):');
        foreach ($tanpaBerkas as $nama) {
            $this->line('  - '.$nama);
        }
    }
}
