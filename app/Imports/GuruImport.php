<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public int $imported = 0;
    public array $skipped = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $nip = trim((string) ($row['nip'] ?? ''));

            if ($nip === '') {
                $this->skipped[] = ['nip' => null, 'reason' => 'NIP kosong'];
                continue;
            }

            if (Guru::where('nip', $nip)->exists()) {
                $this->skipped[] = ['nip' => $nip, 'reason' => 'NIP sudah ada'];
                continue;
            }

            $nama = trim((string) ($row['nama'] ?? ''));
            $nik = trim((string) ($row['nik'] ?? '')) ?: null;
            $gender = strtoupper(trim((string) ($row['jenis_kelamin'] ?? '')));
            $tempat = trim((string) ($row['tempat_lahir'] ?? '')) ?: null;
            $pendidikan = trim((string) ($row['pendidikan'] ?? '')) ?: null;
            $wali = trim((string) ($row['wali_kelas'] ?? '')) ?: null;
            $jtm = $row['jtm'] !== null && $row['jtm'] !== '' ? (int) $row['jtm'] : null;
            $passwordPlain = trim((string) ($row['password'] ?? '')) ?: $nip;
            $isActive = $this->toBoolean($row['is_active'] ?? null);

            try {
                $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
            } catch (\Throwable $e) {
                $this->skipped[] = ['nip' => $nip, 'reason' => 'Tanggal lahir tidak valid'];
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

    private function toBoolean($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'ya'], true);
    }

    private function buildEmail(string $nip): string
    {
        return Str::slug($nip, '.') . '@guru.local';
    }
}
