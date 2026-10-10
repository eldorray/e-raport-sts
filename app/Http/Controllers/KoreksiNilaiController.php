<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\GradeDescriptorService;
use App\Services\PenyimpananNilaiService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Koreksi nilai Sumatif/STS oleh admin per kelas dan mata pelajaran.
 *
 * Admin menulis ke baris nilai milik guru pengampu (guru_id jadwal mengajar)
 * lewat PenyimpananNilaiService, sehingga tidak perlu lagi memindahkan guru
 * hanya untuk membetulkan nilai, dan setiap baris yang diubah mencatat siapa
 * dan kapan koreksinya dilakukan.
 */
class KoreksiNilaiController extends Controller
{
    public function __construct(private readonly PenyimpananNilaiService $penyimpanan) {}

    /**
     * Daftar kelas pada tahun ajaran terpilih beserta jadwal mengajar dan progres nilainya.
     *
     * @param  Request  $request  HTTP request (opsional ?kelas= untuk menyaring satu kelas)
     * @return View Halaman daftar koreksi nilai
     */
    public function index(Request $request): View
    {
        $tahunId = (int) session('selected_tahun_ajaran_id') ?: null;
        $kelasFilter = $request->integer('kelas') ?: null;

        $tahunAjaran = $tahunId ? TahunAjaran::find($tahunId) : null;

        $semuaKelas = $tahunAjaran
            ? Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->withCount('siswas')->orderBy('nama')->get()
            : new EloquentCollection;

        $kelasTampil = $kelasFilter ? $semuaKelas->where('id', $kelasFilter)->values() : $semuaKelas;

        $mengajarPerKelas = $kelasTampil->isEmpty()
            ? collect()
            : Mengajar::with(['mataPelajaran', 'guru'])
                ->where('tahun_ajaran_id', $tahunId)
                ->whereIn('kelas_id', $kelasTampil->pluck('id'))
                ->orderBy('mata_pelajaran_id')
                ->get()
                ->groupBy('kelas_id');

        $lengkapPerMengajar = $this->jumlahLengkapPerMengajar(
            $mengajarPerKelas->flatten(1)->pluck('id'),
            (int) $tahunId,
        );

        return view('admin.koreksi-nilai.index', [
            'tahunAjaran' => $tahunAjaran,
            'semuaKelas' => $semuaKelas,
            'kelasTampil' => $kelasTampil,
            'kelasFilter' => $kelasFilter,
            'mengajarPerKelas' => $mengajarPerKelas,
            'lengkapPerMengajar' => $lengkapPerMengajar,
        ]);
    }

    /**
     * Form koreksi nilai satu jadwal mengajar (sama seperti form guru).
     *
     * @param  Mengajar  $mengajar  Jadwal mengajar dari route model binding
     * @return View Halaman form koreksi
     */
    public function show(Mengajar $mengajar): View
    {
        $mengajar->load(['kelas', 'mataPelajaran', 'tahunAjaran', 'guru.user']);

        $semester = $this->penyimpanan->semesterMengajar($mengajar);
        $bobot = $this->penyimpanan->bobotGuru($mengajar->guru?->user);

        $siswas = Siswa::where('kelas_id', $mengajar->kelas_id)
            ->orderBy('nama')
            ->get();

        // Baca baris dengan kunci yang sama persis dengan yang akan ditulis
        // (guru_id jadwal). Jadwal tanpa guru hanya ditampilkan apa adanya.
        $nilaiBySiswa = Penilaian::with('pengoreksi:id,name')
            ->where('mengajar_id', $mengajar->id)
            ->where('tahun_ajaran_id', $mengajar->tahun_ajaran_id)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->when($mengajar->guru_id, fn ($q) => $q->where('guru_id', $mengajar->guru_id))
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->orderBy('id')
            ->get()
            ->keyBy('siswa_id');

        return view('admin.koreksi-nilai.show', [
            'mengajar' => $mengajar,
            'siswas' => $siswas,
            'nilaiBySiswa' => $nilaiBySiswa,
            'semester' => $semester,
            'bobotSumatif' => $bobot['sumatif'],
            'bobotSts' => $bobot['sts'],
            'bobotValid' => $this->penyimpanan->bobotValid($bobot['sumatif'], $bobot['sts']),
            'tahunDitutup' => $mengajar->tahunAjaran?->is_active !== true,
            'bisaSimpan' => $mengajar->guru_id !== null && $semester !== null,
            'gradeService' => new GradeDescriptorService,
        ]);
    }

    /**
     * Simpan koreksi nilai ke baris milik guru pengampu.
     *
     * Konteks tulis (tahun ajaran & semester) diambil dari jadwal mengajar,
     * bukan dari sesi, sehingga koreksi tahun ajaran yang sudah ditutup tetap
     * masuk ke tahun ajaran yang benar.
     *
     * @param  Request  $request  HTTP request dengan data nilai
     * @param  Mengajar  $mengajar  Jadwal mengajar dari route model binding
     * @return RedirectResponse Kembali ke form koreksi
     */
    public function store(Request $request, Mengajar $mengajar): RedirectResponse
    {
        $mengajar->load(['tahunAjaran', 'guru.user']);

        if ($mengajar->guru_id === null) {
            return back()
                ->withErrors(['guru' => __('Jadwal ini belum punya guru pengampu. Atur guru di menu Mengajar sebelum mengoreksi nilai.')])
                ->withInput();
        }

        $semester = $this->penyimpanan->semesterMengajar($mengajar);

        if ($semester === null) {
            return back()
                ->withErrors(['semester' => __('Semester jadwal mengajar ini tidak diketahui, jadi nilainya belum bisa dikoreksi.')])
                ->withInput();
        }

        $validated = $request->validate($this->penyimpanan->aturanValidasi());

        $bobot = $this->penyimpanan->bobotGuru($mengajar->guru?->user);

        if (! $this->penyimpanan->bobotValid($bobot['sumatif'], $bobot['sts'])) {
            return back()
                ->withErrors(['bobot_sumatif' => __('Total bobot penilaian guru pengampu belum 100%. Minta guru memperbaiki bobotnya terlebih dahulu.')])
                ->withInput();
        }

        $jumlahBerubah = $this->penyimpanan->simpan(
            $mengajar,
            (int) $mengajar->tahun_ajaran_id,
            $semester,
            $validated,
            $bobot['sumatif'],
            $bobot['sts'],
            $request->user(),
        );

        $pesan = $jumlahBerubah > 0
            ? __('Koreksi disimpan: :jumlah nilai siswa diubah.', ['jumlah' => $jumlahBerubah])
            : __('Tidak ada nilai yang berubah.');

        return redirect()->route('koreksi-nilai.show', $mengajar)->with('status', $pesan);
    }

    /**
     * Jumlah siswa dengan nilai lengkap (Sumatif dan STS terisi) per jadwal mengajar.
     *
     * Hanya baris milik guru pengampu saat ini (guru_id jadwal) dan siswa yang
     * masih berada di kelas tersebut yang dihitung, sama seperti yang tampil di form.
     *
     * @param  Collection<int, mixed>  $mengajarIds  ID jadwal mengajar
     * @param  int  $tahunId  ID tahun ajaran
     * @return Collection<int|string, int> mengajar_id => jumlah siswa lengkap
     */
    private function jumlahLengkapPerMengajar(Collection $mengajarIds, int $tahunId): Collection
    {
        if ($mengajarIds->isEmpty()) {
            return collect();
        }

        return Penilaian::query()
            ->join('mengajars', function (JoinClause $join): void {
                $join->on('mengajars.id', '=', 'penilaians.mengajar_id')
                    ->on('mengajars.guru_id', '=', 'penilaians.guru_id');
            })
            ->join('siswas', function (JoinClause $join): void {
                $join->on('siswas.id', '=', 'penilaians.siswa_id')
                    ->on('siswas.kelas_id', '=', 'mengajars.kelas_id');
            })
            ->whereIn('penilaians.mengajar_id', $mengajarIds)
            ->where('penilaians.tahun_ajaran_id', $tahunId)
            ->whereNotNull('penilaians.nilai_sumatif')
            ->whereNotNull('penilaians.nilai_sts')
            ->groupBy('penilaians.mengajar_id')
            ->selectRaw('penilaians.mengajar_id, COUNT(DISTINCT penilaians.siswa_id) as lengkap')
            ->pluck('lengkap', 'mengajar_id')
            ->map(fn (mixed $jumlah): int => (int) $jumlah);
    }
}
