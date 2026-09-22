<?php

namespace App\Services;

use App\Models\Kelas;

/**
 * Service pemetaan rombel API data induk ke kelas lokal.
 *
 * API data induk mengirim label rombel berbentuk "Kelas 1 - KELAS 1A" (MI)
 * atau "Kelas VII - Kelas VII A" (SMP). Service ini mengubah label tersebut
 * menjadi tingkat + nama rombel lokal, lalu memakai kelas yang sudah ada
 * (dicocokkan berdasarkan nama rombel) atau membuat kelas baru bila belum
 * tersedia pada tahun ajaran yang sedang dipilih.
 */
class KelasResolverService
{
    /** @var array<int, string> Konversi tingkat angka ke angka romawi */
    private const TINGKAT_ROMAWI = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'V',
        6 => 'VI',
        7 => 'VII',
        8 => 'VIII',
        9 => 'IX',
    ];

    /** @var int Panjang maksimum nama kelas (mengikuti kolom kelas.nama) */
    private const MAX_NAMA_LENGTH = 50;

    /** @var int Panjang maksimum tingkat (mengikuti kolom kelas.tingkat) */
    private const MAX_TINGKAT_LENGTH = 20;

    /** @var array<string, int> Peta nama rombel ternormalisasi ke ID kelas */
    private array $kelasByNama = [];

    /** @var bool Menandakan kelas dari database sudah dimuat */
    private bool $kelasLoaded = false;

    /** @var int Jumlah kelas baru yang dibuat otomatis */
    public int $created = 0;

    /**
     * @param  int  $tahunAjaranId  ID tahun ajaran tempat kelas dipetakan
     * @param  string|null  $jenis  Jenis kelas untuk kelas baru (mis. MI/SMP)
     */
    public function __construct(
        private readonly int $tahunAjaranId,
        private readonly ?string $jenis = null,
    ) {}

    /**
     * Menentukan ID kelas dari label rombel API.
     *
     * Kelas baru dibuat otomatis bila belum ada di tahun ajaran ini.
     *
     * @param  string|null  $tingkatRombel  Label rombel, mis. "Kelas 1 - KELAS 1A"
     * @return int|null ID kelas, atau null bila label tidak dapat dipetakan
     */
    public function resolveId(?string $tingkatRombel): ?int
    {
        $parsed = $this->parseRombel($tingkatRombel);

        if ($parsed === null) {
            return null;
        }

        $this->loadExistingKelas();

        $key = $this->normalizeKey($parsed['nama']);

        if (isset($this->kelasByNama[$key])) {
            return $this->kelasByNama[$key];
        }

        $kelas = Kelas::create([
            'tahun_ajaran_id' => $this->tahunAjaranId,
            'nama' => $parsed['nama'],
            'tingkat' => $parsed['tingkat'],
            'jenis' => $this->jenis,
        ]);

        $this->kelasByNama[$key] = $kelas->id;
        $this->created++;

        return $kelas->id;
    }

    /**
     * Mengurai label rombel menjadi tingkat dan nama rombel.
     *
     * @param  string|null  $tingkatRombel  Label rombel dari API
     * @return array{tingkat: string, nama: string}|null Hasil parsing atau null
     */
    public function parseRombel(?string $tingkatRombel): ?array
    {
        $label = trim((string) $tingkatRombel);

        if ($label === '') {
            return null;
        }

        $parts = preg_split('/\s*[-–—]\s*/u', $label, 2) ?: [];
        $hasTingkat = count($parts) > 1;

        $tingkatRaw = $hasTingkat ? $parts[0] : '';
        $namaRaw = $hasTingkat ? $parts[1] : $parts[0];
        $nama = $this->normalizeNama($namaRaw);

        if ($nama === '') {
            $nama = $this->normalizeNama($tingkatRaw);
        }

        if ($nama === '') {
            return null;
        }

        return [
            'tingkat' => $this->normalizeTingkat($tingkatRaw, $nama),
            'nama' => $nama,
        ];
    }

    /**
     * Menormalisasi nama rombel menjadi bentuk tampil, mis. "1A" atau "VII A".
     *
     * @param  string  $value  Nama rombel mentah
     * @return string Nama rombel tanpa prefix "KELAS"
     */
    private function normalizeNama(string $value): string
    {
        $value = strtoupper(mb_substr(trim($value), 0, self::MAX_NAMA_LENGTH));
        $value = $this->stripKelasPrefix($value);
        $value = (string) preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    /**
     * Menormalisasi tingkat menjadi angka romawi, mis. "1" atau "Kelas 1" menjadi "I".
     *
     * @param  string  $tingkatRaw  Tingkat mentah dari API
     * @param  string  $namaRombel  Nama rombel hasil normalisasi sebagai cadangan
     * @return string Tingkat ternormalisasi
     */
    private function normalizeTingkat(string $tingkatRaw, string $namaRombel): string
    {
        $source = trim($tingkatRaw) !== '' ? $tingkatRaw : $namaRombel;
        $value = $this->stripKelasPrefix(strtoupper(trim($source)));

        if (preg_match('/^([0-9]+)/', $value, $matches) === 1) {
            $angka = (int) $matches[1];

            $value = self::TINGKAT_ROMAWI[$angka] ?? $matches[1];
        } elseif (preg_match('/^([IVXL]+)/', $value, $matches) === 1) {
            $value = $matches[1];
        }

        return mb_substr($value, 0, self::MAX_TINGKAT_LENGTH);
    }

    /**
     * Menghapus prefix "KELAS"/"TINGKAT"/"KLS" dari nilai.
     *
     * @param  string  $value  Nilai yang akan dibersihkan
     * @return string Nilai tanpa prefix
     */
    private function stripKelasPrefix(string $value): string
    {
        return trim((string) preg_replace('/^(KELAS|TINGKAT|KLS)\s*/i', '', $value));
    }

    /**
     * Membuat kunci pencocokan kelas (huruf besar tanpa spasi).
     *
     * @param  string  $value  Nama rombel
     * @return string Kunci pencocokan
     */
    private function normalizeKey(string $value): string
    {
        return str_replace(' ', '', strtoupper(trim($this->stripKelasPrefix($value))));
    }

    /**
     * Memuat kelas yang sudah ada pada tahun ajaran ini ke cache pencarian.
     */
    private function loadExistingKelas(): void
    {
        if ($this->kelasLoaded) {
            return;
        }

        $this->kelasLoaded = true;

        Kelas::query()
            ->where('tahun_ajaran_id', $this->tahunAjaranId)
            ->get(['id', 'nama'])
            ->each(function (Kelas $kelas): void {
                $key = $this->normalizeKey((string) $kelas->nama);

                if ($key !== '' && ! isset($this->kelasByNama[$key])) {
                    $this->kelasByNama[$key] = $kelas->id;
                }
            });
    }
}
