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
use App\Http\Controllers\RaporDataController;
use App\Http\Controllers\PrintSettingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TahfidzController;
use App\Http\Controllers\TahfidzPrintController;
use App\Http\Controllers\MengajarTahfidzController;
use App\Http\Controllers\WaliKelasSiswaController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
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
        Route::patch('tahun-ajaran/{tahunAjaran}/toggle-active', [TahunAjaranController::class, 'toggleActive'])->name('tahun-ajaran.toggle-active');
        Route::get('guru/import/template', [GuruController::class, 'template'])->name('guru.template');
        Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
        Route::delete('guru/delete-all', [GuruController::class, 'destroyAll'])->name('guru.destroy-all');
        Route::resource('guru', GuruController::class)->except(['show']);
        Route::patch('guru/{guru}/toggle-status', [GuruController::class, 'toggleStatus'])->name('guru.toggle');
        Route::post('guru/sync', [GuruController::class, 'syncFromApi'])->name('guru.sync');
        Route::post('siswa/sync', [SiswaController::class, 'syncFromApi'])->name('siswa.sync');
        Route::post('mata-pelajaran/sync', [MataPelajaranController::class, 'syncFromApi'])->name('mata-pelajaran.sync');
        Route::resource('kelas', KelasController::class)->except(['show'])->parameters([
            'kelas' => 'kelas',
        ]);
        Route::post('kelas/copy', [KelasController::class, 'copy'])->name('kelas.copy');
        Route::resource('ekskul', EkskulController::class)->except(['create', 'edit', 'show']);
        Route::get('mengajar', [MengajarController::class, 'index'])->name('mengajar.index');
        Route::post('mengajar', [MengajarController::class, 'store'])->name('mengajar.store');
        Route::delete('mengajar/{mengajar}', [MengajarController::class, 'destroy'])->name('mengajar.destroy');
        Route::put('mengajar/{mengajar}', [MengajarController::class, 'update'])->name('mengajar.update');
        Route::post('mengajar/copy', [MengajarController::class, 'copy'])->name('mengajar.copy');

        // Mengajar Tahfidz
        Route::get('mengajar-tahfidz', [MengajarTahfidzController::class, 'index'])->name('mengajar-tahfidz.index');
        Route::post('mengajar-tahfidz', [MengajarTahfidzController::class, 'store'])->name('mengajar-tahfidz.store');
        Route::delete('mengajar-tahfidz/{mengajarTahfidz}', [MengajarTahfidzController::class, 'destroy'])->name('mengajar-tahfidz.destroy');

        Route::get('rombel-kelas', [RombelKelasController::class, 'index'])->name('rombel.index');
        Route::put('rombel-kelas/{kelas}', [RombelKelasController::class, 'update'])->name('rombel.update');
        Route::post('rombel-kelas/copy', [RombelKelasController::class, 'copy'])->name('rombel.copy');
        Route::get('siswa/import/template', [SiswaController::class, 'template'])->name('siswa.template');
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::patch('siswa/{siswa}/toggle-status', [SiswaController::class, 'toggleStatus'])->name('siswa.toggle');
        Route::delete('siswa/delete-all', [SiswaController::class, 'destroyAll'])->name('siswa.destroy-all');
        Route::resource('siswa', SiswaController::class);
        Route::get('rapor/pengaturan-cetak', [PrintSettingController::class, 'edit'])->name('rapor.print-settings.edit');
        Route::post('rapor/pengaturan-cetak', [PrintSettingController::class, 'update'])->name('rapor.print-settings.update');
        Route::resource('users', AdminUserController::class)->except(['show']);

        // Backup & Restore
        Route::get('backup', [BackupController::class, 'index'])->name('backup.index');
        Route::get('backup/download', [BackupController::class, 'download'])->name('backup.download');
        Route::post('backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
    });

    // Admin & Guru: pengaturan bobot dan cetak rapor
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('penilaian/bobot', [PenilaianController::class, 'editBobot'])->name('penilaian.bobot.edit');
        Route::post('penilaian/bobot', [PenilaianController::class, 'updateBobot'])->name('penilaian.bobot.update');
        Route::get('rapor', [RaporAdminController::class, 'index'])->name('rapor.index');
        Route::get('rapor/kelas/{kelas}/ledger', [RaporAdminController::class, 'ledger'])->name('rapor.ledger');
        Route::get('rapor/absen', [RaporDataController::class, 'absen'])->name('rapor.absen');
        Route::post('rapor/absen', [RaporDataController::class, 'absenStore'])->name('rapor.absen.store');
        Route::get('rapor/prestasi', [RaporDataController::class, 'prestasi'])->name('rapor.prestasi');
        Route::post('rapor/prestasi', [RaporDataController::class, 'prestasiStore'])->name('rapor.prestasi.store');
        Route::get('rapor/catatan', [RaporDataController::class, 'catatan'])->name('rapor.catatan');
        Route::post('rapor/catatan', [RaporDataController::class, 'catatanStore'])->name('rapor.catatan.store');

        // Tahfidz
        Route::get('tahfidz', [TahfidzController::class, 'index'])->name('tahfidz.index');
        Route::get('tahfidz/{siswa}', [TahfidzController::class, 'show'])->whereNumber('siswa')->name('tahfidz.show');
        Route::post('tahfidz/{siswa}', [TahfidzController::class, 'store'])->whereNumber('siswa')->name('tahfidz.store');
        Route::delete('tahfidz/{siswa}/reset', [TahfidzController::class, 'reset'])->whereNumber('siswa')->name('tahfidz.reset');
        Route::get('tahfidz/{siswa}/print', [TahfidzPrintController::class, 'show'])->whereNumber('siswa')->name('tahfidz.print');

        // Route dengan parameter dinamis harus di akhir
        Route::get('rapor/{siswa}', [RaportPrintController::class, 'show'])->whereNumber('siswa')->name('rapor.print');
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
        Route::delete('penilaian/{mengajar}/reset', [PenilaianController::class, 'reset'])->name('guru.penilaian.reset');

        // Wali Kelas: kelola siswa di kelas
        Route::get('wali-kelas/siswa', [WaliKelasSiswaController::class, 'index'])->name('wali-kelas.siswa.index');
        Route::post('wali-kelas/siswa', [WaliKelasSiswaController::class, 'store'])->name('wali-kelas.siswa.store');
        Route::get('wali-kelas/siswa/{siswa}', [WaliKelasSiswaController::class, 'show'])->name('wali-kelas.siswa.show');
        Route::put('wali-kelas/siswa/{siswa}', [WaliKelasSiswaController::class, 'update'])->name('wali-kelas.siswa.update');
        Route::delete('wali-kelas/siswa/{siswa}', [WaliKelasSiswaController::class, 'destroy'])->name('wali-kelas.siswa.destroy');
        Route::patch('wali-kelas/siswa/{siswa}/toggle-status', [WaliKelasSiswaController::class, 'toggleStatus'])->name('wali-kelas.siswa.toggle');
    });
});

require __DIR__.'/auth.php';
