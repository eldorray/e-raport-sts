<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Satu-satunya jalur penyimpanan nilai Sumatif/STS per jadwal mengajar.
 *
 * Dipakai oleh form guru (PenilaianController) dan koreksi admin
 * (KoreksiNilaiController) supaya aturan validasi, kunci baris, dan jejak
 * koreksinya tidak bisa berbeda di antara keduanya.
 *
 * Baris nilai selalu ditulis dengan guru_id milik jadwal mengajar, sehingga
 * koreksi admin memperbarui baris yang sama dengan milik guru pengampu dan
 * tidak pernah membuat baris ganda.
 */
final class PenyimpananNilaiService
{
    /** @var int Nilai minimum untuk penilaian */
    public const MIN_NILAI = 0;

    /** @var int Nilai maksimum untuk penilaian */
    public const MAX_NILAI = 100;

    /** @var int Panjang maksimum materi/TP */
    public const MAX_MATERI_TP = 255;

    /** @var float Total bobot penilaian yang harus dicapai */
    private const TOTAL_BOBOT = 100.0;

    /** @var float Toleransi untuk validasi total bobot */
    private const BOBOT_TOLERANCE = 0.01;

    /** @var list<string> Kolom yang menentukan apakah sebuah baris nilai berubah */
    private const KOLOM_NILAI = ['nilai_sumatif', 'nilai_sts', 'materi_tp'];

    /**
     * Aturan validasi input form nilai (dipakai guru dan admin).
     *
     * @return array<string, list<string>>
     */
    public function aturanValidasi(): array
    {
        $rentang = ['nullable', 'numeric', 'min:'.self::MIN_NILAI, 'max:'.self::MAX_NILAI];

        return [
            'nilai_sumatif' => ['sometimes', 'array'],
            'nilai_sumatif.*' => $rentang,
            'nilai_sts' => ['sometimes', 'array'],
            'nilai_sts.*' => $rentang,
            'materi_tp' => ['nullable', 'string', 'max:'.self::MAX_MATERI_TP],
        ];
    }

    /**
     * Bobot Sumatif/STS milik seorang user guru, dengan default dari config.
     *
     * @param  User|null  $user  User guru (null bila jadwal belum punya guru)
     * @return array{sumatif: float, sts: float}
     */
    public function bobotGuru(?User $user): array
    {
        return [
            'sumatif' => (float) ($user->bobot_sumatif ?? config('rapor.bobot_sumatif', 50)),
            'sts' => (float) ($user->bobot_sts ?? config('rapor.bobot_sts', 50)),
        ];
    }

    /**
     * Apakah total bobot Sumatif + STS sudah 100%.
     */
    public function bobotValid(float $bobotSumatif, float $bobotSts): bool
    {
        return abs(($bobotSumatif + $bobotSts) - self::TOTAL_BOBOT) <= self::BOBOT_TOLERANCE;
    }

    /**
     * Semester tempat nilai sebuah jadwal mengajar disimpan.
     *
     * Jadwal lama tanpa semester memakai semester milik baris tahun ajarannya
     * (setiap baris tahun ajaran mewakili satu semester).
     */
    public function semesterMengajar(Mengajar $mengajar): ?string
    {
        $semester = trim((string) $mengajar->semester);

        if ($semester !== '') {
            return $semester;
        }

        $semesterTahun = trim((string) $mengajar->tahunAjaran?->semester);

        return $semesterTahun !== '' ? $semesterTahun : null;
    }

    /**
     * Menyimpan nilai seluruh siswa pada satu jadwal mengajar.
     *
     * Hanya baris yang Sumatif/STS/materinya benar-benar berubah yang diberi
     * jejak: bila $pengoreksi diisi (admin), baris itu dicatat sebagai
     * dikoreksi oleh admin tersebut; bila kosong (guru), jejak koreksi pada
     * baris itu dihapus karena perubahan terakhir milik guru.
     *
     * @param  Mengajar  $mengajar  Jadwal mengajar (guru_id wajib terisi)
     * @param  int  $tahunId  Tahun ajaran tempat nilai disimpan
     * @param  string  $semester  Semester tempat nilai disimpan
     * @param  array<string, mixed>  $validated  Input yang sudah lolos aturanValidasi()
     * @param  float  $bobotSumatif  Bobot sumatif guru pengampu
     * @param  float  $bobotSts  Bobot STS guru pengampu
     * @param  User|null  $pengoreksi  Admin yang mengoreksi, null bila disimpan guru sendiri
     * @return int Jumlah baris nilai yang berubah
     *
     * @throws LogicException Bila jadwal mengajar belum punya guru
     */
    public function simpan(
        Mengajar $mengajar,
        int $tahunId,
        string $semester,
        array $validated,
        float $bobotSumatif,
        float $bobotSts,
        ?User $pengoreksi = null,
    ): int {
        if ($mengajar->guru_id === null) {
            throw new LogicException('Jadwal mengajar tanpa guru tidak bisa menyimpan nilai.');
        }

        $guruId = (int) $mengajar->guru_id;

        $siswaIds = Siswa::where('kelas_id', $mengajar->kelas_id)->pluck('id');

        return DB::transaction(function () use ($mengajar, $guruId, $tahunId, $semester, $validated, $bobotSumatif, $bobotSts, $pengoreksi, $siswaIds): int {
            $mengajar->bobot_sumatif = $bobotSumatif;
            $mengajar->bobot_sts = $bobotSts;
            $mengajar->save();

            return $this->simpanNilaiSiswa($mengajar, $guruId, $tahunId, $semester, $validated, $siswaIds, $pengoreksi);
        });
    }

    /**
     * Menulis baris nilai tiap siswa yang ada di payload.
     *
     * @param  array<string, mixed>  $validated  Input yang sudah divalidasi
     * @param  Collection<int, int>  $siswaIds  ID siswa di kelas jadwal mengajar
     * @return int Jumlah baris nilai yang berubah
     */
    private function simpanNilaiSiswa(
        Mengajar $mengajar,
        int $guruId,
        int $tahunId,
        string $semester,
        array $validated,
        Collection $siswaIds,
        ?User $pengoreksi,
    ): int {
        $sumatifPayload = $this->payloadNilai($validated, 'nilai_sumatif');
        $stsPayload = $this->payloadNilai($validated, 'nilai_sts');
        $materiTp = isset($validated['materi_tp']) && is_string($validated['materi_tp'])
            ? trim($validated['materi_tp'])
            : null;

        $allKeys = array_unique(array_merge(array_keys($sumatifPayload), array_keys($stsPayload)));
        $jumlahBerubah = 0;

        foreach ($allKeys as $siswaId) {
            if (! $siswaIds->contains((int) $siswaId)) {
                continue;
            }

            $record = Penilaian::firstOrNew([
                'tahun_ajaran_id' => $tahunId,
                'semester' => $semester,
                'kelas_id' => $mengajar->kelas_id,
                'siswa_id' => $siswaId,
                'mata_pelajaran_id' => $mengajar->mata_pelajaran_id,
                'guru_id' => $guruId,
                'mengajar_id' => $mengajar->id,
            ]);

            $record->materi_tp = $materiTp !== '' ? $materiTp : null;

            if (array_key_exists($siswaId, $sumatifPayload)) {
                $record->nilai_sumatif = $this->angka($sumatifPayload[$siswaId]);
            }

            if (array_key_exists($siswaId, $stsPayload)) {
                $record->nilai_sts = $this->angka($stsPayload[$siswaId]);
            }

            if ($this->berubah($record)) {
                $record->dikoreksi_oleh = $pengoreksi?->id;
                $record->dikoreksi_pada = $pengoreksi !== null ? now() : null;
                $jumlahBerubah++;
            }

            $record->save();
        }

        return $jumlahBerubah;
    }

    /**
     * Apakah Sumatif/STS/materi sebuah baris berbeda dari yang tersimpan.
     *
     * Baris baru hanya dianggap berubah bila Sumatif atau STS-nya berisi:
     * baris kosong yang sekadar ikut membawa materi/TP kelas bukan koreksi
     * nilai, jadi tidak diberi jejak.
     */
    private function berubah(Penilaian $record): bool
    {
        if ($record->exists) {
            return $record->isDirty(self::KOLOM_NILAI);
        }

        return $record->nilai_sumatif !== null || $record->nilai_sts !== null;
    }

    /**
     * Ambil payload nilai per siswa (siswa_id => nilai) dari input tervalidasi.
     *
     * @param  array<string, mixed>  $validated  Input yang sudah divalidasi
     * @return array<int|string, mixed>
     */
    private function payloadNilai(array $validated, string $kunci): array
    {
        $payload = $validated[$kunci] ?? [];

        return is_array($payload) ? $payload : [];
    }

    /**
     * Ubah nilai input (sudah tervalidasi numeric/null) menjadi float atau null.
     */
    private function angka(mixed $nilai): ?float
    {
        return is_numeric($nilai) ? (float) $nilai : null;
    }
}
