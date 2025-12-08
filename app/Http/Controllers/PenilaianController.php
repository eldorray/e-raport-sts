<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenilaianController extends Controller
{
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

    public function show(Request $request, Mengajar $mengajar): View
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');
        $guru = Guru::where('user_id', $request->user()->id)->first();
            $user = $request->user();

        if (! $guru || $mengajar->guru_id !== $guru->id) {
            abort(403, __('Anda tidak memiliki akses ke penilaian ini.'));
        }

        if (! $tahunId || $mengajar->tahun_ajaran_id !== $tahunId) {
            abort(404, __('Penilaian tidak tersedia untuk tahun ajaran ini.'));
        }

        // Tampilkan semua siswa di kelas ini (termasuk yang belum/belum tepat tahun ajarannya),
        // agar guru bisa menilai seluruh anggota kelas.
        $siswas = Siswa::where('kelas_id', $mengajar->kelas_id)
            ->orderBy('nama')
            ->get();

        $nilaiBySiswa = Penilaian::where('mengajar_id', $mengajar->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        return view('guru.penilaian.show', [
            'mengajar' => $mengajar,
            'siswas' => $siswas,
            'nilaiBySiswa' => $nilaiBySiswa,
            'tahunId' => $tahunId,
            'semester' => $semester,
                'bobotSumatif' => $user?->bobot_sumatif ?? (float) config('rapor.bobot_sumatif', 50),
                'bobotSts' => $user?->bobot_sts ?? (float) config('rapor.bobot_sts', 50),
        ]);
    }

    public function store(Request $request, Mengajar $mengajar): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');
        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $tahunId || ! $semester) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran & semester terlebih dahulu.')]);
        }

        if (! $guru || $mengajar->guru_id !== $guru->id) {
            abort(403, __('Anda tidak memiliki akses ke penilaian ini.'));
        }

        $validated = $request->validate([
            'nilai_sumatif' => ['sometimes', 'array'],
            'nilai_sumatif.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_sts' => ['sometimes', 'array'],
            'nilai_sts.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'materi_tp' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $bobotSumatif = $user?->bobot_sumatif ?? (float) config('rapor.bobot_sumatif', 50);
        $bobotSts = $user?->bobot_sts ?? (float) config('rapor.bobot_sts', 50);

        if (abs(($bobotSumatif + $bobotSts) - 100) > 0.01) {
            return back()
                ->withErrors(['bobot_sumatif' => __('Total bobot harus 100%.')])
                ->withInput();
        }

        // Ambil semua siswa di kelas ini tanpa menyaring tahun ajaran supaya tidak ada yang terlewat
        $siswas = Siswa::where('kelas_id', $mengajar->kelas_id)
            ->pluck('id');

        DB::transaction(function () use ($validated, $mengajar, $guru, $tahunId, $semester, $siswas, $bobotSumatif, $bobotSts) {
            $mengajar->bobot_sumatif = $bobotSumatif;
            $mengajar->bobot_sts = $bobotSts;
            $mengajar->save();

            $sumatifPayload = $validated['nilai_sumatif'] ?? [];
            $stsPayload = $validated['nilai_sts'] ?? [];
            $materiTp = isset($validated['materi_tp']) ? trim($validated['materi_tp']) : null;

            $allKeys = collect(array_keys($sumatifPayload) + array_keys($stsPayload))->unique();

            foreach ($allKeys as $siswaId) {
                if (! $siswas->contains((int) $siswaId)) {
                    continue; // skip siswa not in kelas/year
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
        });

        return back()->with('status', __('Nilai disimpan.'));
    }

    public function editBobot(Request $request): View
    {
        $user = $request->user();

        return view('penilaian.bobot', [
            'bobotSumatif' => $user?->bobot_sumatif ?? (float) config('rapor.bobot_sumatif', 50),
            'bobotSts' => $user?->bobot_sts ?? (float) config('rapor.bobot_sts', 50),
            'user' => $user,
        ]);
    }

    public function updateBobot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bobot_sumatif' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_sts' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (abs(($data['bobot_sumatif'] + $data['bobot_sts']) - 100) > 0.01) {
            return back()->withErrors(['bobot_sumatif' => __('Total bobot harus 100%.')])->withInput();
        }

        $request->user()->update([
            'bobot_sumatif' => $data['bobot_sumatif'],
            'bobot_sts' => $data['bobot_sts'],
        ]);

        return back()->with('status', __('Bobot penilaian diperbarui.'));
    }
}
