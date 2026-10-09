<?php

declare(strict_types=1);

use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $tahun = TahunAjaran::create(['nama' => '2026/2027', 'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester' => 'Ganjil', 'is_active' => true]);
    $this->sesi = ['selected_tahun_ajaran_id' => $tahun->id, 'selected_semester' => 'Ganjil'];
});

dataset('halaman sync', [
    'siswa' => ['siswa.index', 'siswa.sync', 'siswa-mi'],
    'guru' => ['guru.index', 'guru.sync', 'guru-mi'],
    'mata pelajaran' => ['mata-pelajaran.index', 'mata-pelajaran.sync', 'mapel-mi'],
]);

it('menyembunyikan menu Sync API dan menutup rutenya bila SYNC_API_ENABLED=false', function (string $halaman, string $rute, string $sumber) {
    config(['services.data_induk.enabled' => false]);
    Http::fake();

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route($halaman))
        ->assertOk()
        ->assertDontSee('Sync API')
        ->assertDontSee('id="syncModal"', false);

    $this->post(route($rute), ['source' => $sumber])->assertNotFound();

    Http::assertNothingSent();
})->with('halaman sync');

it('menampilkan menu Sync API bila SYNC_API_ENABLED=true', function (string $halaman) {
    config(['services.data_induk.enabled' => true]);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route($halaman))
        ->assertOk()
        ->assertSee('Sync API')
        ->assertSee('id="syncModal"', false);
})->with('halaman sync');
