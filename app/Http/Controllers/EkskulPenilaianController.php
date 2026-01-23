<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EkskulPenilaianController extends Controller
{
    public function index(Request $request): View
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();

        $assignments = collect();
        if ($guru) {
            $assignments = Ekskul::with('guru')
                ->where('guru_id', $guru->id)
                ->orderBy('nama')
                ->get();
        }

        return view('guru.ekskul.index', [
            'assignments' => $assignments,
        ]);
    }

    public function show(Request $request, Ekskul $ekskul): View
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $guru || $ekskul->guru_id !== $guru->id) {
            abort(403, __('Anda tidak memiliki akses ke penilaian ekskul ini.'));
        }

        $existing = EkskulPenilaian::where('ekskul_id', $ekskul->id)
            ->where('guru_id', $guru->id)
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->get()
            ->keyBy('siswa_id');

        $selectedIds = $existing->keys();

        $participants = $selectedIds->isNotEmpty()
            ? Siswa::with('kelas')->whereIn('id', $selectedIds)->orderBy('nama')->get()
            : collect();

        $availableSiswas = Siswa::with('kelas')
            ->when($selectedIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $selectedIds))
            ->orderBy('nama')
            ->get();

        // Check if tahun ajaran is active (guru can only edit on active tahun ajaran)
        $tahunAjaran = TahunAjaran::find($tahunId);
        $canEdit = $tahunAjaran && $tahunAjaran->is_active;

        return view('guru.ekskul.show', [
            'ekskul' => $ekskul,
            'participants' => $participants,
            'availableSiswas' => $availableSiswas,
            'existing' => $existing,
            'tahunId' => $tahunId,
            'semester' => $semester,
            'canEdit' => $canEdit,
        ]);
    }

    public function store(Request $request, Ekskul $ekskul): RedirectResponse
    {
        $guru = Guru::where('user_id', $request->user()->id)->first();
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $guru || $ekskul->guru_id !== $guru->id) {
            abort(403, __('Anda tidak memiliki akses ke penilaian ekskul ini.'));
        }

        if (! $tahunId || ! $semester) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran & semester terlebih dahulu.')]);
        }

        // Block guru from editing inactive tahun ajaran
        $tahunAjaran = TahunAjaran::find($tahunId);
        if (! $tahunAjaran || ! $tahunAjaran->is_active) {
            return back()->withErrors(['tahun_ajaran' => __('Tidak dapat menyimpan data pada tahun ajaran yang tidak aktif.')]);
        }

        $action = $request->input('action', 'save');

        if ($action === 'add') {
            $data = $request->validate([
                'siswa_ids' => ['required', 'array'],
                'siswa_ids.*' => ['exists:siswas,id'],
            ]);

            DB::transaction(function () use ($data, $ekskul, $guru, $tahunId, $semester) {
                foreach ($data['siswa_ids'] as $siswaId) {
                    EkskulPenilaian::firstOrCreate([
                        'ekskul_id' => $ekskul->id,
                        'guru_id' => $guru->id,
                        'siswa_id' => $siswaId,
                        'tahun_ajaran_id' => $tahunId,
                        'semester' => $semester,
                    ]);
                }
            });

            return back()->with('status', __('Siswa ditambahkan ke penilaian ekskul.'));
        }

        if ($action === 'remove') {
            $data = $request->validate([
                'siswa_id' => ['required', 'exists:siswas,id'],
            ]);

            EkskulPenilaian::where('ekskul_id', $ekskul->id)
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunId)
                ->where('semester', $semester)
                ->where('siswa_id', $data['siswa_id'])
                ->delete();

            return back()->with('status', __('Peserta dihapus dari penilaian ekskul.'));
        }

        $validated = $request->validate([
            'nilai' => ['sometimes', 'array'],
            'nilai.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['sometimes', 'array'],
            'catatan.*' => ['nullable', 'string'],
        ]);

        $nilaiPayload = $validated['nilai'] ?? [];
        $catatanPayload = $validated['catatan'] ?? [];
        $siswaIds = array_unique(array_merge(array_keys($nilaiPayload), array_keys($catatanPayload)));

        DB::transaction(function () use ($siswaIds, $nilaiPayload, $catatanPayload, $ekskul, $guru, $tahunId, $semester) {
            foreach ($siswaIds as $siswaId) {
                $record = EkskulPenilaian::firstOrNew([
                    'ekskul_id' => $ekskul->id,
                    'guru_id' => $guru->id,
                    'siswa_id' => $siswaId,
                    'tahun_ajaran_id' => $tahunId,
                    'semester' => $semester,
                ]);

                if (array_key_exists($siswaId, $nilaiPayload)) {
                    $record->nilai = $nilaiPayload[$siswaId] !== null ? (float) $nilaiPayload[$siswaId] : null;
                }

                if (array_key_exists($siswaId, $catatanPayload)) {
                    $record->catatan = $catatanPayload[$siswaId] !== '' ? $catatanPayload[$siswaId] : null;
                }

                $record->save();
            }
        });

        return back()->with('status', __('Penilaian ekskul disimpan.'));
    }
}
