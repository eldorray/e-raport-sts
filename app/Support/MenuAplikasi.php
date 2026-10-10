<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Satu sumber daftar menu aplikasi HP dan judul halaman dalam mode aplikasi.
 */
class MenuAplikasi
{
    /**
     * Judul halaman web yang tampil di header bingkai aplikasi, berdasarkan nama rute.
     * Kunci dengan akhiran ".*" berlaku untuk semua rute berawalan sama.
     *
     * @var array<string, string>
     */
    private const JUDUL = [
        'dashboard' => 'Dashboard',
        'school-profile.*' => 'Profil Sekolah',
        'tahun-ajaran.*' => 'Tahun Ajaran',
        'tahun-ajaran-baru.*' => 'Tahun Ajaran Baru',
        'mata-pelajaran.*' => 'Mata Pelajaran',
        'kelas.*' => 'Kelas',
        'ekskul.*' => 'Ekskul',
        'guru.index' => 'Guru',
        'guru.create' => 'Guru',
        'guru.edit' => 'Guru',
        'mengajar.*' => 'Mengajar',
        'mengajar-tahfidz.*' => 'Mengajar Tahfidz',
        'siswa.*' => 'Siswa',
        'rombel.*' => 'Rombel Kelas',
        'users.*' => 'Manajemen User',
        'rapor.print-settings.*' => 'Pengaturan Cetak',
        'rapor.index' => 'Cetak Rapor',
        'rapor.absen' => 'Data Absen',
        'rapor.prestasi' => 'Prestasi Siswa',
        'rapor.catatan' => 'Catatan Wali',
        'koreksi-nilai.*' => 'Koreksi Nilai',
        'tahfidz.*' => 'Raport Tahfidz',
        'backup.*' => 'Backup',
        'settings.profile.*' => 'Profil',
        'settings.password.*' => 'Kata Sandi',
        'settings.appearance.*' => 'Tampilan',
        'penilaian.bobot.*' => 'Bobot Nilai',
        'guru.penilaian.*' => 'Penilaian',
        'guru.pelajaran' => 'Pelajaran Saya',
        'guru.ekskul.*' => 'Ekskul Saya',
        'wali-kelas.siswa.*' => 'Siswa Kelas',
    ];

    /**
     * Semua menu admin versi web, dikelompokkan seperti sidebar.
     *
     * @return list<array{judul: string, item: list<array{label: string, keterangan: string, ikon: string, url: string}>}>
     */
    public static function admin(): array
    {
        return [
            [
                'judul' => __('Umum'),
                'item' => [
                    self::item(__('Dashboard Web'), __('Ringkasan lengkap versi desktop'), 'fa-gauge', 'dashboard'),
                    self::item(__('Profil Sekolah'), __('Identitas madrasah dan kepala madrasah'), 'fa-school', 'school-profile.index'),
                ],
            ],
            [
                'judul' => __('Lembaga'),
                'item' => [
                    self::item(__('Tahun Ajaran'), __('Daftar tahun ajaran dan semester'), 'fa-calendar', 'tahun-ajaran.index'),
                    self::item(__('Tahun Ajaran Baru'), __('Lanjutkan kelas dan siswa ke tahun berikutnya'), 'fa-forward', 'tahun-ajaran-baru.create'),
                    self::item(__('Mata Pelajaran'), __('Daftar mapel dan kodenya'), 'fa-book', 'mata-pelajaran.index'),
                    self::item(__('Kelas'), __('Kelas dan wali kelas'), 'fa-layer-group', 'kelas.index'),
                    self::item(__('Ekskul'), __('Ekstrakurikuler dan pembinanya'), 'fa-star', 'ekskul.index'),
                ],
            ],
            [
                'judul' => __('Guru'),
                'item' => [
                    self::item(__('Guru'), __('Data guru dan akunnya'), 'fa-user', 'guru.index'),
                    self::item(__('Mengajar'), __('Penugasan mapel per kelas'), 'fa-person-chalkboard', 'mengajar.index'),
                    self::item(__('Mengajar Tahfidz'), __('Pembimbing tahfidz per kelas'), 'fa-book-quran', 'mengajar-tahfidz.index'),
                ],
            ],
            [
                'judul' => __('Siswa'),
                'item' => [
                    self::item(__('Siswa'), __('Data induk siswa'), 'fa-user-graduate', 'siswa.index'),
                    self::item(__('Rombel Kelas'), __('Pembagian siswa ke kelas'), 'fa-people-roof', 'rombel.index'),
                ],
            ],
            [
                'judul' => __('Pengguna'),
                'item' => [
                    self::item(__('Manajemen User'), __('Akun admin dan guru'), 'fa-user-gear', 'users.index'),
                ],
            ],
            [
                'judul' => __('Rapor'),
                'item' => [
                    self::item(__('Pengaturan Cetak'), __('Tanggal rapor, tanda tangan, dan kertas'), 'fa-gear', 'rapor.print-settings.edit'),
                    self::item(__('Cetak Rapor'), __('Rapor dan leger per kelas'), 'fa-file-lines', 'rapor.index'),
                    self::item(__('Koreksi Nilai'), __('Periksa dan perbaiki nilai per kelas'), 'fa-pen-to-square', 'koreksi-nilai.index'),
                    self::item(__('Raport Tahfidz'), __('Penilaian dan cetak rapor tahfidz'), 'fa-book-quran', 'tahfidz.index'),
                ],
            ],
            [
                'judul' => __('Sistem'),
                'item' => [
                    self::item(__('Backup'), __('Unduh cadangan database'), 'fa-database', 'backup.index'),
                    self::item(__('Setting Tampilan'), __('Tema tampilan versi web'), 'fa-palette', 'settings.appearance.edit'),
                ],
            ],
        ];
    }

    /**
     * Judul untuk header bingkai aplikasi; null bila rute tidak dikenal.
     */
    public static function judul(?string $rute): ?string
    {
        if ($rute === null) {
            return null;
        }

        foreach (self::JUDUL as $pola => $judul) {
            $cocok = str_ends_with($pola, '.*')
                ? str_starts_with($rute, substr($pola, 0, -1))
                : $rute === $pola;

            if ($cocok) {
                return __($judul);
            }
        }

        return null;
    }

    /**
     * Tujuan tombol kembali di header: halaman sebelumnya bila ada, selain itu beranda aplikasi.
     */
    public static function kembali(Request $request): string
    {
        $sebelumnya = url()->previous();

        if ($sebelumnya !== $request->fullUrl() && str_starts_with($sebelumnya, $request->getSchemeAndHttpHost())) {
            return $sebelumnya;
        }

        return $request->user()?->role === 'admin' ? route('admin.pwa.menu') : route('guru.pwa.beranda');
    }

    /**
     * Satu butir menu yang menautkan ke halaman web.
     *
     * @return array{label: string, keterangan: string, ikon: string, url: string}
     */
    private static function item(string $label, string $keterangan, string $ikon, string $rute): array
    {
        return [
            'label' => $label,
            'keterangan' => $keterangan,
            'ikon' => $ikon,
            'url' => route($rute),
        ];
    }
}
