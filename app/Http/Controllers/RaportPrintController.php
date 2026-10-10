<?php

namespace App\Http\Controllers;

use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\PrintSetting;
use App\Models\RaporMetadata;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
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
     * @param  Siswa  $siswa  Instance siswa dari route model binding
     * @return View Halaman cetak rapor
     */
    public function show(Request $request, Siswa $siswa): View|\Illuminate\Http\RedirectResponse
    {
        $tahunId = $request->integer('tahun_ajaran_id') ?: session('selected_tahun_ajaran_id');
        $semester = $request->input('semester') ?: session('selected_semester');

        $this->validateContext($tahunId, $semester);
        $this->authorizeAccess($request->user(), $siswa, $tahunId);

        $kelas = $siswa->kelas;

        // Validate student has a class assigned
        if (! $kelas) {
            return redirect()->back()->with('error', __('Siswa belum ditempatkan di kelas. Silakan tetapkan kelas terlebih dahulu.'));
        }

        $wali = $kelas->guru;

        $school = SchoolProfile::first();
        $printSetting = PrintSetting::first();
        $tahun = TahunAjaran::find($tahunId);

        $gradeService = new GradeDescriptorService;
        $nilai = $this->buildNilaiCollection($siswa->id, $tahunId, $semester, $gradeService);

        $ekskul = EkskulPenilaian::with('ekskul')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->get();

        $meta = $this->getOrCreateMetadata($tahunId, $semester, $siswa->id, $kelas->id, $wali?->id);
        $prestasi = collect($meta->prestasi ?? [])->values()->take(3);

        $printPlace = $this->resolvePrintPlace($printSetting, $school);
        $raporDate = $this->resolveRaporDate($printSetting, $meta);
        $watermarkDataUrl = $this->buildWatermarkDataUrl($printSetting, $school);
        $namaYayasan = $printSetting?->nama_yayasan;

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
            'namaYayasan',
        ));
    }

    /**
     * Menampilkan halaman cetak rapor seluruh siswa aktif dalam satu kelas.
     *
     * Konteks tahun ajaran & semester diambil dari kelas itu sendiri (bukan dari
     * query string). Metadata rapor hanya dibaca, tidak dibuat, agar pratinjau
     * tidak meninggalkan baris RaporMetadata.
     *
     * @param  Request  $request  HTTP request
     * @param  Kelas  $kelas  Instance kelas dari route model binding
     * @return View Halaman cetak rapor satu kelas
     */
    public function kelas(Request $request, Kelas $kelas): View
    {
        $this->authorizeKelasAccess($request->user(), $kelas);

        $tahun = $kelas->tahun_ajaran_id ? TahunAjaran::find($kelas->tahun_ajaran_id) : null;
        // Satu baris TahunAjaran = satu semester; sesi hanya cadangan bila kolomnya kosong
        $semester = $tahun?->semester ?: session('selected_semester');

        if (! $tahun || ! $semester) {
            abort(422, __('Kelas ini belum terhubung dengan tahun ajaran dan semester.'));
        }

        $tahunId = $tahun->id;
        $semester = (string) $semester;

        $siswas = $kelas->siswas()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();
        $siswaIds = $siswas->pluck('id');

        $wali = $kelas->guru;
        $school = SchoolProfile::first();
        $printSetting = PrintSetting::first();

        $metaPerSiswa = RaporMetadata::query()
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->keyBy('siswa_id');

        $ekskulPerSiswa = EkskulPenilaian::with('ekskul')
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds)
            ->get()
            ->groupBy('siswa_id');

        $gradeService = new GradeDescriptorService;

        $lembars = $siswas->map(fn (Siswa $siswa): array => $this->buildLembar(
            $siswa,
            $tahunId,
            $semester,
            $metaPerSiswa->get($siswa->id) ?? $this->makeDefaultMetadata($tahunId, $semester, $siswa->id, $kelas->id, $wali?->id),
            $ekskulPerSiswa->get($siswa->id, collect()),
            $printSetting,
            $gradeService,
        ));

        return view('rapor.print-kelas', [
            'school' => $school,
            'kelas' => $kelas,
            'wali' => $wali,
            'tahun' => $tahun,
            'semester' => $semester,
            'lembars' => $lembars,
            'printPlace' => $this->resolvePrintPlace($printSetting, $school),
            'watermarkDataUrl' => $this->buildWatermarkDataUrl($printSetting, $school),
            'namaYayasan' => $printSetting?->nama_yayasan,
        ]);
    }

    /**
     * Data satu siswa untuk partial rapor.partials.lembar (sama seperti yang disiapkan show()).
     *
     * @param  \Illuminate\Support\Collection<int, EkskulPenilaian>  $ekskul
     * @return array<string, mixed>
     */
    private function buildLembar(
        Siswa $siswa,
        int $tahunId,
        string $semester,
        RaporMetadata $meta,
        \Illuminate\Support\Collection $ekskul,
        ?PrintSetting $printSetting,
        GradeDescriptorService $gradeService
    ): array {
        return [
            'siswa' => $siswa,
            'nilai' => $this->buildNilaiCollection($siswa->id, $tahunId, $semester, $gradeService),
            'ekskul' => $ekskul,
            'meta' => $meta,
            'prestasi' => collect($meta->prestasi ?? [])->values()->take(3),
            'raporDate' => $this->resolveRaporDate($printSetting, $meta),
        ];
    }

    /**
     * Otorisasi cetak rapor satu kelas: admin semua kelas, guru hanya kelas yang ia walikan.
     */
    private function authorizeKelasAccess(User $user, Kelas $kelas): void
    {
        $roleSlug = strtolower((string) ($user->role ?? ''));

        if ($roleSlug === 'admin') {
            return;
        }

        if ($roleSlug !== 'guru') {
            abort(403, __('Anda tidak memiliki akses ke kelas ini.'));
        }

        $guru = Guru::where('user_id', $user->id)->first();

        // Guru mapel yang bukan wali kelas tidak boleh mencetak rapor satu kelas
        if (! $guru || ! $kelas->guru_id || (int) $kelas->guru_id !== (int) $guru->id) {
            abort(403, __('Anda tidak memiliki akses ke kelas ini.'));
        }
    }

    /**
     * Metadata default di memori (tidak disimpan) untuk siswa yang belum punya metadata rapor.
     */
    private function makeDefaultMetadata(int $tahunId, string $semester, int $siswaId, ?int $kelasId, ?int $waliId): RaporMetadata
    {
        return new RaporMetadata([
            'tahun_ajaran_id' => $tahunId,
            'semester' => $semester,
            'siswa_id' => $siswaId,
            'kelas_id' => $kelasId,
            'wali_guru_id' => $waliId,
            'tanggal_rapor' => now(),
            'prestasi' => [],
        ]);
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
    private function authorizeAccess(User $user, Siswa $siswa, int $tahunId): void
    {
        $roleSlug = strtolower((string) ($user->role ?? ''));
        $isAdmin = $roleSlug === 'admin';

        if ($isAdmin) {
            return;
        }

        $isGuru = $roleSlug === 'guru';
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
     *
     * @return \Illuminate\Support\Collection<int, array{mapel: \App\Models\MataPelajaran|null, sumatif: float|null, sts: float|null, rapor: float|null, deskripsi: string, descriptor: array{predikat: string, keterangan: string, kalimat: string}|null, kelompok: string|null, urutan: string|null}>
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
                    'deskripsi' => $this->buildFallbackDeskripsi($n->materi_tp),
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
     * Teks fallback untuk kolom Capaian Kompetensi ketika deskriptor belum bisa
     * dihitung (nilai sumatif/STS belum lengkap).
     */
    private function buildFallbackDeskripsi(?string $materiTp): string
    {
        $materi = trim((string) $materiTp);

        if ($materi === '') {
            return __('Nilai belum lengkap.');
        }

        return __('Nilai belum lengkap untuk materi: :materi', ['materi' => $materi]);
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

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }
}
