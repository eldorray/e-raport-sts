<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MengajarTahfidz;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller untuk mengelola penugasan guru mengajar Tahfidz.
 */
class MengajarTahfidzController extends Controller
{
    /**
     * Menampilkan daftar assignment tahfidz per kelas.
     */
    public function index(Request $request): View
    {
        $tahunId = session('selected_tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->value('id');
        $semester = session('selected_semester');

        $kelasList = Kelas::when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $gurus = Guru::where('is_active', true)->orderBy('nama')->get();

        $assignments = MengajarTahfidz::with(['kelas', 'guru'])
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->get()
            ->keyBy('kelas_id');

        return view('mengajar-tahfidz.index', compact(
            'kelasList',
            'gurus',
            'assignments',
            'tahunId',
            'semester'
        ));
    }

    /**
     * Menyimpan atau update assignment tahfidz.
     */
    public function store(Request $request): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId) {
            return back()->withErrors(['error' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.kelas_id' => ['required', Rule::exists('kelas', 'id')],
            'items.*.guru_id' => ['nullable', Rule::exists('gurus', 'id')],
        ]);

        foreach ($validated['items'] as $item) {
            if (! empty($item['guru_id'])) {
                MengajarTahfidz::updateOrCreate(
                    [
                        'tahun_ajaran_id' => $tahunId,
                        'semester' => $semester,
                        'kelas_id' => $item['kelas_id'],
                    ],
                    [
                        'guru_id' => $item['guru_id'],
                    ]
                );
            } else {
                // Jika guru_id kosong, hapus assignment jika ada
                MengajarTahfidz::where([
                    'tahun_ajaran_id' => $tahunId,
                    'semester' => $semester,
                    'kelas_id' => $item['kelas_id'],
                ])->delete();
            }
        }

        return back()->with('status', __('Assignment tahfidz berhasil disimpan.'));
    }

    /**
     * Menghapus assignment tahfidz.
     */
    public function destroy(MengajarTahfidz $mengajarTahfidz): RedirectResponse
    {
        $mengajarTahfidz->delete();

        return back()->with('status', __('Assignment tahfidz dihapus.'));
    }
}
