<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RombelKelasController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id') ?? \App\Models\TahunAjaran::where('is_active', true)->value('id');

        $kelasList = Kelas::with(['guru'])
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->withCount('siswas')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();
        $selectedKelas = null;

        if ($request->filled('kelas_id')) {
            $selectedKelas = Kelas::with(['guru', 'siswas'])->find($request->integer('kelas_id'));
        } elseif ($kelasList->isNotEmpty()) {
            $selectedKelas = $kelasList->first();
        }

        $siswas = collect();

        if ($selectedKelas) {
            $siswas = Siswa::with('kelas')
                ->where(fn ($q) => $q->whereNull('kelas_id')->orWhere('kelas_id', $selectedKelas->id))
                ->orderBy('nama')
                ->get();
        }

        return view('rombel.index', [
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'siswas' => $siswas,
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'siswa_ids' => ['array'],
            'siswa_ids.*' => ['exists:siswas,id'],
        ]);

        $selectedIds = $data['siswa_ids'] ?? [];

        // Remove students no longer in this kelas
        Siswa::where('kelas_id', $kelas->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['kelas_id' => null]);

        if (! empty($selectedIds)) {
            Siswa::whereIn('id', $selectedIds)->update(['kelas_id' => $kelas->id]);
        }

        return redirect()->route('rombel.index', ['kelas_id' => $kelas->id])
            ->with('status', __('Rombel kelas berhasil diperbarui.'));
    }

}
