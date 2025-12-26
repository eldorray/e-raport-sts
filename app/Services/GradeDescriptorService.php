<?php

namespace App\Services;

/**
 * Service untuk menghitung dan menghasilkan deskripsi nilai rapor.
 *
 * Service ini menggunakan konfigurasi dari config/rapor.php untuk
 * menentukan batas nilai dan template deskripsi.
 */
class GradeDescriptorService
{
    /** @var float Toleransi untuk validasi total bobot */
    private const BOBOT_TOLERANCE = 0.01;

    /** @var float Total bobot yang harus dicapai */
    private const TOTAL_BOBOT = 100.0;

    /**
     * Menghitung nilai rapor dari nilai sumatif dan STS.
     *
     * @param  float|null  $nilaiSumatif  Nilai sumatif
     * @param  float|null  $nilaiSts      Nilai STS
     * @param  float       $bobotSumatif  Bobot sumatif (default dari config)
     * @param  float       $bobotSts      Bobot STS (default dari config)
     * @return float|null  Nilai rapor atau null jika tidak bisa dihitung
     */
    public function calculateRapor(
        ?float $nilaiSumatif,
        ?float $nilaiSts,
        ?float $bobotSumatif = null,
        ?float $bobotSts = null
    ): ?float {
        $bobotSumatif ??= (float) config('rapor.bobot_sumatif', 50);
        $bobotSts ??= (float) config('rapor.bobot_sts', 50);

        if ($nilaiSumatif === null || $nilaiSts === null) {
            return null;
        }

        if (! $this->isValidTotalBobot($bobotSumatif, $bobotSts)) {
            return null;
        }

        return round((($nilaiSumatif * $bobotSumatif) + ($nilaiSts * $bobotSts)) / self::TOTAL_BOBOT, 2);
    }

    /**
     * Mendapatkan deskriptor nilai berdasarkan nilai rapor.
     *
     * @param  float|null   $rapor     Nilai rapor
     * @param  string|null  $materiTp  Materi/TP untuk placeholder
     * @return array|null   Array dengan predikat, keterangan, dan kalimat deskripsi
     */
    public function getDescriptor(?float $rapor, ?string $materiTp = null): ?array
    {
        if ($rapor === null) {
            return null;
        }

        $materiText = $materiTp ?: __('Materi/TP belum diisi');
        $boundaries = config('rapor.grade_boundaries');
        $descriptors = config('rapor.descriptors');

        $gradeKey = $this->determineGradeKey($rapor, $boundaries);
        $descriptor = $descriptors[$gradeKey] ?? $descriptors['perlu_bimbingan'];

        return [
            'predikat' => $descriptor['predikat'],
            'keterangan' => $descriptor['keterangan'],
            'kalimat' => str_replace('[Materi/TP]', $materiText, $descriptor['template']),
        ];
    }

    /**
     * Mendapatkan nilai dan deskriptor sekaligus.
     *
     * @param  float|null   $nilaiSumatif  Nilai sumatif
     * @param  float|null   $nilaiSts      Nilai STS
     * @param  string|null  $materiTp      Materi/TP untuk placeholder
     * @param  float|null   $bobotSumatif  Bobot sumatif
     * @param  float|null   $bobotSts      Bobot STS
     * @return array Array dengan rapor dan descriptor
     */
    public function calculateWithDescriptor(
        ?float $nilaiSumatif,
        ?float $nilaiSts,
        ?string $materiTp = null,
        ?float $bobotSumatif = null,
        ?float $bobotSts = null
    ): array {
        $rapor = $this->calculateRapor($nilaiSumatif, $nilaiSts, $bobotSumatif, $bobotSts);
        $descriptor = $this->getDescriptor($rapor, $materiTp);

        return [
            'rapor' => $rapor,
            'descriptor' => $descriptor,
        ];
    }

    /**
     * Menentukan grade key berdasarkan nilai rapor.
     *
     * @param  float  $rapor      Nilai rapor
     * @param  array  $boundaries Batas-batas nilai
     * @return string Grade key (sangat_baik, baik, cukup, perlu_bimbingan)
     */
    private function determineGradeKey(float $rapor, array $boundaries): string
    {
        if ($rapor >= ($boundaries['sangat_baik'] ?? 86)) {
            return 'sangat_baik';
        }

        if ($rapor >= ($boundaries['baik'] ?? 76)) {
            return 'baik';
        }

        if ($rapor >= ($boundaries['cukup'] ?? 61)) {
            return 'cukup';
        }

        return 'perlu_bimbingan';
    }

    /**
     * Memvalidasi apakah total bobot sudah sesuai.
     *
     * @param  float  $bobotSumatif  Bobot sumatif
     * @param  float  $bobotSts      Bobot STS
     * @return bool True jika valid
     */
    private function isValidTotalBobot(float $bobotSumatif, float $bobotSts): bool
    {
        return abs(($bobotSumatif + $bobotSts) - self::TOTAL_BOBOT) <= self::BOBOT_TOLERANCE;
    }
}
