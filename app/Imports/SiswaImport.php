<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Import class untuk data siswa dari file Excel.
 *
 * Menangani import massal data siswa dengan validasi dan skip duplikat.
 */
class SiswaImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /** @var int Jumlah siswa yang berhasil diimpor */
    public int $imported = 0;

    /** @var array<int, array{nis: string|null, reason: string}> Baris yang dilewati */
    public array $skipped = [];

    /**
     * Memproses koleksi baris dari file Excel.
     *
     * @param  Collection  $rows  Koleksi baris data
     * @return void
     * @throws \RuntimeException Jika tahun ajaran belum dipilih
     */
    public function collection(Collection $rows): void
    {
        $tahunId = Session::get('selected_tahun_ajaran_id');

        if (! $tahunId) {
            throw new \RuntimeException(__('Pilih tahun ajaran terlebih dahulu.'));
        }

        foreach ($rows as $row) {
            $this->processRow($row, $tahunId);
        }
    }

    /**
     * Mendefinisikan aturan validasi untuk setiap baris.
     *
     * @return array<string, array<int, string>>
     */
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

    /**
     * Memproses satu baris data siswa.
     *
     * @param  mixed  $row      Data baris
     * @param  int    $tahunId  ID tahun ajaran
     * @return void
     */
    private function processRow($row, int $tahunId): void
    {
        $nis = $this->trimString($row['nis'] ?? '');

        if ($nis === '') {
            $this->skipped[] = ['nis' => null, 'reason' => 'NIS kosong'];
            return;
        }

        if (Siswa::where('nis', $nis)->exists()) {
            $this->skipped[] = ['nis' => $nis, 'reason' => 'NIS sudah ada'];
            return;
        }

        $data = $this->parseRowData($row, $tahunId);

        if ($data === null) {
            $this->skipped[] = ['nis' => $nis, 'reason' => 'Tanggal tidak valid'];
            return;
        }

        Siswa::create($data);
        $this->imported++;
    }

    /**
     * Mengurai data dari satu baris menjadi array untuk create.
     *
     * @param  mixed  $row      Data baris
     * @param  int    $tahunId  ID tahun ajaran
     * @return array|null Array data siswa atau null jika parsing gagal
     */
    private function parseRowData($row, int $tahunId): ?array
    {
        $nis = $this->trimString($row['nis'] ?? '');
        $nisn = $this->trimString($row['nisn'] ?? '') ?: null;
        $nama = $this->trimString($row['nama'] ?? '');
        $kelasName = $this->trimString($row['kelas'] ?? '') ?: null;
        $tingkat = $this->trimString($row['tingkat'] ?? '') ?: null;
        $gender = strtoupper($this->trimString($row['jenis_kelamin'] ?? ''));

        try {
            $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
            $tanggalDiterima = $this->parseDate($row['tanggal_diterima'] ?? null);
        } catch (\Throwable) {
            return null;
        }

        $kelasId = $this->findKelasId($kelasName, $tahunId, $tingkat);

        return [
            'tahun_ajaran_id' => $tahunId,
            'nis' => $nis,
            'nisn' => $nisn,
            'nama' => $nama,
            'kelas_id' => $kelasId,
            'jenis_kelamin' => $gender,
            'tempat_lahir' => $this->trimString($row['tempat_lahir'] ?? '') ?: null,
            'tanggal_lahir' => $tanggalLahir,
            'agama' => $this->trimString($row['agama'] ?? '') ?: null,
            'status_keluarga' => $this->trimString($row['status_keluarga'] ?? '') ?: null,
            'anak_ke' => $this->parseInteger($row['anak_ke'] ?? null),
            'telpon' => $this->trimString($row['telpon'] ?? '') ?: null,
            'alamat' => $this->trimString($row['alamat'] ?? '') ?: null,
            'sekolah_asal' => $this->trimString($row['sekolah_asal'] ?? '') ?: null,
            'tanggal_diterima' => $tanggalDiterima,
            'kelas_diterima' => $this->trimString($row['kelas_diterima'] ?? '') ?: $kelasName,
            'nama_ayah' => $this->trimString($row['nama_ayah'] ?? '') ?: null,
            'nama_ibu' => $this->trimString($row['nama_ibu'] ?? '') ?: null,
            'pekerjaan_ayah' => $this->trimString($row['pekerjaan_ayah'] ?? '') ?: null,
            'pekerjaan_ibu' => $this->trimString($row['pekerjaan_ibu'] ?? '') ?: null,
            'alamat_orang_tua' => $this->trimString($row['alamat_orang_tua'] ?? '') ?: null,
            'nama_wali' => $this->trimString($row['nama_wali'] ?? '') ?: null,
            'pekerjaan_wali' => $this->trimString($row['pekerjaan_wali'] ?? '') ?: null,
            'alamat_wali' => $this->trimString($row['alamat_wali'] ?? '') ?: null,
        ];
    }

    /**
     * Mencari ID kelas berdasarkan nama dan tahun ajaran.
     *
     * @param  string|null  $kelasName  Nama kelas
     * @param  int          $tahunId    ID tahun ajaran
     * @param  string|null  $tingkat    Tingkat kelas
     * @return int|null ID kelas atau null
     */
    private function findKelasId(?string $kelasName, int $tahunId, ?string $tingkat): ?int
    {
        if (! $kelasName) {
            return null;
        }

        $kelas = Kelas::where('nama', $kelasName)
            ->where('tahun_ajaran_id', $tahunId)
            ->when($tingkat, fn ($q) => $q->where('tingkat', $tingkat))
            ->first();

        return $kelas?->id;
    }

    /**
     * Trim dan konversi nilai ke string.
     *
     * @param  mixed  $value  Nilai yang akan di-trim
     * @return string String yang sudah di-trim
     */
    private function trimString($value): string
    {
        return trim((string) $value);
    }

    /**
     * Parse nilai menjadi integer atau null.
     *
     * @param  mixed  $value  Nilai yang akan di-parse
     * @return int|null Integer atau null
     */
    private function parseInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Parse tanggal dari berbagai format.
     *
     * Mendukung format Excel numeric dan string date.
     *
     * @param  mixed  $value  Nilai tanggal
     * @return Carbon|null Instance Carbon atau null
     */
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
