<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public int $imported = 0;
    public array $skipped = [];

    public function collection(Collection $rows): void
    {
        $tahunId = Session::get('selected_tahun_ajaran_id');

        if (! $tahunId) {
            throw new \RuntimeException(__('Pilih tahun ajaran terlebih dahulu.'));
        }

        foreach ($rows as $row) {
            $nis = trim((string) ($row['nis'] ?? ''));

            if ($nis === '') {
                $this->skipped[] = ['nis' => null, 'reason' => 'NIS kosong'];
                continue;
            }

            if (Siswa::where('nis', $nis)->exists()) {
                $this->skipped[] = ['nis' => $nis, 'reason' => 'NIS sudah ada'];
                continue;
            }

            $nisn = trim((string) ($row['nisn'] ?? '')) ?: null;
            $nama = trim((string) ($row['nama'] ?? ''));
            $kelasName = trim((string) ($row['kelas'] ?? '')) ?: null;
            $tingkat = trim((string) ($row['tingkat'] ?? '')) ?: null;
            $gender = strtoupper(trim((string) ($row['jenis_kelamin'] ?? '')));
            $tempat = trim((string) ($row['tempat_lahir'] ?? '')) ?: null;
            $agama = trim((string) ($row['agama'] ?? '')) ?: null;
            $statusKeluarga = trim((string) ($row['status_keluarga'] ?? '')) ?: null;
            $anakKe = $row['anak_ke'] !== null && $row['anak_ke'] !== '' ? (int) $row['anak_ke'] : null;
            $telpon = trim((string) ($row['telpon'] ?? '')) ?: null;
            $alamat = trim((string) ($row['alamat'] ?? '')) ?: null;
            $sekolahAsal = trim((string) ($row['sekolah_asal'] ?? '')) ?: null;
            $kelasDiterima = trim((string) ($row['kelas_diterima'] ?? '')) ?: $kelasName;
            $namaAyah = trim((string) ($row['nama_ayah'] ?? '')) ?: null;
            $namaIbu = trim((string) ($row['nama_ibu'] ?? '')) ?: null;
            $pekerjaanAyah = trim((string) ($row['pekerjaan_ayah'] ?? '')) ?: null;
            $pekerjaanIbu = trim((string) ($row['pekerjaan_ibu'] ?? '')) ?: null;
            $alamatOrangTua = trim((string) ($row['alamat_orang_tua'] ?? '')) ?: null;
            $namaWali = trim((string) ($row['nama_wali'] ?? '')) ?: null;
            $pekerjaanWali = trim((string) ($row['pekerjaan_wali'] ?? '')) ?: null;
            $alamatWali = trim((string) ($row['alamat_wali'] ?? '')) ?: null;

            try {
                $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
                $tanggalDiterima = $this->parseDate($row['tanggal_diterima'] ?? null);
            } catch (\Throwable $e) {
                $this->skipped[] = ['nis' => $nis, 'reason' => 'Tanggal tidak valid'];
                continue;
            }

            $kelasId = null;
            if ($kelasName) {
                $kelas = Kelas::where('nama', $kelasName)
                    ->where('tahun_ajaran_id', $tahunId)
                    ->when($tingkat, fn ($q) => $q->where('tingkat', $tingkat))
                    ->first();
                $kelasId = $kelas?->id;
            }

            Siswa::create([
                'tahun_ajaran_id' => $tahunId,
                'nis' => $nis,
                'nisn' => $nisn,
                'nama' => $nama,
                'kelas_id' => $kelasId,
                'kelas_diterima' => $kelasName,
                'jenis_kelamin' => $gender,
                'tempat_lahir' => $tempat,
                'tanggal_lahir' => $tanggalLahir,
                'agama' => $agama,
                'status_keluarga' => $statusKeluarga,
                'anak_ke' => $anakKe,
                'telpon' => $telpon,
                'alamat' => $alamat,
                'sekolah_asal' => $sekolahAsal,
                'tanggal_diterima' => $tanggalDiterima,
                'kelas_diterima' => $kelasDiterima,
                'nama_ayah' => $namaAyah,
                'nama_ibu' => $namaIbu,
                'pekerjaan_ayah' => $pekerjaanAyah,
                'pekerjaan_ibu' => $pekerjaanIbu,
                'alamat_orang_tua' => $alamatOrangTua,
                'nama_wali' => $namaWali,
                'pekerjaan_wali' => $pekerjaanWali,
                'alamat_wali' => $alamatWali,
            ]);

            $this->imported++;
        }
    }

    public function rules(): array
    {
        return [
            '*.nis' => ['required', 'string', 'max:30'],
            '*.nisn' => ['nullable', 'string', 'max:30'],
            '*.nama' => ['required', 'string', 'max:255'],
            '*.kelas' => ['nullable', 'string', 'max:50'],
            '*.tingkat' => ['nullable', 'string', 'max:20'],
            '*.jenis_kelamin' => ['required', 'in:L,P,l,p'],
            '*.tempat_lahir' => ['nullable', 'string', 'max:100'],
            '*.tanggal_lahir' => ['nullable'],
            '*.agama' => ['nullable', 'string', 'max:50'],
            '*.status_keluarga' => ['nullable', 'string', 'max:50'],
            '*.anak_ke' => ['nullable', 'integer', 'min:1'],
            '*.telpon' => ['nullable', 'string', 'max:30'],
            '*.alamat' => ['nullable', 'string'],
            '*.sekolah_asal' => ['nullable', 'string', 'max:150'],
            '*.tanggal_diterima' => ['nullable'],
            '*.kelas_diterima' => ['nullable', 'string', 'max:50'],
            '*.nama_ayah' => ['nullable', 'string', 'max:150'],
            '*.nama_ibu' => ['nullable', 'string', 'max:150'],
            '*.pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            '*.pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            '*.alamat_orang_tua' => ['nullable', 'string'],
            '*.nama_wali' => ['nullable', 'string', 'max:150'],
            '*.pekerjaan_wali' => ['nullable', 'string', 'max:100'],
            '*.alamat_wali' => ['nullable', 'string'],
        ];
    }

    private function parseDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        }

        return Carbon::parse($value);
    }
}
