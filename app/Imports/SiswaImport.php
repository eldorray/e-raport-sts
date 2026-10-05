<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
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
class SiswaImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    /**
     * Kolom teks yang sering terbaca sebagai angka oleh Excel.
     *
     * @var list<string>
     */
    private const TEXT_COLUMNS = ['nis', 'nisn', 'kelas', 'tingkat', 'telpon', 'kelas_diterima'];

    /**
     * Batas angka yang masih presisi di Excel (15 digit signifikan).
     */
    private const EXCEL_PRECISION_LIMIT = 1e15;

    /** @var int Jumlah siswa yang berhasil diimpor */
    public int $imported = 0;

    /** @var array<int, array{nis: string|null, reason: string}> Baris yang dilewati */
    public array $skipped = [];

    /**
     * Memproses koleksi baris dari file Excel.
     *
     * @param  Collection<int, array<string, mixed>>  $rows  Koleksi baris data
     *
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
     * Mengubah sel angka pada kolom teks menjadi string sebelum validasi.
     *
     * Angka bulat diubah apa adanya. Angka desimal/eksponen yang bisa sudah
     * kehilangan digit dibiarkan agar gagal validasi `string` dengan pesan
     * yang meminta kolom diformat sebagai Teks.
     *
     * @param  array<string, mixed>  $data  Data satu baris
     * @param  int  $index  Nomor baris di file
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $data, int $index): array
    {
        foreach (self::TEXT_COLUMNS as $column) {
            if (array_key_exists($column, $data)) {
                $data[$column] = $this->numberToString($data[$column]);
            }
        }

        return $data;
    }

    /**
     * Pesan validasi khusus untuk kolom identitas yang terbaca sebagai angka.
     *
     * @return array<string, string>
     */
    public function customValidationMessages(): array
    {
        return [
            'nis.string' => __('NIS terbaca sebagai angka desimal/eksponen sehingga digitnya bisa berubah. Ubah format kolom NIS sebagai Teks di Excel, lalu ketik ulang nilainya.'),
            'nisn.string' => __('NISN terbaca sebagai angka desimal/eksponen sehingga digitnya bisa berubah. Ubah format kolom NISN sebagai Teks di Excel, lalu ketik ulang nilainya.'),
        ];
    }

    /**
     * Memproses satu baris data siswa.
     *
     * @param  mixed  $row  Data baris
     * @param  int  $tahunId  ID tahun ajaran
     */
    private function processRow($row, int $tahunId): void
    {
        $nis = $this->trimString($row['nis'] ?? '');

        if ($nis === '') {
            $this->skipped[] = ['nis' => null, 'reason' => __('NIS kosong')];

            return;
        }

        if (Siswa::where('tahun_ajaran_id', $tahunId)->where('nis', $nis)->exists()) {
            $this->skipped[] = ['nis' => $nis, 'reason' => __('NIS :nis sudah terdaftar di tahun ajaran ini', ['nis' => $nis])];

            return;
        }

        $nisn = $this->trimString($row['nisn'] ?? '');

        if ($nisn !== '' && Siswa::where('tahun_ajaran_id', $tahunId)->where('nisn', $nisn)->exists()) {
            $this->skipped[] = ['nis' => $nis, 'reason' => __('NISN :nisn sudah terdaftar di tahun ajaran ini', ['nisn' => $nisn])];

            return;
        }

        $data = $this->parseRowData($row, $tahunId);

        if ($data === null) {
            $this->skipped[] = ['nis' => $nis, 'reason' => __('NIS :nis: tanggal tidak valid', ['nis' => $nis])];

            return;
        }

        Siswa::create($data);
        $this->imported++;
    }

    /**
     * Mengurai data dari satu baris menjadi array untuk create.
     *
     * @param  mixed  $row  Data baris
     * @param  int  $tahunId  ID tahun ajaran
     * @return array<string, mixed>|null Array data siswa atau null jika parsing gagal
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
     * @param  int  $tahunId  ID tahun ajaran
     * @param  string|null  $tingkat  Tingkat kelas
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
     * Mengubah angka dari sel Excel menjadi string tanpa kehilangan digit.
     *
     * Angka desimal atau angka di atas batas presisi Excel dikembalikan apa
     * adanya karena digit aslinya tidak bisa dipastikan.
     *
     * @param  mixed  $value  Nilai sel
     * @return mixed String untuk angka yang aman, nilai asli untuk lainnya
     */
    private function numberToString(mixed $value): mixed
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value) && is_finite($value) && floor($value) === $value && abs($value) < self::EXCEL_PRECISION_LIMIT) {
            return sprintf('%.0f', $value);
        }

        return $value;
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
    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
        }

        return Carbon::parse((string) $value);
    }
}
