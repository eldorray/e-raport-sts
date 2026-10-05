<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    /**
     * Kolom teks yang sering terbaca sebagai angka oleh Excel.
     *
     * @var list<string>
     */
    private const TEXT_COLUMNS = ['nip', 'nik', 'wali_kelas', 'password'];

    /**
     * Batas angka yang masih presisi di Excel (15 digit signifikan).
     */
    private const EXCEL_PRECISION_LIMIT = 1e15;

    public int $imported = 0;

    /** @var list<array{nip: string|null, reason: string}> */
    public array $skipped = [];

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nip = trim((string) ($row['nip'] ?? ''));

            if ($nip === '') {
                $this->skipped[] = ['nip' => null, 'reason' => __('NIP kosong')];

                continue;
            }

            if (Guru::where('nip', $nip)->exists()) {
                $this->skipped[] = ['nip' => $nip, 'reason' => __('NIP :nip sudah terdaftar', ['nip' => $nip])];

                continue;
            }

            $nama = trim((string) ($row['nama'] ?? ''));
            $nik = trim((string) ($row['nik'] ?? '')) ?: null;
            $gender = strtoupper(trim((string) ($row['jenis_kelamin'] ?? '')));
            $tempat = trim((string) ($row['tempat_lahir'] ?? '')) ?: null;
            $pendidikan = trim((string) ($row['pendidikan'] ?? '')) ?: null;
            $wali = trim((string) ($row['wali_kelas'] ?? '')) ?: null;
            $jtmRaw = $row['jtm'] ?? null;
            $jtm = $jtmRaw !== null && $jtmRaw !== '' ? (int) $jtmRaw : null;
            $passwordPlain = trim((string) ($row['password'] ?? '')) ?: $nip;
            $isActive = $this->toBoolean($row['is_active'] ?? null);

            try {
                $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
            } catch (\Throwable $e) {
                $this->skipped[] = ['nip' => $nip, 'reason' => __('NIP :nip: tanggal lahir tidak valid', ['nip' => $nip])];

                continue;
            }

            $user = User::create([
                'name' => $nama,
                'email' => $this->buildEmail($nip),
                'password' => Hash::make($passwordPlain),
                'role' => 'guru',
                'nip' => $nip,
                'nik' => $nik,
                'is_active' => $isActive,
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nama' => $nama,
                'nip' => $nip,
                'nik' => $nik,
                'jenis_kelamin' => $gender,
                'tempat_lahir' => $tempat,
                'tanggal_lahir' => $tanggalLahir,
                'pendidikan' => $pendidikan,
                'wali_kelas' => $wali,
                'jtm' => $jtm,
                'initial_password' => $passwordPlain,
                'is_active' => $isActive,
            ]);

            $this->imported++;
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            '*.nip' => ['required', 'string', 'max:30'],
            '*.nama' => ['required', 'string', 'max:255'],
            '*.jenis_kelamin' => ['required', 'in:L,P,l,p'],
            '*.nik' => ['nullable', 'string', 'max:30'],
            '*.tempat_lahir' => ['nullable', 'string', 'max:100'],
            '*.tanggal_lahir' => ['nullable'],
            '*.pendidikan' => ['nullable', 'string', 'max:100'],
            '*.wali_kelas' => ['nullable', 'string', 'max:50'],
            '*.jtm' => ['nullable', 'integer', 'min:0'],
            '*.password' => ['nullable', 'string', 'min:3'],
            '*.is_active' => ['nullable'],
        ];
    }

    /**
     * Mengubah sel angka pada kolom teks menjadi string sebelum validasi.
     *
     * Angka bulat diubah apa adanya. Angka desimal/eksponen yang bisa sudah
     * kehilangan digit (mis. NIP 18 digit) dibiarkan agar gagal validasi
     * `string` dengan pesan yang meminta kolom diformat sebagai Teks.
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
            'nip.string' => __('NIP terbaca sebagai angka desimal/eksponen sehingga digitnya bisa berubah. Ubah format kolom NIP sebagai Teks di Excel, lalu ketik ulang nilainya.'),
            'nik.string' => __('NIK terbaca sebagai angka desimal/eksponen sehingga digitnya bisa berubah. Ubah format kolom NIK sebagai Teks di Excel, lalu ketik ulang nilainya.'),
        ];
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

    private function toBoolean(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'ya'], true);
    }

    private function buildEmail(string $nip): string
    {
        return Str::slug($nip, '.').'@guru.local';
    }
}
