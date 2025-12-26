<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /** @var float Nilai minimum untuk penilaian */
    private const MIN_NILAI = 0;

    /** @var float Nilai maksimum untuk penilaian */
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
     * @param  Request   $request   HTTP request
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

        $nilaiBySiswa = Penilaian::where('mengajar_id', $mengajar->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $bobotSumatif = $user?->bobot_sumatif ?? $this->getDefaultBobotSumatif();
        $bobotSts = $user?->bobot_sts ?? $this->getDefaultBobotSts();

        return view('guru.penilaian.show', [
            'mengajar' => $mengajar,
            'siswas' => $siswas,
            'nilaiBySiswa' => $nilaiBySiswa,
            'tahunId' => $tahunId,
            'semester' => $semester,
            'bobotSumatif' => $bobotSumatif,
            'bobotSts' => $bobotSts,
        ]);
    }

    /**
     * Menyimpan nilai siswa.
     *
     * @param  Request   $request   HTTP request dengan data nilai
     * @param  Mengajar  $mengajar  Instance mengajar dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya
     */
    public function store(Request $request, Mengajar $mengajar): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $tahunId || ! $semester) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran & semester terlebih dahulu.')]);
        }

        $this->authorizeGuruAccess($guru, $mengajar);

        $validated = $request->validate([
            'nilai_sumatif' => ['sometimes', 'array'],
            'nilai_sumatif.*' => ['nullable', 'numeric', 'min:' . self::MIN_NILAI, 'max:' . self::MAX_NILAI],
            'nilai_sts' => ['sometimes', 'array'],
            'nilai_sts.*' => ['nullable', 'numeric', 'min:' . self::MIN_NILAI, 'max:' . self::MAX_NILAI],
            'materi_tp' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $bobotSumatif = $user?->bobot_sumatif ?? $this->getDefaultBobotSumatif();
        $bobotSts = $user?->bobot_sts ?? $this->getDefaultBobotSts();

        if (! $this->isValidTotalBobot($bobotSumatif, $bobotSts)) {
            return back()
                ->withErrors(['bobot_sumatif' => __('Total bobot harus 100%.')])
                ->withInput();
        }

        $siswas = Siswa::where('kelas_id', $mengajar->kelas_id)->pluck('id');

        DB::transaction(function () use ($validated, $mengajar, $guru, $tahunId, $semester, $siswas, $bobotSumatif, $bobotSts) {
            $mengajar->bobot_sumatif = $bobotSumatif;
            $mengajar->bobot_sts = $bobotSts;
            $mengajar->save();

            $this->saveNilaiSiswa($validated, $mengajar, $guru, $tahunId, $semester, $siswas);
        });

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
            'bobotSumatif' => $user?->bobot_sumatif ?? $this->getDefaultBobotSumatif(),
            'bobotSts' => $user?->bobot_sts ?? $this->getDefaultBobotSts(),
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
            'bobot_sumatif' => ['required', 'numeric', 'min:' . self::MIN_NILAI, 'max:' . self::MAX_NILAI],
            'bobot_sts' => ['required', 'numeric', 'min:' . self::MIN_NILAI, 'max:' . self::MAX_NILAI],
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
     * @param  float  $bobotSts      Bobot STS
     * @return bool True jika valid
     */
    private function isValidTotalBobot(float $bobotSumatif, float $bobotSts): bool
    {
        return abs(($bobotSumatif + $bobotSts) - self::TOTAL_BOBOT) <= self::BOBOT_TOLERANCE;
    }

    /**
     * Memvalidasi akses guru ke mengajar.
     *
     * @param  Guru|null  $guru     Instance guru
     * @param  Mengajar   $mengajar Instance mengajar
     * @return void
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
     * @param  Mengajar  $mengajar Instance mengajar
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function validateTahunAjaran(?int $tahunId, Mengajar $mengajar): void
    {
        if (! $tahunId || $mengajar->tahun_ajaran_id !== $tahunId) {
            abort(404, __('Penilaian tidak tersedia untuk tahun ajaran ini.'));
        }
    }

    /**
     * Menyimpan nilai semua siswa.
     *
     * @param  array                            $validated  Data nilai yang sudah divalidasi
     * @param  Mengajar                         $mengajar   Instance mengajar
     * @param  Guru                             $guru       Instance guru
     * @param  int                              $tahunId    ID tahun ajaran
     * @param  string                           $semester   Semester
     * @param  \Illuminate\Support\Collection   $siswas     Koleksi ID siswa
     * @return void
     */
    private function saveNilaiSiswa(
        array $validated,
        Mengajar $mengajar,
        Guru $guru,
        int $tahunId,
        string $semester,
        $siswas
    ): void {
        $sumatifPayload = $validated['nilai_sumatif'] ?? [];
        $stsPayload = $validated['nilai_sts'] ?? [];
        $materiTp = isset($validated['materi_tp']) ? trim($validated['materi_tp']) : null;

        $allKeys = collect(array_keys($sumatifPayload) + array_keys($stsPayload))->unique();

        foreach ($allKeys as $siswaId) {
            if (! $siswas->contains((int) $siswaId)) {
                continue;
            }

            $record = Penilaian::firstOrNew([
                'tahun_ajaran_id' => $tahunId,
                'semester' => $semester,
                'kelas_id' => $mengajar->kelas_id,
                'siswa_id' => $siswaId,
                'mata_pelajaran_id' => $mengajar->mata_pelajaran_id,
                'guru_id' => $guru->id,
                'mengajar_id' => $mengajar->id,
            ]);

            $record->materi_tp = $materiTp !== '' ? $materiTp : null;

            if (array_key_exists($siswaId, $sumatifPayload)) {
                $record->nilai_sumatif = $sumatifPayload[$siswaId] !== null ? (float) $sumatifPayload[$siswaId] : null;
            }

            if (array_key_exists($siswaId, $stsPayload)) {
                $record->nilai_sts = $stsPayload[$siswaId] !== null ? (float) $stsPayload[$siswaId] : null;
            }

            $record->save();
        }
    }

    /**
     * Reset semua nilai untuk satu mengajar.
     *
     * @param  Request   $request   HTTP request
     * @param  Mengajar  $mengajar  Instance mengajar
     * @return RedirectResponse
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

        // Hapus semua penilaian untuk mengajar ini pada semester/tahun yang dipilih
        Penilaian::where('mengajar_id', $mengajar->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->delete();

        return back()->with('status', __('Nilai berhasil direset. Silakan isi ulang.'));
    }
}
