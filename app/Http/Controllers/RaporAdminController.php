<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\PrintSetting;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller untuk mengelola rapor dari sisi admin.
 *
 * Menangani tampilan daftar siswa dan ledger nilai per kelas.
 */
class RaporAdminController extends Controller
{
    /** @var string Role user untuk guru */
    private const USER_ROLE_GURU = 'guru';

    /** @var float Total bobot yang harus dicapai */
    private const TOTAL_BOBOT = 100.0;

    /** @var float Toleransi validasi bobot */
    private const BOBOT_TOLERANCE = 0.01;

    /** @var string Default tempat cetak */
    private const DEFAULT_PRINT_PLACE = 'Tangerang';

    /** @var string Default nama sekolah */
    private const DEFAULT_SCHOOL_NAME = 'MI Daarul Hikmah';

    /**
     * Menampilkan daftar siswa untuk rapor.
     *
     * @param  Request  $request  HTTP request
     * @return View Halaman index rapor
     */
    public function index(Request $request): View
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        $role = $request->user()->role ?? null;
        $guru = $this->getGuruForRole($role);

        $tingkat = $request->input('tingkat');
        $kelasId = $request->input('kelas_id');

        $kelasQuery = Kelas::query()->orderBy('nama');

        // Filter kelas berdasarkan tahun ajaran yang dipilih
        if ($tahunId) {
            $kelasQuery->where('tahun_ajaran_id', $tahunId);
        }

        if ($tingkat) {
            $kelasQuery->where('tingkat', $tingkat);
        }

        if ($guru) {
            $kelasQuery->where('guru_id', $guru->id);
            if (! $kelasId) {
                $kelasId = $kelasQuery->first()?->id;
            }
        }

        $kelasList = $kelasQuery->get();

        if ($role === self::USER_ROLE_GURU && ! $kelasId && $kelasList->isNotEmpty()) {
            $kelasId = $kelasList->first()->id;
        }

        $tingkatOptions = Kelas::query()
            ->select('tingkat')
            ->whereNotNull('tingkat')
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->distinct()
            ->orderBy('tingkat')
            ->pluck('tingkat');

        $siswas = $this->getSiswasByFilter($tingkat, $kelasId, $guru);

        return view('rapor.index', [
            'siswas' => $siswas,
            'kelasList' => $kelasList,
            'tingkatOptions' => $tingkatOptions,
            'tingkat' => $tingkat,
            'kelasId' => $kelasId,
            'tahunId' => $tahunId,
            'semester' => $semester,
            'role' => $role,
            'isGuru' => $role === self::USER_ROLE_GURU,
        ]);
    }

    /**
     * Menampilkan ledger nilai per kelas.
     *
     * @param  Request  $request  HTTP request
     * @param  Kelas    $kelas    Instance kelas dari route model binding
     * @return View Halaman ledger
     */
    public function ledger(Request $request, Kelas $kelas): View
    {
        $tahunId = $request->integer('tahun_ajaran_id') ?: session('selected_tahun_ajaran_id');
        $semester = $request->input('semester') ?: session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        $tahun = TahunAjaran::find($tahunId);

        $user = $request->user();
        $role = $user->role ?? null;
        $this->authorizeKelasAccess($role, $kelas, $user);

        $siswas = $kelas->siswas()->orderBy('nama')->get();
        $mapels = $this->getMapelsByKelas($kelas->id, $tahunId, $semester);
        $nilai = $this->calculateNilaiPerSiswa($siswas, $mapels, $tahunId, $semester);

        $school = SchoolProfile::first();
        $printSetting = PrintSetting::first();

        $printPlace = $printSetting?->tempat_cetak ?? $school?->city ?? self::DEFAULT_PRINT_PLACE;
        $printDate = $printSetting?->tanggal_cetak ?? now();
        $watermarkDataUrl = $this->generateWatermarkDataUrl($printSetting, $school);

        return view('rapor.ledger', [
            'kelas' => $kelas,
            'tahun' => $tahun,
            'semester' => $semester,
            'mapels' => $mapels,
            'nilai' => $nilai,
            'school' => $school,
            'printPlace' => $printPlace,
            'printDate' => $printDate,
            'watermarkDataUrl' => $watermarkDataUrl,
        ]);
    }

    /**
     * Mendapatkan instance guru jika role adalah guru.
     *
     * @param  string|null  $role  Role user
     * @return Guru|null Instance guru atau null
     */
    private function getGuruForRole(?string $role): ?Guru
    {
        if ($role !== self::USER_ROLE_GURU) {
            return null;
        }

        $guru = Guru::where('user_id', Auth::id())->first();

        if (! $guru) {
            abort(403, __('Akun Anda belum terhubung dengan data guru.'));
        }

        return $guru;
    }

    /**
     * Mendapatkan daftar siswa berdasarkan filter.
     *
     * @param  string|null  $tingkat  Filter tingkat
     * @param  int|null     $kelasId  Filter kelas ID
     * @param  Guru|null    $guru     Instance guru untuk filter wali kelas
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getSiswasByFilter(?string $tingkat, ?int $kelasId, ?Guru $guru)
    {
        return Siswa::with('kelas')
            ->when($tingkat, fn ($q) => $q->whereHas('kelas', fn ($qq) => $qq->where('tingkat', $tingkat)))
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->when($guru, function ($q) use ($guru) {
                $q->whereHas('kelas', function ($qq) use ($guru) {
                    $qq->where('guru_id', $guru->id);
                });
            })
            ->orderBy('nama')
            ->get();
    }

    /**
     * Memvalidasi akses guru ke kelas.
     *
     * @param  string|null  $role   Role user
     * @param  Kelas        $kelas  Instance kelas
     * @param  mixed        $user   Instance user
     * @return void
     */
    private function authorizeKelasAccess(?string $role, Kelas $kelas, $user): void
    {
        if ($role !== self::USER_ROLE_GURU) {
            return;
        }

        $guruId = optional(Guru::where('user_id', $user->id)->first())->id;

        if ($kelas->guru_id !== $guruId) {
            abort(403, __('Anda tidak memiliki akses ke kelas ini.'));
        }
    }

    /**
     * Mendapatkan daftar mata pelajaran per kelas.
     *
     * @param  int          $kelasId   ID kelas
     * @param  int          $tahunId   ID tahun ajaran
     * @param  string|null  $semester  Semester
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getMapelsByKelas(int $kelasId, int $tahunId, ?string $semester)
    {
        return Mengajar::with(['mataPelajaran', 'guru.user'])
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahunId)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->orderBy('mata_pelajaran_id')
            ->get();
    }

    /**
     * Menghitung nilai rapor per siswa.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $siswas    Koleksi siswa
     * @param  \Illuminate\Database\Eloquent\Collection  $mapels    Koleksi mengajar
     * @param  int                                       $tahunId   ID tahun ajaran
     * @param  string|null                               $semester  Semester
     * @return array Array nilai per siswa
     */
    private function calculateNilaiPerSiswa($siswas, $mapels, int $tahunId, ?string $semester): array
    {
        $nilai = [];

        foreach ($siswas as $siswa) {
            $row = ['siswa' => $siswa, 'mapels' => [], 'total' => 0, 'count' => 0];

            foreach ($mapels as $mengajar) {
                $penilaian = Penilaian::where('mengajar_id', $mengajar->id)
                    ->where('siswa_id', $siswa->id)
                    ->where('tahun_ajaran_id', $tahunId)
                    ->when($semester, fn ($q) => $q->where('semester', $semester))
                    ->first();

                $rapor = $this->calculateRaporValue($penilaian, $mengajar);

                $row['mapels'][$mengajar->mata_pelajaran_id] = $rapor;

                if ($rapor !== null) {
                    $row['total'] += $rapor;
                    $row['count'] += 1;
                }
            }

            $nilai[] = $row;
        }

        return $nilai;
    }

    /**
     * Menghitung nilai rapor dari penilaian.
     *
     * @param  Penilaian|null  $penilaian  Instance penilaian
     * @param  Mengajar        $mengajar   Instance mengajar
     * @return int|null Nilai rapor atau null
     */
    private function calculateRaporValue(?Penilaian $penilaian, Mengajar $mengajar): ?int
    {
        if (! $penilaian || $penilaian->nilai_sumatif === null || $penilaian->nilai_sts === null) {
            return null;
        }

        $guruUser = $mengajar->guru?->user;
        $bobotSumatif = $guruUser?->bobot_sumatif ?? (float) config('rapor.bobot_sumatif', 50);
        $bobotSts = $guruUser?->bobot_sts ?? (float) config('rapor.bobot_sts', 50);

        if (abs(($bobotSumatif + $bobotSts) - self::TOTAL_BOBOT) > self::BOBOT_TOLERANCE) {
            return null;
        }

        return (int) round((($penilaian->nilai_sumatif * $bobotSumatif) + ($penilaian->nilai_sts * $bobotSts)) / self::TOTAL_BOBOT);
    }

    /**
     * Membuat data URL untuk watermark SVG.
     *
     * @param  PrintSetting|null   $printSetting  Instance print setting
     * @param  SchoolProfile|null  $school        Instance school profile
     * @return string|null Data URL SVG atau null
     */
    private function generateWatermarkDataUrl(?PrintSetting $printSetting, ?SchoolProfile $school): ?string
    {
        $watermarkText = null;

        if ($printSetting) {
            $watermarkText = trim((string) $printSetting->watermark);
            if ($watermarkText === '') {
                $watermarkText = null;
            }
        } else {
            $watermarkText = $school?->name ?: self::DEFAULT_SCHOOL_NAME;
        }

        if (! $watermarkText) {
            return null;
        }

        $svg = sprintf(
            "<svg xmlns='http://www.w3.org/2000/svg' width='150' height='100' viewBox='0 0 150 100'><text x='0' y='30' fill='#2e6b3a' font-size='14' font-family='Times New Roman,serif' transform='rotate(30 0 30)'>%s</text></svg>",
            $watermarkText,
        );

        return 'data:image/svg+xml,' . rawurlencode($svg);
    }
}
