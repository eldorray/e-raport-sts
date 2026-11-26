<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Mengganti kata sandi guru yang masih sama dengan NIP/NIK-nya.
 *
 * Versi lama memberi akun hasil sync/impor kata sandi yang sama dengan NIP/NIK, padahal
 * keduanya juga diterima sebagai nama login. Perintah ini mengganti kata sandi akun-akun
 * tersebut dengan nilai acak, menyimpannya di kolom Password daftar guru untuk dibagikan
 * admin, dan mewajibkan guru menggantinya saat login berikutnya.
 */
class RotasiSandiBawaanGuru extends Command
{
    protected $signature = 'guru:rotasi-sandi-bawaan {--dry-run : Hanya hitung akun yang terdampak, tanpa mengubah apa pun}';

    protected $description = 'Ganti kata sandi guru yang masih sama dengan NIP/NIK dengan kata sandi acak';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $jumlah = 0;

        User::where('role', 'guru')->orderBy('id')->each(function (User $user) use ($dryRun, &$jumlah): void {
            $pengenal = array_filter([$user->nip, $user->nik]);
            $masihBawaan = collect($pengenal)->contains(fn (string $nilai): bool => Hash::check($nilai, $user->password));

            if (! $masihBawaan) {
                return;
            }

            $jumlah++;

            if ($dryRun) {
                return;
            }

            $sandiBaru = Str::password(12, symbols: false);

            $user->forceFill([
                'password' => Hash::make($sandiBaru),
                'must_change_password' => true,
                // Memutus cookie "Ingat saya" yang dibuat dengan kata sandi lama.
                'remember_token' => Str::random(60),
            ])->save();

            Guru::where('user_id', $user->id)->update(['initial_password' => $sandiBaru]);
        });

        $this->info($dryRun
            ? "{$jumlah} akun guru masih memakai NIP/NIK sebagai kata sandi."
            : "{$jumlah} akun guru diberi kata sandi acak baru. Lihat kolom Password di daftar guru untuk membagikannya.");

        return self::SUCCESS;
    }
}
