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

    /**
     * Menyalin rombel dari tahun ajaran/semester sebelumnya.
     */
    public function copy(Request $request): RedirectResponse
    {
        $targetTahunId = session('selected_tahun_ajaran_id');

        if (! $targetTahunId) {
            return back()->withErrors(['error' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $data = $request->validate([
            'source_tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id', 'different:target_tahun_ajaran_id'],
        ]);

        $sourceTahunId = $data['source_tahun_ajaran_id'];

        // Get kelas dari source tahun ajaran
        $sourceKelasWithSiswas = Kelas::with('siswas')
            ->where('tahun_ajaran_id', $sourceTahunId)
            ->get();

        if ($sourceKelasWithSiswas->isEmpty()) {
            return back()->with('warning', __('Tidak ada kelas di tahun ajaran sumber.'));
        }

        $copiedCount = 0;
        $skippedCount = 0;

        foreach ($sourceKelasWithSiswas as $sourceKelas) {
            // Cari kelas dengan nama yang sama di target tahun ajaran
            $targetKelas = Kelas::where('tahun_ajaran_id', $targetTahunId)
                ->where('nama', $sourceKelas->nama)
                ->first();

            if (! $targetKelas) {
                $skippedCount++;
                continue;
            }

            // Copy siswa assignments
            foreach ($sourceKelas->siswas as $siswa) {
                // Hanya update jika siswa belum punya kelas di target tahun ajaran
                if ($siswa->kelas_id === $sourceKelas->id || $siswa->kelas_id === null) {
                    $siswa->update(['kelas_id' => $targetKelas->id]);
                    $copiedCount++;
                }
            }
        }

        $message = __('Berhasil menyalin :count siswa ke rombel.', ['count' => $copiedCount]);
        if ($skippedCount > 0) {
            $message .= ' ' . __(':count kelas dilewati karena tidak ditemukan di tahun ajaran tujuan.', ['count' => $skippedCount]);
        }

        return back()->with('status', $message);
    }
}

