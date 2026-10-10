<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Support\MenuAplikasi;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Aplikasi admin versi PWA (mobile-first).
 *
 * Berisi ringkasan kelengkapan nilai, pintasan ke semua menu web, dan pengaturan akun.
 * Pengolahan data tetap memakai halaman web yang sudah ada (responsif), sehingga
 * validasi dan hak aksesnya tetap satu sumber.
 */
class PwaAdminController extends Controller
{
    /** Jumlah guru yang ditampilkan pada daftar "nilai belum lengkap" di beranda. */
    private const BATAS_GURU_BELUM_LENGKAP = 5;

    /**
     * Beranda: tahun ajaran terpilih dan kelengkapan nilai seluruh madrasah.
     *
     * @return View Halaman beranda aplikasi admin
     */
    public function beranda(): View
    {
        $konteks = $this->konteks();

        return view('admin.pwa.beranda', $konteks + $this->kelengkapan($konteks));
    }

    /**
     * Semua menu admin versi web, dikelompokkan seperti sidebar.
     *
     * @return View Halaman menu aplikasi admin
     */
    public function menu(): View
    {
        return view('admin.pwa.menu', $this->konteks() + [
            'grupMenu' => MenuAplikasi::admin(),
        ]);
    }

    /**
     * Akun admin: identitas, tahun ajaran, tema, sandi, dan keluar.
     *
     * @param  Request  $request  HTTP request
     * @return View Halaman akun aplikasi admin
     */
    public function akun(Request $request): View
    {
        return view('admin.pwa.akun', $this->konteks() + [
            'user' => $request->user(),
        ]);
    }

    /**
     * Konteks halaman: daftar tahun ajaran untuk pemilih dan tahun ajaran terpilih di sesi.
     *
     * @return array{daftarTahun: Collection<int, TahunAjaran>, tahunAjaran: TahunAjaran|null, tahunId: int|null, semester: string|null}
     */
    private function konteks(): array
    {
        $daftarTahun = TahunAjaran::query()
            ->orderByDesc('tahun_mulai')
            ->orderByDesc('nama')
            ->orderBy('semester')
            ->get(['id', 'nama', 'semester', 'is_active']);

        $idTerpilih = (int) session('selected_tahun_ajaran_id');
        /** @var TahunAjaran|null $tahunAjaran */
        $tahunAjaran = $idTerpilih > 0 ? $daftarTahun->firstWhere('id', $idTerpilih) : null;
        $semester = session('selected_semester') ?? $tahunAjaran?->semester;

        return [
            'daftarTahun' => $daftarTahun,
            'tahunAjaran' => $tahunAjaran,
            'tahunId' => $tahunAjaran?->id,
            'semester' => $semester !== null ? (string) $semester : null,
        ];
    }

    /**
     * Kelengkapan nilai tahun ajaran terpilih.
     *
     * Target = jumlah siswa kelas untuk setiap penugasan mengajar; lengkap = siswa kelas itu
     * yang nilai sumatif DAN STS-nya sudah terisi. Dihitung dengan kueri berkelompok
     * (jumlah kueri tetap, tidak bergantung pada banyaknya kelas atau penugasan).
     *
     * @param  array{daftarTahun: Collection<int, TahunAjaran>, tahunAjaran: TahunAjaran|null, tahunId: int|null, semester: string|null}  $konteks
     * @return array{ringkasan: array{target: int, lengkap: int, persen: int, penugasan: int, kelas: int, siswa: int}, perKelas: Collection<int, array{kelas: Kelas, penugasan: int, siswa: int, target: int, lengkap: int, persen: int}>, guruBelumLengkap: Collection<int, array{id: int, nama: string, belum: int, penugasan: int}>, jumlahGuruBelumLengkap: int}
     */
    private function kelengkapan(array $konteks): array
    {
        $tahunId = $konteks['tahunId'];
        $semester = $konteks['semester'];

        if ($tahunId === null) {
            return [
                'ringkasan' => ['target' => 0, 'lengkap' => 0, 'persen' => 0, 'penugasan' => 0, 'kelas' => 0, 'siswa' => 0],
                'perKelas' => collect(),
                'guruBelumLengkap' => collect(),
                'jumlahGuruBelumLengkap' => 0,
            ];
        }

        $mengajars = Mengajar::query()
            ->where('tahun_ajaran_id', $tahunId)
            ->when($semester, fn ($query) => $query->where('semester', $semester))
            ->get(['id', 'kelas_id', 'guru_id']);

        $kelas = Kelas::query()
            ->where('tahun_ajaran_id', $tahunId)
            ->orWhereIn('id', $mengajars->pluck('kelas_id')->filter()->unique()->values())
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat']);

        $jumlahSiswa = $this->jumlahSiswaPerKelas($kelas->pluck('id'));
        $lengkapPerMengajar = $this->lengkapPerMengajar($tahunId, $semester);

        /** @var array<int, array{kelas: Kelas, penugasan: int, siswa: int, target: int, lengkap: int, persen: int}> $perKelas */
        $perKelas = [];

        foreach ($kelas as $item) {
            $perKelas[$item->id] = [
                'kelas' => $item,
                'penugasan' => 0,
                'siswa' => $jumlahSiswa[$item->id] ?? 0,
                'target' => 0,
                'lengkap' => 0,
                'persen' => 0,
            ];
        }

        /** @var array<int, array{belum: int, penugasan: int}> $perGuru */
        $perGuru = [];

        foreach ($mengajars as $mengajar) {
            if (! isset($perKelas[$mengajar->kelas_id])) {
                continue;
            }

            $siswa = $perKelas[$mengajar->kelas_id]['siswa'];
            $lengkap = min($siswa, $lengkapPerMengajar[$mengajar->id] ?? 0);

            $perKelas[$mengajar->kelas_id]['penugasan']++;
            $perKelas[$mengajar->kelas_id]['target'] += $siswa;
            $perKelas[$mengajar->kelas_id]['lengkap'] += $lengkap;

            if ($mengajar->guru_id) {
                $perGuru[$mengajar->guru_id] ??= ['belum' => 0, 'penugasan' => 0];
                $perGuru[$mengajar->guru_id]['penugasan']++;

                if ($lengkap < $siswa) {
                    $perGuru[$mengajar->guru_id]['belum']++;
                }
            }
        }

        $perKelas = collect($perKelas)
            ->map(fn (array $baris): array => [...$baris, 'persen' => $this->persen($baris['lengkap'], $baris['target'])])
            ->values();

        $target = (int) $perKelas->sum('target');
        $lengkap = (int) $perKelas->sum('lengkap');
        $guruBelumLengkap = $this->guruBelumLengkap($perGuru);

        return [
            'ringkasan' => [
                'target' => $target,
                'lengkap' => $lengkap,
                'persen' => $this->persen($lengkap, $target),
                'penugasan' => (int) $perKelas->sum('penugasan'),
                'kelas' => $perKelas->count(),
                'siswa' => (int) $perKelas->sum('siswa'),
            ],
            'perKelas' => $perKelas,
            'guruBelumLengkap' => $guruBelumLengkap->take(self::BATAS_GURU_BELUM_LENGKAP)->values(),
            'jumlahGuruBelumLengkap' => $guruBelumLengkap->count(),
        ];
    }

    /**
     * Jumlah siswa per kelas.
     *
     * @param  Collection<int, int>  $kelasIds
     * @return array<int, int> Jumlah siswa per ID kelas
     */
    private function jumlahSiswaPerKelas(Collection $kelasIds): array
    {
        if ($kelasIds->isEmpty()) {
            return [];
        }

        return Siswa::query()
            ->whereIn('kelas_id', $kelasIds)
            ->groupBy('kelas_id')
            ->toBase()
            ->selectRaw('kelas_id, COUNT(*) as jumlah')
            ->pluck('jumlah', 'kelas_id')
            ->mapWithKeys(fn (mixed $jumlah, int|string $kelasId): array => [(int) $kelasId => (int) $jumlah])
            ->all();
    }

    /**
     * Jumlah siswa dengan nilai sumatif dan STS lengkap per penugasan mengajar.
     *
     * Hanya siswa yang masih tercatat di kelas penugasan itu yang dihitung, jadi nilai
     * siswa yang sudah pindah kelas tidak membuat progres melebihi 100%.
     *
     * @return array<int, int> Jumlah siswa lengkap per ID penugasan
     */
    private function lengkapPerMengajar(int $tahunId, ?string $semester): array
    {
        return Penilaian::query()
            ->join('mengajars', 'mengajars.id', '=', 'penilaians.mengajar_id')
            ->join('siswas', function (JoinClause $join): void {
                $join->on('siswas.id', '=', 'penilaians.siswa_id')
                    ->on('siswas.kelas_id', '=', 'mengajars.kelas_id');
            })
            ->where('mengajars.tahun_ajaran_id', $tahunId)
            ->where('penilaians.tahun_ajaran_id', $tahunId)
            ->when($semester, fn ($query) => $query
                ->where('mengajars.semester', $semester)
                ->where('penilaians.semester', $semester))
            ->whereNotNull('penilaians.nilai_sumatif')
            ->whereNotNull('penilaians.nilai_sts')
            ->groupBy('penilaians.mengajar_id')
            ->toBase()
            ->selectRaw('penilaians.mengajar_id as mengajar_id, COUNT(DISTINCT penilaians.siswa_id) as lengkap')
            ->pluck('lengkap', 'mengajar_id')
            ->mapWithKeys(fn (mixed $jumlah, int|string $mengajarId): array => [(int) $mengajarId => (int) $jumlah])
            ->all();
    }

    /**
     * Guru yang masih punya penugasan dengan nilai belum lengkap, terbanyak lebih dulu.
     *
     * @param  array<int, array{belum: int, penugasan: int}>  $perGuru
     * @return Collection<int, array{id: int, nama: string, belum: int, penugasan: int}>
     */
    private function guruBelumLengkap(array $perGuru): Collection
    {
        $belum = array_filter($perGuru, fn (array $baris): bool => $baris['belum'] > 0);

        if ($belum === []) {
            return collect();
        }

        $nama = Guru::query()->whereIn('id', array_keys($belum))->pluck('nama', 'id');

        return collect($belum)
            ->map(fn (array $baris, int $guruId): array => [
                'id' => $guruId,
                'nama' => (string) ($nama[$guruId] ?? '—'),
                'belum' => $baris['belum'],
                'penugasan' => $baris['penugasan'],
            ])
            ->sortBy([
                fn (array $a, array $b): int => $b['belum'] <=> $a['belum'],
                fn (array $a, array $b): int => strcasecmp($a['nama'], $b['nama']),
            ])
            ->values();
    }

    /**
     * Persentase dibulatkan ke bawah supaya 100% hanya tampil bila benar-benar lengkap.
     */
    private function persen(int $bagian, int $total): int
    {
        return $total > 0 ? intdiv($bagian * 100, $total) : 0;
    }
}
