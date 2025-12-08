<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id') ?? \App\Models\TahunAjaran::where('is_active', true)->value('id');

        $kelasList = Kelas::with(['guru'])
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->withCount('siswas')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $gurus = Guru::orderBy('nama')->get();

        return view('kelas.index', [
            'kelasList' => $kelasList,
            'gurus' => $gurus,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');

        if (! $tahunId) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $data = $this->validatedData($request);
        $data['tahun_ajaran_id'] = $tahunId;

        $kelas = Kelas::create($data);

        $this->syncGuruWaliKelas($kelas);

        return back()->with('status', __('Kelas berhasil dibuat.'));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $this->validatedData($request, $kelas->id);

        $previousGuruId = $kelas->guru_id;

        $kelas->fill($data);
        $kelas->save();

        if ($previousGuruId && $previousGuruId !== $kelas->guru_id) {
            Guru::whereKey($previousGuruId)->update(['wali_kelas' => null]);
        }

        $this->syncGuruWaliKelas($kelas);

        return back()->with('status', __('Kelas berhasil diperbarui.'));
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        // Put related siswa back to unassigned state
        Siswa::where('kelas_id', $kelas->id)->update(['kelas_id' => null]);

        if ($kelas->guru_id) {
            Guru::whereKey($kelas->guru_id)->update(['wali_kelas' => null]);
        }

        $kelas->delete();

        return back()->with('status', __('Kelas dihapus.'));
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $tahunId = session('selected_tahun_ajaran_id');

        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelas', 'nama')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'tingkat' => ['required', 'string', 'max:20'],
            'jurusan' => ['nullable', 'string', 'max:50'],
            'jenis' => ['nullable', 'string', 'max:50'],
            'guru_id' => ['nullable', Rule::exists('gurus', 'id')],
        ]);
    }

    private function syncGuruWaliKelas(Kelas $kelas): void
    {
        if ($kelas->guru_id) {
            $kelas->guru()->update(['wali_kelas' => $kelas->nama]);
        }
    }
}
