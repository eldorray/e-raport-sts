<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Menyelaraskan tabel migrations dengan skema database yang sebenarnya.
 *
 * Fitur Backup tidak menyertakan tabel migrations, sehingga setelah restore
 * backup lama tabel migrations bisa tertinggal dari skema: kolom sudah ada tetapi
 * migrasinya tercatat belum berjalan. Akibatnya `php artisan migrate` berhenti
 * dengan error "Duplicate column name". Service ini mendeteksi migrasi seperti itu
 * sehingga bisa ditandai sudah berjalan tanpa menjalankan ulang perubahannya.
 */
class MigrasiReconcileService
{
    public const STATUS_SUDAH = 'sudah diterapkan';

    public const STATUS_BELUM = 'belum diterapkan';

    public const STATUS_SEBAGIAN = 'sebagian';

    public const STATUS_TIDAK_DIKENALI = 'tidak dikenali';

    public const STATUS_TANPA_BERKAS = 'berkas tidak ada';

    /**
     * Pemanggilan $table->...() yang bukan penambahan kolom.
     *
     * @var array<int, string>
     */
    private const BUKAN_KOLOM = [
        'dropforeign', 'foreign', 'index', 'unique', 'primary', 'dropindex',
        'dropunique', 'dropprimary', 'renamecolumn', 'dropcolumn', 'dropmorphs',
        'dropsoftdeletes', 'dropsoftdeletestz', 'dropremembertoken',
        'dropconstrained', 'constrained', 'renameindex', 'dropfulltext', 'fulltext',
        'dropforeignid', 'dropmorphs', 'dropulidmorphs', 'dropuuidmorphs',
    ];

    /**
     * Daftar migrasi yang tercatat pada tabel migrations.
     *
     * @return Collection<int, string>
     */
    public function tercatat(): Collection
    {
        return DB::table('migrations')->orderBy('id')->pluck('migration');
    }

    /**
     * Daftar berkas migrasi pada folder database/migrations.
     *
     * @return Collection<int, string>
     */
    public function berkas(): Collection
    {
        return collect(File::files(database_path('migrations')))
            ->map(fn ($berkas): string => $berkas->getFilenameWithoutExtension())
            ->sort()
            ->values();
    }

    /**
     * Migrasi yang belum tercatat pada tabel migrations.
     *
     * @return Collection<int, string>
     */
    public function tertunda(): Collection
    {
        return $this->berkas()->diff($this->tercatat())->values();
    }

    /**
     * Catatan migrasi yang berkasnya sudah tidak ada (sisa restore backup lama).
     *
     * @return Collection<int, string>
     */
    public function tanpaBerkas(): Collection
    {
        return $this->tercatat()->diff($this->berkas())->values();
    }

    /**
     * Menilai apakah isi sebuah migrasi sudah tercermin pada skema database.
     *
     * @param  string  $namaMigrasi  Nama berkas migrasi tanpa ekstensi
     * @return array{status: string, rincian: array<int, string>, terpenuhi: int, total: int}
     */
    public function analisa(string $namaMigrasi): array
    {
        $path = database_path('migrations/'.$namaMigrasi.'.php');

        if (! File::exists($path)) {
            return [
                'status' => self::STATUS_TANPA_BERKAS,
                'rincian' => [],
                'terpenuhi' => 0,
                'total' => 0,
            ];
        }

        $objek = $this->objekDariIsi($this->isiUp(File::get($path)));

        $rincian = [];
        $terpenuhi = 0;

        foreach ($objek as $item) {
            $ada = $this->objekAda($item);
            $terpenuhi += $ada ? 1 : 0;
            $rincian[] = ($ada ? '[ada] ' : '[hilang] ').$this->deskripsi($item);
        }

        $status = match (true) {
            $objek === [] => self::STATUS_TIDAK_DIKENALI,
            $terpenuhi === count($objek) => self::STATUS_SUDAH,
            $terpenuhi === 0 => self::STATUS_BELUM,
            default => self::STATUS_SEBAGIAN,
        };

        return [
            'status' => $status,
            'rincian' => $rincian,
            'terpenuhi' => $terpenuhi,
            'total' => count($objek),
        ];
    }

    /**
     * Menandai migrasi sebagai sudah berjalan tanpa menjalankan perubahannya.
     *
     * @param  string  $namaMigrasi  Nama berkas migrasi tanpa ekstensi
     */
    public function tandaiSudahDijalankan(string $namaMigrasi): void
    {
        DB::table('migrations')->insert([
            'migration' => $namaMigrasi,
            'batch' => (int) DB::table('migrations')->max('batch') + 1,
        ]);
    }

    /**
     * Mengambil isi method up() saja, supaya operasi pada down() tidak ikut dibaca.
     */
    private function isiUp(string $isi): string
    {
        $mulai = strpos($isi, 'function up(');

        if ($mulai === false) {
            return $isi;
        }

        $selesai = strpos($isi, 'function down(', $mulai);

        return $selesai === false ? substr($isi, $mulai) : substr($isi, $mulai, $selesai - $mulai);
    }

    /**
     * Menyusun daftar objek skema yang seharusnya ada setelah migrasi dijalankan.
     *
     * @return array<int, array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}>
     */
    private function objekDariIsi(string $isi): array
    {
        $objek = [];

        preg_match_all("/Schema::create\(\s*'([^']+)'/", $isi, $tabelBaru);
        foreach ($tabelBaru[1] as $tabel) {
            $objek[] = $this->objekTabel($tabel, true);
        }

        preg_match_all("/Schema::drop(?:IfExists)?\(\s*'([^']+)'/", $isi, $tabelDihapus);
        foreach ($tabelDihapus[1] as $tabel) {
            $objek[] = $this->objekTabel($tabel, false);
        }

        foreach ($this->segmenTabel($isi) as $segmen) {
            $objek = array_merge($objek, $this->objekDariSegmen($segmen['tabel'], $segmen['isi']));
        }

        return $objek;
    }

    /**
     * Memecah isi migrasi menjadi bagian per pemanggilan Schema::table().
     *
     * @return array<int, array{tabel: string, isi: string}>
     */
    private function segmenTabel(string $isi): array
    {
        $segmen = [];

        preg_match_all(
            "/Schema::table\(\s*'([^']+)'\s*,/",
            $isi,
            $cocok,
            PREG_OFFSET_CAPTURE
        );

        foreach ($cocok[1] as $urutan => $tabelCocok) {
            $mulai = $tabelCocok[1];
            $berikutnya = $cocok[0][$urutan + 1][1] ?? strlen($isi);

            $segmen[] = [
                'tabel' => $tabelCocok[0],
                'isi' => substr($isi, $mulai, $berikutnya - $mulai),
            ];
        }

        return $segmen;
    }

    /**
     * Mencari kolom dan indeks yang disentuh pada satu blok Schema::table().
     *
     * @return array<int, array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}>
     */
    private function objekDariSegmen(string $tabel, string $segmen): array
    {
        $objek = [];

        preg_match_all("/\\\$table->renameColumn\(\s*'([^']+)'\s*,\s*'([^']+)'/", $segmen, $rename);
        foreach ($rename[1] as $urutan => $kolomLama) {
            $objek[] = $this->objekKolom($tabel, $kolomLama, false);
            $objek[] = $this->objekKolom($tabel, $rename[2][$urutan], true);
        }

        preg_match_all("/\\\$table->dropColumn\(\s*(\[[^\]]*\]|'[^']*')/", $segmen, $dropKolom);
        foreach ($dropKolom[1] as $argumen) {
            preg_match_all("/'([^']+)'/", $argumen, $namaKolom);
            foreach ($namaKolom[1] as $kolom) {
                $objek[] = $this->objekKolom($tabel, $kolom, false);
            }
        }

        foreach ($this->indeksDariSegmen($tabel, $segmen) as $indeks) {
            $objek[] = $indeks;
        }

        preg_match_all("/\\\$table->([a-zA-Z]+)\(\s*'([^']+)'/", $segmen, $kolom);
        foreach ($kolom[1] as $urutan => $tipe) {
            if (in_array(strtolower($tipe), self::BUKAN_KOLOM, true)) {
                continue;
            }

            $objek[] = $this->objekKolom($tabel, $kolom[2][$urutan], true);
        }

        return $objek;
    }

    /**
     * Mencari indeks/unique yang ditambahkan atau dihapus pada satu blok.
     *
     * @return array<int, array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}>
     */
    private function indeksDariSegmen(string $tabel, string $segmen): array
    {
        $objek = [];

        preg_match_all("/\\\$table->drop(?:Unique|Index)\(\s*(\[[^\]]*\]|'[^']*')/", $segmen, $dropIndeks);
        foreach ($dropIndeks[1] as $argumen) {
            preg_match_all("/'([^']+)'/", $argumen, $nama);

            if ($nama[1] === []) {
                continue;
            }

            $objek[] = $this->objekIndeks($tabel, $nama[1], false);
        }

        preg_match_all("/\\\$table->(unique|index)\(([^;]*?)\);/s", $segmen, $tambahIndeks, PREG_SET_ORDER);
        foreach ($tambahIndeks as $cocok) {
            $argumen = $cocok[2];
            $kolom = [];

            if (preg_match('/\[(.*)\]/s', $argumen, $daftar)) {
                preg_match_all("/'([^']+)'/", $daftar[1], $isiDaftar);
                $kolom = $isiDaftar[1];
            } elseif (preg_match("/'([^']+)'/", $argumen, $satu)) {
                $kolom = [$satu[1]];
            }

            if ($kolom === []) {
                continue;
            }

            $nama = $this->namaIndeks($tabel, $kolom, $cocok[1], $argumen);

            $objek[] = $this->objekIndeks($tabel, [$nama], true);
        }

        return $objek;
    }

    /**
     * Menentukan nama indeks sesuai konvensi Laravel bila tidak ditulis eksplisit.
     *
     * @param  array<int, string>  $kolom
     */
    private function namaIndeks(string $tabel, array $kolom, string $tipe, string $argumen): string
    {
        if (preg_match('/\]\s*,\s*\'([^\']+)\'/', $argumen, $cocok)) {
            return $cocok[1];
        }

        if (preg_match("/\(\s*'[^']+'\s*,\s*'([^']+)'/", $argumen, $cocok)) {
            return $cocok[1];
        }

        return $tabel.'_'.implode('_', $kolom).'_'.$tipe;
    }

    /**
     * @return array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}
     */
    private function objekTabel(string $tabel, bool $harusAda): array
    {
        return ['jenis' => 'tabel', 'tabel' => $tabel, 'kolom' => null, 'indeks' => null, 'harus_ada' => $harusAda];
    }

    /**
     * @return array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}
     */
    private function objekKolom(string $tabel, string $kolom, bool $harusAda): array
    {
        return ['jenis' => 'kolom', 'tabel' => $tabel, 'kolom' => $kolom, 'indeks' => null, 'harus_ada' => $harusAda];
    }

    /**
     * @param  array<int, string>  $indeks
     * @return array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}
     */
    private function objekIndeks(string $tabel, array $indeks, bool $harusAda): array
    {
        return ['jenis' => 'indeks', 'tabel' => $tabel, 'kolom' => null, 'indeks' => $indeks[0], 'harus_ada' => $harusAda];
    }

    /**
     * Memeriksa keberadaan satu objek pada skema.
     *
     * @param  array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}  $item
     */
    private function objekAda(array $item): bool
    {
        $ada = match ($item['jenis']) {
            'tabel' => Schema::hasTable($item['tabel']),
            'kolom' => Schema::hasColumn($item['tabel'], (string) $item['kolom']),
            'indeks' => Schema::hasIndex($item['tabel'], (string) $item['indeks']),
            default => false,
        };

        return $ada === $item['harus_ada'];
    }

    /**
     * @param  array{jenis: string, tabel: string, kolom: ?string, indeks: ?string, harus_ada: bool}  $item
     */
    private function deskripsi(array $item): string
    {
        $aksi = $item['harus_ada'] ? 'ada' : 'dihapus';

        return match ($item['jenis']) {
            'tabel' => "tabel {$item['tabel']} ({$aksi})",
            'kolom' => "kolom {$item['tabel']}.{$item['kolom']} ({$aksi})",
            default => "indeks {$item['tabel']}.{$item['indeks']} ({$aksi})",
        };
    }
}
