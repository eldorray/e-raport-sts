<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\TahunAjaran;
use App\Services\PenghapusanDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller untuk mengelola jadwal mengajar.
 *
 * Menangani penugasan guru ke mata pelajaran per kelas.
 */
class MengajarController extends Controller
{
    /**
     * Semester yang dikenal aplikasi.
     *
     * @var array<int, string>
     */
    private const SEMESTER_VALID = ['Ganjil', 'Genap'];

    /**
     * Menampilkan daftar jadwal mengajar per kelas.
     *
     * @param  Request  $request  HTTP request
     * @return View|RedirectResponse Halaman index mengajar
     */
    public function index(Request $request): View|RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->value('id');
        $semester = session('selected_semester');

        $kelasList = Kelas::when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $tingkats = $kelasList->pluck('tingkat')->unique()->values();

        $selectedTingkat = $request->string('tingkat')->toString() ?: $tingkats->first();
        $kelasByTingkat = $kelasList->when($selectedTingkat, fn ($c) => $c->where('tingkat', $selectedTingkat));
        $selectedKelasId = $request->integer('kelas_id') ?: $kelasByTingkat->first()?->id;

        $selectedKelas = $selectedKelasId ? $kelasList->firstWhere('id', $selectedKelasId) : null;

        $mengajars = collect();
        $mengajarByMapel = collect();

        if ($selectedKelas && $tahunId) {
            $mengajars = Mengajar::with(['mataPelajaran', 'guru'])
                ->where('tahun_ajaran_id', $tahunId)
                ->when($semester, fn ($q) => $q->where('semester', $semester))
                ->where('kelas_id', $selectedKelas->id)
                ->orderBy('id')
                ->get();

            $mengajarByMapel = $mengajars->keyBy('mata_pelajaran_id');
        }

        $mataPelajarans = MataPelajaran::orderByRaw(
            "CASE kelompok WHEN 'PAI' THEN 1 WHEN 'Umum' THEN 2 WHEN 'Mulok' THEN 3 ELSE 4 END"
        )
            ->orderByRaw("COALESCE(NULLIF(urutan, '') + 0, 9999)")
            ->orderBy('nama_mapel')
            ->get();
        $gurus = Guru::orderBy('nama')->get();
        $tahunOptions = TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();

        // Jumlah jadwal mengajar tiap tahun ajaran per semester, dipakai untuk
        // menandai tahun ajaran mana yang benar-benar bisa disalin.
        $jadwalPerTahun = Mengajar::selectRaw('tahun_ajaran_id, semester, COUNT(*) as jumlah_jadwal')
            ->groupBy('tahun_ajaran_id', 'semester')
            ->get()
            ->mapWithKeys(fn (Mengajar $baris): array => [
                $baris->tahun_ajaran_id.'-'.$baris->semester => (int) $baris->getAttribute('jumlah_jadwal'),
            ]);

        return view('mengajar.index', compact(
            'tingkats',
            'kelasList',
            'selectedTingkat',
            'selectedKelasId',
            'selectedKelas',
            'mengajars',
            'mengajarByMapel',
            'mataPelajarans',
            'gurus',
            'tahunOptions',
            'jadwalPerTahun',
            'tahunId',
            'semester'
        ));
    }

    /**
     * Menyimpan jadwal mengajar baru.
     *
     * @param  Request  $request  HTTP request dengan data jadwal
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function store(Request $request): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $validated = $request->validate([
            'kelas_id' => [
                'required',
                Rule::exists('kelas', 'id')->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId)),
            ],
            'items' => ['required', 'array'],
            'items.*.mata_pelajaran_id' => ['required', Rule::exists('mata_pelajarans', 'id')],
            'items.*.guru_id' => ['nullable', Rule::exists('gurus', 'id')],
            'items.*.jtm' => ['nullable', 'integer', 'min:0'],
        ]);

        $kelasId = $validated['kelas_id'];

        foreach ($validated['items'] as $item) {
            Mengajar::updateOrCreate(
                [
                    'tahun_ajaran_id' => $tahunId,
                    'semester' => $semester,
                    'kelas_id' => $kelasId,
                    'mata_pelajaran_id' => $item['mata_pelajaran_id'],
                ],
                [
                    'guru_id' => $item['guru_id'] ?? null,
                    'jtm' => $item['jtm'] ?? null,
                ]
            );
        }

        return back()->with('status', __('Jadwal mengajar disimpan.'));
    }

    /**
     * Memperbarui jadwal mengajar yang sudah ada.
     *
     * @param  Request  $request  HTTP request dengan data yang diperbarui
     * @param  Mengajar  $mengajar  Instance mengajar dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function update(Request $request, Mengajar $mengajar): RedirectResponse
    {
        $data = $this->validatedData($request, $mengajar->tahun_ajaran_id, $mengajar->semester);

        $mengajar->update($data);

        return back()->with('status', __('Jadwal mengajar diperbarui.'));
    }

    /**
     * Menghapus jadwal mengajar.
     *
     * Penghapusan ditolak bila jadwal ini masih menyimpan nilai agar data nilai
     * tidak ikut terhapus.
     *
     * @param  Mengajar  $mengajar  Instance mengajar dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(Mengajar $mengajar): RedirectResponse
    {
        $alasan = (new PenghapusanDataService)->alasanMengajarTidakBisaDihapus($mengajar);

        if ($alasan !== null) {
            return back()->withErrors(['mengajar' => $alasan]);
        }

        $mengajar->delete();

        return back()->with('status', __('Jadwal mengajar dihapus.'));
    }

    /**
     * Menyalin jadwal mengajar dari tahun ajaran lain.
     *
     * @param  Request  $request  HTTP request dengan data sumber
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function copy(Request $request): RedirectResponse
    {
        $targetYear = session('selected_tahun_ajaran_id');
        $targetSemester = session('selected_semester');

        if (! $targetYear) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        if (! $targetSemester) {
            return back()->withErrors(['semester' => __('Pilih semester terlebih dahulu.')]);
        }

        $data = $request->validate([
            'source_tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'source_semester' => ['nullable', Rule::in(self::SEMESTER_VALID)],
            'kelas_id' => ['required', Rule::exists('kelas', 'id')->where('tahun_ajaran_id', $targetYear)],
        ], [
            'kelas_id.exists' => __('Kelas tujuan tidak terdaftar pada tahun ajaran yang sedang dipilih.'),
            'source_semester.in' => __('Semester sumber tidak dikenal.'),
        ]);

        $sourceSemester = $data['source_semester'] ?? $targetSemester;

        /** @var TahunAjaran $tahunSumber */
        $tahunSumber = TahunAjaran::findOrFail($data['source_tahun_ajaran_id']);

        if ($tahunSumber->id === (int) $targetYear && $sourceSemester === $targetSemester) {
            return back()->with('error', __('Tahun ajaran dan semester sumber sama dengan yang sedang dipilih, jadi tidak ada yang perlu disalin. Pilih semester sumber lain bila ingin menyalin dari Ganjil ke Genap.'));
        }

        /** @var Kelas $kelasTarget */
        $kelasTarget = Kelas::findOrFail($data['kelas_id']);
        $kelasSumber = $this->kelasSetara($tahunSumber->id, $kelasTarget);

        if (! $kelasSumber) {
            return back()->with('error', __('Kelas :kelas tidak ditemukan pada tahun ajaran sumber (:sumber).', [
                'kelas' => $kelasTarget->nama,
                'sumber' => $tahunSumber->nama,
            ]));
        }

        $sourceRecords = Mengajar::where('tahun_ajaran_id', $tahunSumber->id)
            ->where('semester', $sourceSemester)
            ->where('kelas_id', $kelasSumber->id)
            ->get();

        if ($sourceRecords->isEmpty()) {
            return back()->with('error', __('Kelas :kelas belum punya jadwal mengajar pada semester :semester di tahun ajaran :sumber.', [
                'kelas' => $kelasSumber->nama,
                'semester' => $sourceSemester,
                'sumber' => $tahunSumber->nama,
            ]));
        }

        DB::transaction(function () use ($sourceRecords, $targetYear, $targetSemester, $kelasTarget) {
            foreach ($sourceRecords as $record) {
                Mengajar::updateOrCreate(
                    [
                        'tahun_ajaran_id' => $targetYear,
                        'semester' => $targetSemester,
                        'kelas_id' => $kelasTarget->id,
                        'mata_pelajaran_id' => $record->mata_pelajaran_id,
                    ],
                    $this->atributSalinan($record)
                );
            }
        });

        return back()->with('status', __(':jumlah jadwal mengajar disalin dari :sumber semester :semester_sumber ke kelas :kelas semester :semester_tujuan.', [
            'jumlah' => $sourceRecords->count(),
            'sumber' => $tahunSumber->nama,
            'semester_sumber' => $sourceSemester,
            'kelas' => $kelasTarget->nama,
            'semester_tujuan' => $targetSemester,
        ]));
    }

    /**
     * Menyalin jadwal mengajar dari kelas lain di tahun ajaran yang sama.
     *
     * @param  Request  $request  HTTP request dengan data sumber
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function copyFromKelas(Request $request): RedirectResponse
    {
        $targetYear = session('selected_tahun_ajaran_id');
        $targetSemester = session('selected_semester');

        if (! $targetYear) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        if (! $targetSemester) {
            return back()->withErrors(['semester' => __('Pilih semester terlebih dahulu.')]);
        }

        $data = $request->validate([
            'source_kelas_id' => ['required', Rule::exists('kelas', 'id')->where('tahun_ajaran_id', $targetYear)],
            'target_kelas_id' => [
                'required',
                Rule::exists('kelas', 'id')->where('tahun_ajaran_id', $targetYear),
                'different:source_kelas_id',
            ],
        ], [
            'source_kelas_id.exists' => __('Kelas sumber tidak terdaftar pada tahun ajaran yang sedang dipilih.'),
            'target_kelas_id.exists' => __('Kelas tujuan tidak terdaftar pada tahun ajaran yang sedang dipilih.'),
        ]);

        /** @var Kelas $kelasSumber */
        $kelasSumber = Kelas::findOrFail($data['source_kelas_id']);

        /** @var Kelas $kelasTarget */
        $kelasTarget = Kelas::findOrFail($data['target_kelas_id']);

        $sourceRecords = Mengajar::where('tahun_ajaran_id', $targetYear)
            ->where('semester', $targetSemester)
            ->where('kelas_id', $kelasSumber->id)
            ->get();

        if ($sourceRecords->isEmpty()) {
            return back()->with('error', __('Tidak ada jadwal mengajar di kelas :kelas pada semester :semester.', [
                'kelas' => $kelasSumber->nama,
                'semester' => $targetSemester,
            ]));
        }

        DB::transaction(function () use ($sourceRecords, $targetYear, $targetSemester, $data) {
            foreach ($sourceRecords as $record) {
                Mengajar::updateOrCreate(
                    [
                        'tahun_ajaran_id' => $targetYear,
                        'semester' => $targetSemester,
                        'kelas_id' => $data['target_kelas_id'],
                        'mata_pelajaran_id' => $record->mata_pelajaran_id,
                    ],
                    $this->atributSalinan($record)
                );
            }
        });

        return back()->with('status', __(':jumlah jadwal mengajar disalin ke kelas :kelas (semester :semester).', [
            'jumlah' => $sourceRecords->count(),
            'kelas' => $kelasTarget->nama,
            'semester' => $targetSemester,
        ]));
    }

    /**
     * Menampilkan mata pelajaran yang diajar oleh guru yang login.
     *
     * @param  Request  $request  HTTP request
     * @return View|RedirectResponse Halaman daftar pelajaran guru
     */
    public function mySubjects(Request $request): View|RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->value('id');
        $semester = session('selected_semester');

        $guru = Guru::where('user_id', $request->user()->id)->first();

        $assignments = collect();

        if ($guru && $tahunId) {
            $assignments = Mengajar::with(['kelas', 'mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunId)
                ->when($semester, fn ($q) => $q->where('semester', $semester))
                ->orderBy('kelas_id')
                ->orderBy('mata_pelajaran_id')
                ->get();
        }

        return view('guru.pelajaran', compact('assignments', 'tahunId', 'semester', 'guru'));
    }

    /**
     * Validasi data request untuk update mengajar.
     *
     * @param  Request  $request  HTTP request dengan data mengajar
     * @param  int|null  $tahunId  ID tahun ajaran
     * @param  string|null  $semester  Semester
     * @return array<string, mixed> Data yang sudah divalidasi
     */
    private function validatedData(Request $request, ?int $tahunId, ?string $semester): array
    {
        return $request->validate([
            'kelas_id' => [
                'required',
                Rule::exists('kelas', 'id')->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId)),
            ],
            'mata_pelajaran_id' => ['required', Rule::exists('mata_pelajarans', 'id')],
            'guru_id' => ['nullable', Rule::exists('gurus', 'id')],
            'jtm' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * Mencari kelas pada tahun ajaran sumber yang setara dengan kelas tujuan.
     *
     * Kelas disimpan per tahun ajaran, sehingga kelas_id tidak bisa dipakai untuk
     * mencocokkan antar tahun ajaran. Pencocokan dilakukan lewat nama yang
     * dinormalisasi (huruf kecil, awalan "kelas" dan tanda baca diabaikan),
     * dengan cadangan tingkat bila pada tahun ajaran sumber hanya ada satu kelas
     * pada tingkat tersebut.
     *
     * @param  int  $tahunAjaranSumber  ID tahun ajaran asal salinan
     * @param  Kelas  $kelasTarget  Kelas tujuan salinan
     * @return Kelas|null Kelas sumber, null bila tidak ada padanannya
     */
    private function kelasSetara(int $tahunAjaranSumber, Kelas $kelasTarget): ?Kelas
    {
        $kelasSumber = Kelas::where('tahun_ajaran_id', $tahunAjaranSumber)->get();
        $namaTarget = $this->normalisasiNamaKelas($kelasTarget->nama);

        $samaNama = $kelasSumber->first(
            fn (Kelas $kelas): bool => $this->normalisasiNamaKelas($kelas->nama) === $namaTarget
        );

        if ($samaNama) {
            return $samaNama;
        }

        $samaTingkat = $kelasSumber->filter(
            fn (Kelas $kelas): bool => $kelas->tingkat === $kelasTarget->tingkat
        );

        return $samaTingkat->count() === 1 ? $samaTingkat->first() : null;
    }

    /**
     * Menormalkan nama kelas agar cocok antar tahun ajaran.
     */
    private function normalisasiNamaKelas(?string $nama): string
    {
        return Str::of((string) $nama)
            ->lower()
            ->replaceMatches('/kelas/', '')
            ->replaceMatches('/[^a-z0-9]/', '')
            ->value();
    }

    /**
     * Menyusun atribut yang disalin dari jadwal mengajar tahun ajaran sumber.
     *
     * Bobot hanya disalin bila tahun ajaran sumber memang menyimpannya, supaya
     * bobot yang sudah diatur pada tahun ajaran tujuan tidak ikut dikosongkan.
     *
     * @param  Mengajar  $record  Jadwal mengajar sumber
     * @return array<string, mixed> Atribut yang akan di-update atau dibuat
     */
    private function atributSalinan(Mengajar $record): array
    {
        $atribut = [
            'guru_id' => $record->guru_id,
            'jtm' => $record->jtm,
        ];

        if ($record->bobot_sumatif !== null) {
            $atribut['bobot_sumatif'] = $record->bobot_sumatif;
        }

        if ($record->bobot_sts !== null) {
            $atribut['bobot_sts'] = $record->bobot_sts;
        }

        return $atribut;
    }
}
