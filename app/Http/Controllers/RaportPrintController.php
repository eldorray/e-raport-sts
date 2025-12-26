<?php

namespace App\Http\Controllers;

use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\PrintSetting;
use App\Models\RaporMetadata;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\GradeDescriptorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mencetak rapor siswa.
 *
 * Menangani tampilan cetak rapor dengan nilai, deskripsi, dan metadata.
 */
class RaportPrintController extends Controller
{
    /**
     * Menampilkan halaman cetak rapor siswa.
     *
     * @param  Request  $request  HTTP request
     * @param  Siswa    $siswa    Instance siswa dari route model binding
     * @return View Halaman cetak rapor
     */
    public function show(Request $request, Siswa $siswa): View
    {
        $tahunId = $request->integer('tahun_ajaran_id') ?: session('selected_tahun_ajaran_id');
        $semester = $request->input('semester') ?: session('selected_semester');

        $this->validateContext($tahunId, $semester);
        $this->authorizeAccess($request->user(), $siswa, $tahunId);

        $kelas = $siswa->kelas;
        $wali = $kelas?->guru;

        $school = SchoolProfile::first();
        $printSetting = PrintSetting::first();
        $tahun = TahunAjaran::find($tahunId);

        $gradeService = new GradeDescriptorService();
        $nilai = $this->buildNilaiCollection($siswa->id, $tahunId, $semester, $gradeService);

        $ekskul = EkskulPenilaian::with('ekskul')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->get();

        $meta = $this->getOrCreateMetadata($tahunId, $semester, $siswa->id, $kelas?->id, $wali?->id);
        $prestasi = collect($meta->prestasi ?? [])->values()->take(3);

        $printPlace = $this->resolvePrintPlace($printSetting, $school);
        $raporDate = $this->resolveRaporDate($printSetting, $meta);
        $watermarkDataUrl = $this->buildWatermarkDataUrl($printSetting, $school);

        return view('rapor.print', compact(
            'school',
            'siswa',
            'kelas',
            'wali',
            'tahunId',
            'semester',
            'tahun',
            'nilai',
            'ekskul',
            'meta',
            'prestasi',
            'printPlace',
            'raporDate',
            'watermarkDataUrl',
        ));
    }

    /**
     * Validasi konteks tahun ajaran dan semester.
     */
    private function validateContext(?int $tahunId, ?string $semester): void
    {
        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }
    }

    /**
     * Otorisasi akses ke rapor siswa.
     */
    private function authorizeAccess($user, Siswa $siswa, int $tahunId): void
    {
        $roleSlug = strtolower($user->role ?? '');
        $canCheckRole = method_exists($user, 'hasRole');
        $isAdmin = ($canCheckRole && $user->hasRole('admin')) || $roleSlug === 'admin';

        if ($isAdmin) {
            return;
        }

        $isGuru = ($canCheckRole && $user->hasRole('guru')) || $roleSlug === 'guru';
        if (! $isGuru) {
            abort(403, __('Anda tidak memiliki akses ke rapor siswa ini.'));
        }

        $guru = Guru::where('user_id', $user->id)->first();
        if (! $guru) {
            abort(403, __('Akun Anda belum terhubung dengan data guru.'));
        }

        $kelas = $siswa->kelas;
        $wali = $kelas?->guru;

        $isWali = $wali && $wali->id === $guru->id;
        $isMengajar = Mengajar::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->when($kelas?->id, fn ($q) => $q->where('kelas_id', $kelas->id))
            ->exists();

        if (! $isWali && ! $isMengajar) {
            abort(403, __('Anda tidak memiliki akses ke rapor siswa ini.'));
        }
    }

    /**
     * Build koleksi nilai dengan deskriptor menggunakan GradeDescriptorService.
     */
    private function buildNilaiCollection(
        int $siswaId,
        int $tahunId,
        string $semester,
        GradeDescriptorService $gradeService
    ): \Illuminate\Support\Collection {
        return Penilaian::with(['mataPelajaran', 'guru.user'])
            ->where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->get()
            ->map(function ($n) use ($gradeService) {
                $guruUser = $n->guru?->user;
                $bobotSumatif = $guruUser?->bobot_sumatif;
                $bobotSts = $guruUser?->bobot_sts;

                $result = $gradeService->calculateWithDescriptor(
                    $n->nilai_sumatif,
                    $n->nilai_sts,
                    $n->materi_tp,
                    $bobotSumatif,
                    $bobotSts,
                );

                return [
                    'mapel' => $n->mataPelajaran,
                    'sumatif' => $n->nilai_sumatif,
                    'sts' => $n->nilai_sts,
                    'rapor' => $result['rapor'],
                    'deskripsi' => $n->materi_tp,
                    'descriptor' => $result['descriptor'],
                    'kelompok' => $n->mataPelajaran?->kelompok,
                    'urutan' => $n->mataPelajaran?->urutan,
                ];
            })
            ->sortBy(fn ($row) => sprintf(
                '%s-%03d-%s',
                $row['kelompok'] ?? 'Z',
                $row['urutan'] ?? 999,
                $row['mapel']?->nama_mapel
            ))
            ->values();
    }

    /**
     * Get or create rapor metadata.
     */
    private function getOrCreateMetadata(
        int $tahunId,
        string $semester,
        int $siswaId,
        ?int $kelasId,
        ?int $waliId
    ): RaporMetadata {
        return RaporMetadata::firstOrCreate([
            'tahun_ajaran_id' => $tahunId,
            'semester' => $semester,
            'siswa_id' => $siswaId,
        ], [
            'kelas_id' => $kelasId,
            'wali_guru_id' => $waliId,
            'tanggal_rapor' => now(),
            'prestasi' => [],
        ]);
    }

    /**
     * Resolve print place from settings or school profile.
     */
    private function resolvePrintPlace(?PrintSetting $printSetting, ?SchoolProfile $school): string
    {
        return $printSetting?->tempat_cetak ?? $school?->city ?? 'Tangerang';
    }

    /**
     * Resolve rapor date from settings or metadata.
     */
    private function resolveRaporDate(?PrintSetting $printSetting, RaporMetadata $meta): \Carbon\Carbon
    {
        return $printSetting?->tanggal_cetak_rapor ?? $meta->tanggal_rapor ?? now();
    }

    /**
     * Build watermark SVG data URL.
     */
    private function buildWatermarkDataUrl(?PrintSetting $printSetting, ?SchoolProfile $school): ?string
    {
        $watermarkText = null;

        if ($printSetting) {
            $watermarkText = trim((string) $printSetting->watermark);
            if ($watermarkText === '') {
                $watermarkText = null;
            }
        } else {
            $watermarkText = $school?->name ?: 'MI Daarul Hikmah';
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
