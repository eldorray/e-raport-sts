<?php

use App\Http\Controllers\Settings;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\RombelKelasController;
use App\Http\Controllers\MengajarController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\EkskulController;
use App\Http\Controllers\EkskulPenilaianController;
use App\Http\Controllers\RaporAdminController;
use App\Http\Controllers\RaportPrintController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Settings & profile (shared)
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');

    // All authenticated users can switch context (tahun ajaran & semester)
    Route::match(['get', 'patch'], 'tahun-ajaran/switch-session', [TahunAjaranController::class, 'switchSession'])
        ->name('tahun-ajaran.switch-session');

    // Admin-only
    Route::middleware('role:admin')->group(function () {
        Route::get('school-profile', [SchoolProfileController::class, 'index'])->name('school-profile.index');
        Route::put('school-profile', [SchoolProfileController::class, 'update'])->name('school-profile.update');
        Route::resource('mata-pelajaran', MataPelajaranController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('tahun-ajaran', TahunAjaranController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('tahun-ajaran/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate'])->name('tahun-ajaran.activate');
        Route::get('guru/import/template', [GuruController::class, 'template'])->name('guru.template');
        Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
        Route::resource('guru', GuruController::class)->except(['show']);
        Route::patch('guru/{guru}/toggle-status', [GuruController::class, 'toggleStatus'])->name('guru.toggle');
        Route::resource('kelas', KelasController::class)->except(['show'])->parameters([
            'kelas' => 'kelas',
        ]);
        Route::resource('ekskul', EkskulController::class)->except(['create', 'edit', 'show']);
        Route::get('mengajar', [MengajarController::class, 'index'])->name('mengajar.index');
        Route::post('mengajar', [MengajarController::class, 'store'])->name('mengajar.store');
        Route::delete('mengajar/{mengajar}', [MengajarController::class, 'destroy'])->name('mengajar.destroy');
        Route::put('mengajar/{mengajar}', [MengajarController::class, 'update'])->name('mengajar.update');
        Route::post('mengajar/copy', [MengajarController::class, 'copy'])->name('mengajar.copy');
        Route::get('rombel-kelas', [RombelKelasController::class, 'index'])->name('rombel.index');
        Route::put('rombel-kelas/{kelas}', [RombelKelasController::class, 'update'])->name('rombel.update');
        Route::get('siswa/import/template', [SiswaController::class, 'template'])->name('siswa.template');
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::resource('siswa', SiswaController::class);
    });

    // Admin & Guru: pengaturan bobot dan cetak rapor
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('penilaian/bobot', [PenilaianController::class, 'editBobot'])->name('penilaian.bobot.edit');
        Route::post('penilaian/bobot', [PenilaianController::class, 'updateBobot'])->name('penilaian.bobot.update');
        Route::get('rapor', [RaporAdminController::class, 'index'])->name('rapor.index');
        Route::get('rapor/{siswa}', [RaportPrintController::class, 'show'])->name('rapor.print');
    });

    // Guru / wali-only
    Route::middleware('role:guru')->group(function () {
        Route::get('pelajaran-saya', [MengajarController::class, 'mySubjects'])->name('guru.pelajaran');
        Route::get('ekskul-saya', [EkskulPenilaianController::class, 'index'])->name('guru.ekskul.index');
        Route::get('ekskul-saya/{ekskul}', [EkskulPenilaianController::class, 'show'])->name('guru.ekskul.show');
        Route::post('ekskul-saya/{ekskul}', [EkskulPenilaianController::class, 'store'])->name('guru.ekskul.store');
        Route::get('penilaian', [PenilaianController::class, 'index'])->name('guru.penilaian.index');
        Route::get('penilaian/{mengajar}', [PenilaianController::class, 'show'])->name('guru.penilaian.show');
        Route::post('penilaian/{mengajar}', [PenilaianController::class, 'store'])->name('guru.penilaian.store');
    });
});

require __DIR__.'/auth.php';
