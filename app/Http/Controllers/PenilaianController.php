<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\GradeDescriptorService;
use App\Services\PenyimpananNilaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mengelola penilaian siswa.
 *
 * Menangani input nilai sumatif dan STS oleh guru.
 */
class PenilaianController extends Controller
{
    /** @var float Total bobot penilaian yang harus dicapai */
    private const TOTAL_BOBOT = 100.0;

    /** @var float Toleransi untuk validasi total bobot */
    private const BOBOT_TOLERANCE = 0.01;

    /** @var int Nilai minimum untuk penilaian */
    private const MIN_NILAI = 0;

    /** @var int Nilai maksimum untuk penilaian */
    private const MAX_NILAI = 100;

    /**
     * Menampilkan daftar mata pelajaran yang diajar oleh guru.
     *
     * @param  Request  $request  HTTP request
     * @return View Halaman index penilaian
     */
    public function index(Request $request): View
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        $groupedAssignments = collect();

        if ($guru && $tahunId) {
            $groupedAssignments = Mengajar::with(['kelas', 'mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunId)
                ->when($semester, fn ($q) => $q->where('semester', $semester))
                ->orderBy('mata_pelajaran_id')
                ->orderBy('kelas_id')
                ->get()
                ->groupBy('mata_pelajaran_id');
        }

        return view('guru.penilaian.index', [
            'groupedAssignments' => $groupedAssignments,
            'tahunId' => $tahunId,
            'semester' => $semester,
            'guru' => $guru,
        ]);
    }

    /**
     * Menampilkan form input nilai untuk satu mengajar.
     *
     * @param  Request  $request  HTTP request
     * @param  Mengajar  $mengajar  Instance mengajar dari route model binding
     * @return View Halaman form penilaian
     */
    public function show(Request $request, Mengajar $mengajar): View
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');
        $guru = Guru::where('user_id', $request->user()->id)->first();
        $user = $request->user();

        $this->authorizeGuruAccess($guru, $mengajar);
        $this->validateTahunAjaran($tahunId, $mengajar);

        $siswas = Siswa::where('kelas_id', $mengajar->kelas_id)
            ->orderBy('nama')
            ->get();

        $nilaiBySiswa = Penilaian::with('pengoreksi:id,name')
            ->where('mengajar_id', $mengajar->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $bobotSumatif = $user->bobot_sumatif ?? $this->getDefaultBobotSumatif();
        $bobotSts = $user->bobot_sts ?? $this->getDefaultBobotSts();

        // Check if tahun ajaran is active (guru can only edit on active tahun ajaran)
        /** @var TahunAjaran|null $tahunAjaran */
        $tahunAjaran = TahunAjaran::find($tahunId);
        $canEdit = $tahunAjaran && $tahunAjaran->is_active;

        return view('guru.penilaian.show', [
            'mengajar' => $mengajar,
            'siswas' => $siswas,
            'nilaiBySiswa' => $nilaiBySiswa,
            'tahunId' => $tahunId,
            'semester' => $semester,
            'bobotSumatif' => $bobotSumatif,
            'bobotSts' => $bobotSts,
            'canEdit' => $canEdit,
            'gradeService' => new GradeDescriptorService,
        ]);
    }

    /**
     * Menyimpan nilai siswa.
     *
     * Penulisan baris nilai diserahkan ke PenyimpananNilaiService (jalur yang
     * sama dengan koreksi admin); baris yang diubah guru kehilangan jejak
     * koreksi admin karena perubahan terakhirnya milik guru.
     *
     * @param  Request  $request  HTTP request dengan data nilai
     * @param  Mengajar  $mengajar  Instance mengajar dari route model binding
     * @param  PenyimpananNilaiService  $penyimpanan  Service penyimpanan nilai
     * @return RedirectResponse Redirect ke halaman sebelumnya
     */
    public function store(Request $request, Mengajar $mengajar, PenyimpananNilaiService $penyimpanan): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $tahunId || ! $semester) {
            return back()
                ->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran & semester terlebih dahulu.')])
                ->withInput();
        }

        $this->authorizeGuruAccess($guru, $mengajar);

        // Tolak bila tahun ajaran/semester sudah diganti di tab lain
        if ($this->isKonteksBerubah($request, $mengajar, $tahunId, $semester)) {
            return back()
                ->withErrors(['tahun_ajaran' => __('Tahun ajaran atau semester sudah diganti di tab lain. Muat ulang halaman ini, lalu simpan lagi.')])
                ->withInput();
        }

        // Block guru from editing inactive tahun ajaran
        /** @var TahunAjaran|null $tahunAjaran */
        $tahunAjaran = TahunAjaran::find($tahunId);
        if (! $tahunAjaran || ! $tahunAjaran->is_active) {
            return back()
                ->withErrors(['tahun_ajaran' => __('Tidak dapat menyimpan data pada tahun ajaran yang tidak aktif.')])
                ->withInput();
        }

        $validated = $request->validate($penyimpanan->aturanValidasi());

        $user = $request->user();
        $bobotSumatif = $user->bobot_sumatif ?? $this->getDefaultBobotSumatif();
        $bobotSts = $user->bobot_sts ?? $this->getDefaultBobotSts();

        if (! $this->isValidTotalBobot($bobotSumatif, $bobotSts)) {
            return back()
                ->withErrors(['bobot_sumatif' => __('Total bobot harus 100%.')])
                ->withInput();
        }

        // authorizeGuruAccess() menjamin $mengajar->guru_id milik guru ini,
        // jadi baris ditulis dengan guru_id yang sama seperti sebelumnya.
        $penyimpanan->simpan($mengajar, (int) $tahunId, (string) $semester, $validated, $bobotSumatif, $bobotSts);

        return back()->with('status', __('Nilai disimpan.'));
    }

    /**
     * Menampilkan form edit bobot penilaian.
     *
     * @param  Request  $request  HTTP request
     * @return View Halaman form bobot
     */
    public function editBobot(Request $request): View
    {
        $user = $request->user();

        return view('penilaian.bobot', [
            'bobotSumatif' => $user->bobot_sumatif ?? $this->getDefaultBobotSumatif(),
            'bobotSts' => $user->bobot_sts ?? $this->getDefaultBobotSts(),
            'user' => $user,
        ]);
    }

    /**
     * Memperbarui bobot penilaian user.
     *
     * @param  Request  $request  HTTP request dengan data bobot
     * @return RedirectResponse Redirect ke halaman sebelumnya
     */
    public function updateBobot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bobot_sumatif' => ['required', 'numeric', 'min:'.self::MIN_NILAI, 'max:'.self::MAX_NILAI],
            'bobot_sts' => ['required', 'numeric', 'min:'.self::MIN_NILAI, 'max:'.self::MAX_NILAI],
        ]);

        if (! $this->isValidTotalBobot($data['bobot_sumatif'], $data['bobot_sts'])) {
            return back()->withErrors(['bobot_sumatif' => __('Total bobot harus 100%.')])->withInput();
        }

        $request->user()->update([
            'bobot_sumatif' => $data['bobot_sumatif'],
            'bobot_sts' => $data['bobot_sts'],
        ]);

        return back()->with('status', __('Bobot penilaian diperbarui.'));
    }

    /**
     * Mendapatkan default bobot sumatif dari config.
     *
     * @return float Default bobot sumatif
     */
    private function getDefaultBobotSumatif(): float
    {
        return (float) config('rapor.bobot_sumatif', 50);
    }

    /**
     * Mendapatkan default bobot STS dari config.
     *
     * @return float Default bobot STS
     */
    private function getDefaultBobotSts(): float
    {
        return (float) config('rapor.bobot_sts', 50);
    }

    /**
     * Memvalidasi apakah total bobot sudah sesuai.
     *
     * @param  float  $bobotSumatif  Bobot sumatif
     * @param  float  $bobotSts  Bobot STS
     * @return bool True jika valid
     */
    private function isValidTotalBobot(float $bobotSumatif, float $bobotSts): bool
    {
        return abs(($bobotSumatif + $bobotSts) - self::TOTAL_BOBOT) <= self::BOBOT_TOLERANCE;
    }

    /**
     * Memvalidasi akses guru ke mengajar.
     *
     * @param  Guru|null  $guru  Instance guru
     * @param  Mengajar  $mengajar  Instance mengajar
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function authorizeGuruAccess(?Guru $guru, Mengajar $mengajar): void
    {
        if (! $guru || $mengajar->guru_id !== $guru->id) {
            abort(403, __('Anda tidak memiliki akses ke penilaian ini.'));
        }
    }

    /**
     * Memvalidasi tahun ajaran untuk mengajar.
     *
     * @param  int|null  $tahunId  ID tahun ajaran dari session
     * @param  Mengajar  $mengajar  Instance mengajar
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function validateTahunAjaran(?int $tahunId, Mengajar $mengajar): void
    {
        if (! $tahunId || $mengajar->tahun_ajaran_id !== $tahunId) {
            abort(404, __('Penilaian tidak tersedia untuk tahun ajaran ini.'));
        }
    }

    /**
     * Cek apakah konteks simpan tidak cocok dengan tahun ajaran/semester di session.
     *
     * Terjadi bila tahun ajaran/semester diganti di tab lain setelah form dibuka:
     * jadwal mengajar milik tahun/semester lain, atau field tersembunyi
     * `tahun_ajaran_id`/`semester` (opsional) dari form berbeda dengan session.
     *
     * @param  Request  $request  HTTP request
     * @param  Mengajar  $mengajar  Instance mengajar
     * @param  int  $tahunId  ID tahun ajaran dari session
     * @param  string  $semester  Semester dari session
     * @return bool True jika konteks berubah dan penyimpanan harus ditolak
     */
    private function isKonteksBerubah(Request $request, Mengajar $mengajar, int $tahunId, string $semester): bool
    {
        if ((int) $mengajar->tahun_ajaran_id !== $tahunId) {
            return true;
        }

        // Jadwal lama tanpa semester berlaku untuk semester mana pun
        $semesterMengajar = (string) $mengajar->semester;
        if ($semesterMengajar !== '' && ! $this->isSemesterSama($semesterMengajar, $semester)) {
            return true;
        }

        if ($request->filled('tahun_ajaran_id') && (int) $request->input('tahun_ajaran_id') !== $tahunId) {
            return true;
        }

        return $request->filled('semester') && ! $this->isSemesterSama((string) $request->input('semester'), $semester);
    }

    /**
     * Bandingkan dua nilai semester tanpa membedakan huruf besar/kecil (mengikuti collation database).
     */
    private function isSemesterSama(string $a, string $b): bool
    {
        return strcasecmp(trim($a), trim($b)) === 0;
    }

    /**
     * Reset semua nilai untuk satu mengajar.
     *
     * @param  Request  $request  HTTP request
     * @param  Mengajar  $mengajar  Instance mengajar
     */
    public function reset(Request $request, Mengajar $mengajar): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $tahunId || ! $semester) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran & semester terlebih dahulu.')]);
        }

        $this->authorizeGuruAccess($guru, $mengajar);

        // Tolak bila tahun ajaran/semester sudah diganti di tab lain
        if ($this->isKonteksBerubah($request, $mengajar, $tahunId, $semester)) {
            return back()->withErrors(['tahun_ajaran' => __('Tahun ajaran atau semester sudah diganti di tab lain. Muat ulang halaman ini, lalu simpan lagi.')]);
        }

        // Block guru from resetting inactive tahun ajaran
        /** @var TahunAjaran|null $tahunAjaran */
        $tahunAjaran = TahunAjaran::find($tahunId);
        if (! $tahunAjaran || ! $tahunAjaran->is_active) {
            return back()->withErrors(['tahun_ajaran' => __('Tidak dapat mereset data pada tahun ajaran yang tidak aktif.')]);
        }

        // Hapus semua penilaian untuk mengajar ini pada semester/tahun yang dipilih
        Penilaian::where('mengajar_id', $mengajar->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->delete();

        return back()->with('status', __('Nilai berhasil direset. Silakan isi ulang.'));
    }
}
