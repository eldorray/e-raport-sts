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
            ->withCount(['siswas' => fn ($q) => $q->where('tahun_ajaran_id', $tahunId)])
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
            // PENTING: Filter siswa berdasarkan tahun_ajaran_id yang sedang aktif
            // agar tidak tercampur dengan siswa dari tahun ajaran lain
            $siswas = Siswa::with('kelas')
                ->where('tahun_ajaran_id', $tahunId)
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
        $tahunId = $kelas->tahun_ajaran_id;

        // Remove students no longer in this kelas
        // PENTING: Filter juga berdasarkan tahun_ajaran_id untuk mencegah
        // pengaruh ke siswa dari tahun ajaran lain
        Siswa::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->whereNotIn('id', $selectedIds)
            ->update(['kelas_id' => null]);

        if (! empty($selectedIds)) {
            // Pastikan hanya update siswa dari tahun ajaran yang sama
            Siswa::whereIn('id', $selectedIds)
                ->where('tahun_ajaran_id', $tahunId)
                ->update(['kelas_id' => $kelas->id]);
        }

        return redirect()->route('rombel.index', ['kelas_id' => $kelas->id])
            ->with('status', __('Rombel kelas berhasil diperbarui.'));
    }
    /**
     * Menyalin rombel dari tahun ajaran/semester sebelumnya.
     * 
     * Jika siswa belum ada di tahun ajaran target, akan dibuat duplikat data siswa.
     * Kemudian siswa tersebut akan di-assign ke kelas yang sesuai.
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

        // Get kelas dari source tahun ajaran beserta siswanya
        $sourceKelasWithSiswas = Kelas::with(['siswas' => function ($query) use ($sourceTahunId) {
                $query->where('tahun_ajaran_id', $sourceTahunId);
            }])
            ->where('tahun_ajaran_id', $sourceTahunId)
            ->get();

        if ($sourceKelasWithSiswas->isEmpty()) {
            return back()->with('warning', __('Tidak ada kelas di tahun ajaran sumber.'));
        }

        $copiedCount = 0;
        $createdCount = 0;
        $skippedKelasCount = 0;
        $skippedSiswaCount = 0;

        foreach ($sourceKelasWithSiswas as $sourceKelas) {
            // Cari kelas dengan nama yang sama di target tahun ajaran
            $targetKelas = Kelas::where('tahun_ajaran_id', $targetTahunId)
                ->where('nama', $sourceKelas->nama)
                ->first();

            if (! $targetKelas) {
                $skippedKelasCount++;
                continue;
            }

            foreach ($sourceKelas->siswas as $sourceSiswa) {
                // Cari siswa di tahun ajaran target dengan NISN atau NIS yang sama
                $targetSiswa = null;
                
                if (! empty($sourceSiswa->nisn)) {
                    $targetSiswa = Siswa::where('tahun_ajaran_id', $targetTahunId)
                        ->where('nisn', $sourceSiswa->nisn)
                        ->first();
                }
                
                if (! $targetSiswa && ! empty($sourceSiswa->nis)) {
                    $targetSiswa = Siswa::where('tahun_ajaran_id', $targetTahunId)
                        ->where('nis', $sourceSiswa->nis)
                        ->first();
                }

                // Jika siswa belum ada di target, buat duplikat
                if (! $targetSiswa) {
                    $siswaData = $sourceSiswa->toArray();
                    unset($siswaData['id'], $siswaData['created_at'], $siswaData['updated_at']);
                    $siswaData['tahun_ajaran_id'] = $targetTahunId;
                    $siswaData['kelas_id'] = $targetKelas->id;
                    
                    try {
                        $targetSiswa = Siswa::create($siswaData);
                        $createdCount++;
                        $copiedCount++;
                    } catch (\Exception $e) {
                        // Skip jika ada error (misal duplikat NIS/NISN)
                        $skippedSiswaCount++;
                        continue;
                    }
                } else {
                    // Siswa sudah ada, update kelas_id jika belum punya kelas
                    if (is_null($targetSiswa->kelas_id)) {
                        $targetSiswa->update(['kelas_id' => $targetKelas->id]);
                        $copiedCount++;
                    } else {
                        $skippedSiswaCount++;
                    }
                }
            }
        }

        $message = __('Berhasil: :copied siswa di-assign ke kelas.', ['copied' => $copiedCount]);
        if ($createdCount > 0) {
            $message .= ' ' . __(':count siswa baru dibuat.', ['count' => $createdCount]);
        }
        if ($skippedKelasCount > 0) {
            $message .= ' ' . __(':count kelas dilewati.', ['count' => $skippedKelasCount]);
        }
        if ($skippedSiswaCount > 0) {
            $message .= ' ' . __(':count siswa dilewati (sudah punya kelas/duplikat).', ['count' => $skippedSiswaCount]);
        }

        return back()->with('status', $message);
    }
}


