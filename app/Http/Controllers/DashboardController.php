<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\LoginLog;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller untuk menampilkan dashboard.
 *
 * Menangani statistik untuk admin dan guru.
 */
class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan statistik.
     *
     * @param  Request  $request  HTTP request
     * @return View Halaman dashboard
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $isGuru = $user->role === 'guru';

        // Resolve tahun ajaran context
        $sessionYearId = session('selected_tahun_ajaran_id');
        $sessionSemester = session('selected_semester');
        $fallbackYearId = TahunAjaran::where('is_active', true)->value('id');
        $selectedTahunAjaran = $sessionYearId ?: $fallbackYearId;
        $yearModel = $selectedTahunAjaran ? TahunAjaran::find($selectedTahunAjaran) : null;
        $selectedSemester = $sessionSemester ?? $yearModel?->semester;
        $tahunAjaranOptions = TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();

        $adminStats = null;
        $guruStats = null;
        $guruModel = null;
        $waliKelasNama = null;
        $penilaianFilled = 0;
        $targetPenilaian = 0;
        $recentLogins = collect();
        $penilaianStatus = collect();

        if ($isAdmin) {
            $adminStats = $this->buildAdminStats($selectedTahunAjaran);
            $penilaianStatus = $this->buildPenilaianStatus($selectedTahunAjaran, $selectedSemester);

            // Try to get login logs, but don't crash if table doesn't exist yet
            try {
                $recentLogins = LoginLog::with('user')
                    ->orderByDesc('logged_in_at')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                $recentLogins = collect();
            }
        }

        if ($isGuru) {
            $guruData = $this->buildGuruStats($user->id, $selectedTahunAjaran, $selectedSemester);
            $guruStats = $guruData['stats'];
            $guruModel = $guruData['guru'];
            $waliKelasNama = $guruData['wali_kelas_nama'];
            $penilaianFilled = $guruData['penilaian_filled'];
            $targetPenilaian = $guruData['target_penilaian'];

            // Wali kelas: build penilaian status for their class
            if ($guruModel && $waliKelasNama) {
                $waliKelasId = Kelas::where('guru_id', $guruModel->id)
                    ->when($selectedTahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran))
                    ->value('id');
                if ($waliKelasId) {
                    $penilaianStatus = $this->buildPenilaianStatus($selectedTahunAjaran, $selectedSemester, $waliKelasId);
                }
            }
        }

        return view('dashboard', compact(
            'isAdmin',
            'isGuru',
            'selectedTahunAjaran',
            'selectedSemester',
            'tahunAjaranOptions',
            'adminStats',
            'guruStats',
            'guruModel',
            'waliKelasNama',
            'penilaianFilled',
            'targetPenilaian',
            'recentLogins',
            'penilaianStatus',
        ));
    }

    /**
     * Build admin statistics.
     *
     * @param  int|null  $selectedTahunAjaran  Selected academic year ID
     * @return array<string, int> Admin statistics
     */
    private function buildAdminStats(?int $selectedTahunAjaran): array
    {
        return [
            'siswa' => Siswa::count(),
            'guru' => Guru::count(),
            'mapel' => MataPelajaran::count(),
            'rombel' => Kelas::when(
                $selectedTahunAjaran,
                fn ($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran),
            )->count(),
        ];
    }

    /**
     * Build guru statistics.
     *
     * @param  int       $userId            User ID
     * @param  int|null  $selectedTahunAjaran  Selected academic year ID
     * @param  string|null  $selectedSemester   Selected semester
     * @return array Guru statistics and related data
     */
    private function buildGuruStats(int $userId, ?int $selectedTahunAjaran, ?string $selectedSemester): array
    {
        $guruModel = Guru::where('user_id', $userId)->first();
        $waliKelasNama = null;
        $penilaianFilled = 0;
        $targetPenilaian = 0;
        $stats = null;

        if (! $guruModel) {
            return [
                'stats' => null,
                'guru' => null,
                'wali_kelas_nama' => null,
                'penilaian_filled' => 0,
                'target_penilaian' => 0,
            ];
        }

        $waliKelasNama = Kelas::where('guru_id', $guruModel->id)
            ->when($selectedTahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran))
            ->value('nama');

        $mengajarList = Mengajar::with('kelas.siswas')
            ->where('guru_id', $guruModel->id)
            ->when($selectedTahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran))
            ->when($selectedSemester, fn ($q) => $q->where('semester', $selectedSemester))
            ->get();

        $mapelDiampu = $mengajarList->pluck('mata_pelajaran_id')->unique()->count();

        $totalSiswaGuru = $mengajarList
            ->flatMap(fn ($m) => $m->kelas?->siswas ?? collect())
            ->pluck('id')
            ->unique()
            ->count();

        $targetPenilaian = $mengajarList->sum(fn ($m) => $m->kelas?->siswas?->count() ?? 0);

        $penilaianFilled = Penilaian::where('guru_id', $guruModel->id)
            ->when($selectedTahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran))
            ->when($selectedSemester, fn ($q) => $q->where('semester', $selectedSemester))
            ->whereNotNull('nilai_sumatif')
            ->whereNotNull('nilai_sts')
            ->count();

        $progress = $targetPenilaian > 0 ? round(min(100, ($penilaianFilled / $targetPenilaian) * 100)) : 0;

        $stats = [
            'mapel_diampu' => $mapelDiampu,
            'siswa' => $totalSiswaGuru,
            'penilaian_progress' => $progress,
        ];

        return [
            'stats' => $stats,
            'guru' => $guruModel,
            'wali_kelas_nama' => $waliKelasNama,
            'penilaian_filled' => $penilaianFilled,
            'target_penilaian' => $targetPenilaian,
        ];
    }

    /**
     * Build penilaian status per subject/teacher.
     *
     * @param  int|null  $tahunAjaranId  Academic year ID
     * @param  string|null  $semester  Semester
     * @param  int|null  $kelasId  Filter by specific class (for wali kelas)
     * @return \Illuminate\Support\Collection
     */
    private function buildPenilaianStatus(?int $tahunAjaranId, ?string $semester, ?int $kelasId = null)
    {
        if (! $tahunAjaranId) {
            return collect();
        }

        $mengajars = Mengajar::with(['mataPelajaran', 'guru', 'kelas.siswas'])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->whereNotNull('guru_id')
            ->get();

        if ($mengajars->isEmpty()) {
            return collect();
        }

        // Count filled penilaian per mengajar_id
        $filledCounts = Penilaian::where('tahun_ajaran_id', $tahunAjaranId)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->where(function ($q) {
                $q->whereNotNull('nilai_sumatif')->orWhereNotNull('nilai_sts');
            })
            ->select('mengajar_id', DB::raw('COUNT(*) as filled'))
            ->groupBy('mengajar_id')
            ->pluck('filled', 'mengajar_id');

        return $mengajars->map(function ($m) use ($filledCounts) {
            $totalSiswa = $m->kelas?->siswas?->count() ?? 0;
            $filled = $filledCounts->get($m->id, 0);
            $progress = $totalSiswa > 0 ? round(min(100, ($filled / $totalSiswa) * 100)) : 0;

            return (object) [
                'mapel' => $m->mataPelajaran?->nama_mapel ?? '-',
                'guru' => $m->guru?->nama ?? '-',
                'kelas' => $m->kelas?->nama ?? '-',
                'total_siswa' => $totalSiswa,
                'filled' => $filled,
                'progress' => $progress,
            ];
        })->sortBy('kelas')->values();
    }
}
