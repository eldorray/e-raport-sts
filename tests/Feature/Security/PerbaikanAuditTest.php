<?php

declare(strict_types=1);

use App\Imports\GuruImport;
use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->tahun = TahunAjaran::create([
        'nama' => '2026/2027', 'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester' => 'Ganjil', 'is_active' => true,
    ]);
    $this->sesi = ['selected_tahun_ajaran_id' => $this->tahun->id, 'selected_semester' => 'Ganjil'];
});

function guruUji(string $nip, string $sandi): User
{
    $user = User::factory()->create(['role' => 'guru', 'nip' => $nip, 'nik' => $nip, 'password' => Hash::make($sandi), 'is_active' => true]);
    Guru::factory()->create(['user_id' => $user->id, 'nip' => $nip, 'nik' => $nip]);

    return $user->fresh();
}

// --- Temuan 1: skrip pembantu di web root ---

it('tidak menyisakan skrip pemeliharaan yang bisa dijalankan dari web root', function (string $berkas) {
    expect(file_exists(public_path($berkas)))->toBeFalse();
})->with(['run-migrate.php', 'add-logo-right-column.php', 'create-storage-link.php']);

// --- Temuan 2: kredensial admin di seeder ---

it('tidak membuat akun admin bila ADMIN_EMAIL dan ADMIN_PASSWORD kosong', function () {
    config(['auth.admin_awal' => ['email' => null, 'password' => null]]);

    $this->seed(AdminSeeder::class);

    expect(User::where('role', 'admin')->count())->toBe(0);
});

it('membuat akun admin dari .env dan tidak menimpa akun yang sudah ada', function () {
    config(['auth.admin_awal' => ['email' => 'admin-uji@example.test', 'password' => 'sandi-uji-panjang-1']]);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin-uji@example.test')->firstOrFail();
    expect($admin->role)->toBe('admin')
        ->and(Hash::check('sandi-uji-panjang-1', $admin->password))->toBeTrue();

    $admin->forceFill(['password' => Hash::make('sudah-diganti-admin'), 'is_active' => false])->save();
    $this->seed(AdminSeeder::class);

    $admin->refresh();
    expect(Hash::check('sudah-diganti-admin', $admin->password))->toBeTrue()
        ->and($admin->is_active)->toBeFalse();
});

it('menolak ADMIN_PASSWORD yang terlalu pendek', function () {
    config(['auth.admin_awal' => ['email' => 'admin-uji@example.test', 'password' => 'pendek']]);

    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(0);
});

// --- Temuan 3: kata sandi awal guru ---

it('memberi guru hasil sync kata sandi acak, bukan NIK', function () {
    config(['services.data_induk.enabled' => true]);
    Http::fake(['*/api/guru-mi/all*' => Http::response([
        'data' => [['nik' => '9900000000000001', 'full_name' => 'Guru Uji', 'gender' => 'L']],
        'current_page' => 1, 'last_page' => 1,
    ])]);
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->withSession($this->sesi)->post(route('guru.sync'), ['source' => 'guru-mi']);

    $user = User::where('nip', '9900000000000001')->firstOrFail();
    $guru = Guru::where('user_id', $user->id)->firstOrFail();

    expect(Hash::check('9900000000000001', $user->password))->toBeFalse()
        ->and($user->must_change_password)->toBeTrue()
        ->and($guru->initial_password)->not->toBe('9900000000000001')
        ->and(Hash::check((string) $guru->initial_password, $user->password))->toBeTrue();
});

it('memberi guru hasil impor tanpa kolom password kata sandi acak, bukan NIP', function () {
    (new GuruImport)->collection(collect([
        ['nip' => '990000000000000002', 'nama' => 'Guru Impor', 'jenis_kelamin' => 'L', 'password' => null],
    ]));

    $user = User::where('nip', '990000000000000002')->firstOrFail();

    expect(Hash::check('990000000000000002', $user->password))->toBeFalse()
        ->and($user->must_change_password)->toBeTrue();
});

it('menandai akun lama yang login dengan NIP sebagai kata sandi dan menahannya di halaman ganti kata sandi', function () {
    $user = guruUji('9900000000000003', '9900000000000003');

    $this->post('/login', ['login' => '9900000000000003', 'password' => '9900000000000003', 'tahun_ajaran_id' => $this->tahun->id]);

    expect($user->fresh()->must_change_password)->toBeTrue();

    $this->get(route('dashboard'))->assertRedirect(route('settings.password.edit'));
    $this->get(route('settings.password.edit'))->assertOk();
});

it('melepas kewajiban setelah kata sandi diganti dan menolak NIP sebagai kata sandi baru', function () {
    $user = guruUji('9900000000000004', 'sandi-awal-acak');
    $user->forceFill(['must_change_password' => true])->save();

    $this->actingAs($user->fresh())->withSession($this->sesi)
        ->put(route('settings.password.update'), [
            'current_password' => 'sandi-awal-acak', 'password' => '9900000000000004', 'password_confirmation' => '9900000000000004',
        ])->assertSessionHasErrors('password');

    $this->actingAs($user->fresh())->withSession($this->sesi)
        ->put(route('settings.password.update'), [
            'current_password' => 'sandi-awal-acak', 'password' => 'sandi-baru-guru-1', 'password_confirmation' => 'sandi-baru-guru-1',
        ])->assertSessionHasNoErrors();

    expect($user->fresh()->must_change_password)->toBeFalse();

    $this->actingAs($user->fresh())->withSession($this->sesi)->get(route('dashboard'))->assertOk();
});

it('merotasi kata sandi guru yang masih sama dengan NIP lewat perintah artisan', function () {
    $bawaan = guruUji('9900000000000005', '9900000000000005');
    $aman = guruUji('9900000000000006', 'sudah-diganti-guru');

    $this->artisan('guru:rotasi-sandi-bawaan', ['--dry-run' => true])->assertSuccessful();
    expect(Hash::check('9900000000000005', $bawaan->fresh()->password))->toBeTrue();

    $this->artisan('guru:rotasi-sandi-bawaan')->assertSuccessful();

    $bawaan->refresh();
    expect(Hash::check('9900000000000005', $bawaan->password))->toBeFalse()
        ->and($bawaan->must_change_password)->toBeTrue()
        ->and(Hash::check((string) Guru::where('user_id', $bawaan->id)->value('initial_password'), $bawaan->password))->toBeTrue()
        ->and(Hash::check('sudah-diganti-guru', $aman->fresh()->password))->toBeTrue()
        ->and($aman->fresh()->must_change_password)->toBeFalse();
});

// --- Temuan 4: akun nonaktif ---

it('memutus sesi yang sudah berjalan begitu akun dinonaktifkan', function () {
    $user = guruUji('9900000000000007', 'sandi-guru-aktif');
    $tokenLama = $user->remember_token;

    $this->actingAs($user)->withSession($this->sesi)->get(route('dashboard'))->assertOk();

    User::whereKey($user->id)->update(['is_active' => false]);

    $this->actingAs($user->fresh())->withSession($this->sesi)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('login');

    $this->assertGuest();
    // remember_token diputar, jadi cookie "Ingat saya" lama tidak bisa dipakai lagi.
    expect($user->fresh()->remember_token)->not->toBe($tokenLama);
});

it('tidak mengganggu akun aktif', function () {
    $user = guruUji('9900000000000008', 'sandi-guru-aktif');

    $this->actingAs($user)->withSession($this->sesi)->get(route('dashboard'))->assertOk();
    $this->assertAuthenticatedAs($user);
});
