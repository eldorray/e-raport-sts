<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Aplikasi guru versi PWA (mobile-first).
 *
 * Hanya dipakai oleh akun dengan role guru. Penyimpanan nilai sengaja memakai
 * rute yang sama dengan versi desktop (guru.penilaian.store) supaya aturan
 * validasi, bobot, dan hak aksesnya tetap satu sumber.
 */
class PwaGuruController extends Controller
{
    /**
     * Beranda aplikasi guru: ringkasan progres penilaian.
     *
     * @param  Request  $request  HTTP request
     * @return View Halaman beranda aplikasi guru
     */
    public function beranda(Request $request): View
    {
        $konteks = $this->konteks($request);

        if (! $konteks['guru'] || ! $konteks['tahunId']) {
            return view('guru.pwa.beranda', $konteks + [
                'penugasan' => collect(),
                'ringkasan' => $this->ringkasanKosong(),
                'lanjutkan' => collect(),
            ]);
        }

        $penugasan = $this->penugasan($konteks);
        $ringkasan = $this->ringkasan($penugasan, $konteks);
        $lanjutkan = $this->butuhPerhatian($penugasan, $konteks)->take(3)->values();

        return view('guru.pwa.beranda', $konteks + [
            'penugasan' => $penugasan,
            'ringkasan' => $ringkasan,
            'lanjutkan' => $lanjutkan,
        ]);
    }

    /**
     * Daftar penugasan mengajar guru beserta progres pengisian nilainya.
     *
     * @param  Request  $request  HTTP request
     * @return View Halaman daftar nilai
     */
    public function nilai(Request $request): View
    {
        $konteks = $this->konteks($request);

        if (! $konteks['guru'] || ! $konteks['tahunId']) {
            return view('guru.pwa.nilai', $konteks + [
                'perKelas' => collect(),
                'progres' => collect(),
                'jumlahSiswaPerKelas' => collect(),
            ]);
        }

        $penugasan = $this->penugasan($konteks);

        return view('guru.pwa.nilai', [
            'perKelas' => $penugasan->groupBy('kelas_id'),
            'progres' => $this->progres($penugasan, $konteks),
            'jumlahSiswaPerKelas' => $this->jumlahSiswaPerKelas($penugasan),
        ] + $konteks);
    }

    /**
     * Form input nilai cepat untuk satu penugasan mengajar.
     *
     * @param  Request  $request  HTTP request
     * @param  Mengajar  $mengajar  Penugasan mengajar dari route model binding
     * @return View Halaman input nilai
     */
    public function formNilai(Request $request, Mengajar $mengajar): View
    {
        $konteks = $this->konteks($request);

        if (! $konteks['guru'] || $mengajar->guru_id !== $konteks['guru']->id) {
            abort(403, __('Anda tidak memiliki akses ke penilaian ini.'));
        }

        if (! $konteks['tahunId'] || $mengajar->tahun_ajaran_id !== $konteks['tahunId']) {
            abort(404, __('Penilaian tidak tersedia untuk tahun ajaran ini.'));
        }

        $siswas = Siswa::where('kelas_id', $mengajar->kelas_id)
            ->orderBy('nama')
            ->get();

        $nilaiBySiswa = Penilaian::where('mengajar_id', $mengajar->id)
            ->where('tahun_ajaran_id', $konteks['tahunId'])
            ->when($konteks['semester'], fn ($query) => $query->where('semester', $konteks['semester']))
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $user = $request->user();

        return view('guru.pwa.nilai-form', [
            'mengajar' => $mengajar->load(['kelas', 'mataPelajaran']),
            'siswas' => $siswas,
            'nilaiBySiswa' => $nilaiBySiswa,
            'bobotSumatif' => (float) ($user->bobot_sumatif ?? config('rapor.bobot_sumatif', 50)),
            'bobotSts' => (float) ($user->bobot_sts ?? config('rapor.bobot_sts', 50)),
            'materiTp' => $nilaiBySiswa->first()?->materi_tp,
            'canEdit' => (bool) $konteks['tahunAjaran']?->is_active,
        ] + $konteks);
    }

    /**
     * Mengumpulkan konteks halaman: guru, tahun ajaran, dan semester aktif.
     *
     * @param  Request  $request  HTTP request
     * @return array{guru: Guru|null, tahunId: int|null, semester: string|null, tahunAjaran: TahunAjaran|null}
     */
    private function konteks(Request $request): array
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        return [
            'guru' => Guru::where('user_id', $request->user()->id)->first(),
            'tahunId' => $tahunId ? (int) $tahunId : null,
            'semester' => $semester !== null ? (string) $semester : null,
            'tahunAjaran' => $tahunId ? TahunAjaran::query()->whereKey($tahunId)->first() : null,
        ];
    }

    /**
     * Mengambil seluruh penugasan mengajar guru pada tahun ajaran & semester aktif.
     *
     * @param  array{guru: Guru|null, tahunId: int|null, semester: string|null, tahunAjaran: TahunAjaran|null}  $konteks
     * @return Collection<int, Mengajar>
     */
    private function penugasan(array $konteks): Collection
    {
        return Mengajar::with(['kelas', 'mataPelajaran'])
            ->where('guru_id', $konteks['guru']?->id)
            ->where('tahun_ajaran_id', $konteks['tahunId'])
            ->when($konteks['semester'], fn ($query) => $query->where('semester', $konteks['semester']))
            ->orderBy('kelas_id')
            ->orderBy('mata_pelajaran_id')
            ->get();
    }

    /**
     * Menghitung jumlah siswa terisi pada tiap penugasan.
     *
     * @param  Collection<int, Mengajar>  $penugasan
     * @param  array{guru: Guru|null, tahunId: int|null, semester: string|null, tahunAjaran: TahunAjaran|null}  $konteks
     * @return array<int, array{terisi: int, lengkap: int}> Progres per ID penugasan
     */
    private function progres(Collection $penugasan, array $konteks): array
    {
        if ($penugasan->isEmpty()) {
            return [];
        }

        return Penilaian::whereIn('mengajar_id', $penugasan->pluck('id'))
            ->where('tahun_ajaran_id', $konteks['tahunId'])
            ->when($konteks['semester'], fn ($query) => $query->where('semester', $konteks['semester']))
            ->selectRaw('mengajar_id')
            ->selectRaw('SUM(CASE WHEN nilai_sumatif IS NOT NULL OR nilai_sts IS NOT NULL THEN 1 ELSE 0 END) as terisi')
            ->selectRaw('SUM(CASE WHEN nilai_sumatif IS NOT NULL AND nilai_sts IS NOT NULL THEN 1 ELSE 0 END) as lengkap')
            ->groupBy('mengajar_id')
            ->get()
            ->mapWithKeys(function (Penilaian $baris): array {
                $atribut = $baris->getAttributes();

                return [
                    (int) $atribut['mengajar_id'] => [
                        'terisi' => (int) $atribut['terisi'],
                        'lengkap' => (int) $atribut['lengkap'],
                    ],
                ];
            })
            ->all();
    }

    /**
     * Menghitung jumlah siswa per kelas untuk penugasan yang sedang ditampilkan.
     *
     * @param  Collection<int, Mengajar>  $penugasan
     * @return array<int, int> Jumlah siswa per ID kelas
     */
    private function jumlahSiswaPerKelas(Collection $penugasan): array
    {
        if ($penugasan->isEmpty()) {
            return [];
        }

        return Siswa::whereIn('kelas_id', $penugasan->pluck('kelas_id')->unique())
            ->selectRaw('kelas_id, COUNT(*) as jumlah')
            ->groupBy('kelas_id')
            ->get()
            ->mapWithKeys(function (Siswa $baris): array {
                $atribut = $baris->getAttributes();

                return [(int) $atribut['kelas_id'] => (int) $atribut['jumlah']];
            })
            ->all();
    }

    /**
     * Ringkasan progres seluruh penugasan.
     *
     * @param  Collection<int, Mengajar>  $penugasan
     * @param  array{guru: Guru|null, tahunId: int|null, semester: string|null, tahunAjaran: TahunAjaran|null}  $konteks
     * @return array{penugasan: int, kelas: int, siswa: int, terisi: int, lengkap: int}
     */
    private function ringkasan(Collection $penugasan, array $konteks): array
    {
        $jumlahSiswa = $this->jumlahSiswaPerKelas($penugasan);
        $progres = $this->progres($penugasan, $konteks);

        $totalSiswa = 0;
        $totalTerisi = 0;
        $totalLengkap = 0;

        foreach ($penugasan as $item) {
            $totalSiswa += $jumlahSiswa[$item->kelas_id] ?? 0;
            $totalTerisi += $progres[$item->id]['terisi'] ?? 0;
            $totalLengkap += $progres[$item->id]['lengkap'] ?? 0;
        }

        return [
            'penugasan' => $penugasan->count(),
            'kelas' => $penugasan->pluck('kelas_id')->unique()->count(),
            'siswa' => $totalSiswa,
            'terisi' => $totalTerisi,
            'lengkap' => $totalLengkap,
        ];
    }

    /**
     * Penugasan yang paling perlu diisi (nilai belum lengkap).
     *
     * @param  Collection<int, Mengajar>  $penugasan
     * @param  array{guru: Guru|null, tahunId: int|null, semester: string|null, tahunAjaran: TahunAjaran|null}  $konteks
     * @return Collection<int, array{mengajar: Mengajar, jumlahSiswa: int, lengkap: int, terisi: int}>
     */
    private function butuhPerhatian(Collection $penugasan, array $konteks): Collection
    {
        $jumlahSiswa = $this->jumlahSiswaPerKelas($penugasan);
        $progres = $this->progres($penugasan, $konteks);

        return $penugasan
            ->filter(fn (Mengajar $item): bool => ($jumlahSiswa[$item->kelas_id] ?? 0) > 0)
            ->sortBy(fn (Mengajar $item): int => $progres[$item->id]['lengkap'] ?? 0)
            ->map(fn (Mengajar $item): array => [
                'mengajar' => $item,
                'jumlahSiswa' => $jumlahSiswa[$item->kelas_id] ?? 0,
                'lengkap' => $progres[$item->id]['lengkap'] ?? 0,
                'terisi' => $progres[$item->id]['terisi'] ?? 0,
            ])
            ->values();
    }

    /**
     * Ringkasan kosong untuk kondisi tahun ajaran belum dipilih.
     *
     * @return array{penugasan: int, kelas: int, siswa: int, terisi: int, lengkap: int}
     */
    private function ringkasanKosong(): array
    {
        return ['penugasan' => 0, 'kelas' => 0, 'siswa' => 0, 'terisi' => 0, 'lengkap' => 0];
    }
}
