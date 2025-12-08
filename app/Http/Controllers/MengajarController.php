<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MengajarController extends Controller
{
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

        $mataPelajarans = MataPelajaran::orderBy('urutan')->orderBy('nama_mapel')->get();
        $gurus = Guru::orderBy('nama')->get();
        $tahunOptions = TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();

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
            'tahunId',
            'semester'
        ));
    }

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

    public function update(Request $request, Mengajar $mengajar): RedirectResponse
    {
        $data = $this->validatedData($request, $mengajar->tahun_ajaran_id, $mengajar->semester);

        $mengajar->update($data);

        return back()->with('status', __('Jadwal mengajar diperbarui.'));
    }

    public function destroy(Mengajar $mengajar): RedirectResponse
    {
        $mengajar->delete();

        return back()->with('status', __('Jadwal mengajar dihapus.'));
    }

    public function copy(Request $request): RedirectResponse
    {
        $targetYear = session('selected_tahun_ajaran_id');
        $targetSemester = session('selected_semester');

        $data = $request->validate([
            'source_tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);

        $kelasTarget = Kelas::findOrFail($data['kelas_id']);

        $sourceRecords = Mengajar::where('tahun_ajaran_id', $data['source_tahun_ajaran_id'])
            ->when($targetSemester, fn ($q) => $q->where('semester', $targetSemester))
            ->where('kelas_id', $kelasTarget->id)
            ->get();

        DB::transaction(function () use ($sourceRecords, $targetYear, $targetSemester, $kelasTarget) {
            foreach ($sourceRecords as $record) {
                Mengajar::updateOrCreate(
                    [
                        'tahun_ajaran_id' => $targetYear,
                        'semester' => $targetSemester,
                        'kelas_id' => $kelasTarget->id,
                        'mata_pelajaran_id' => $record->mata_pelajaran_id,
                    ],
                    [
                        'guru_id' => $record->guru_id,
                        'jtm' => $record->jtm,
                    ]
                );
            }
        });

        return back()->with('status', __('Jadwal mengajar berhasil disalin.'));
    }

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

}
