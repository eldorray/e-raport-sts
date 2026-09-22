<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Services\PenghapusanDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    public function index(): View
    {
        $tahunAjaran = TahunAjaran::withCount([
            'siswas',
            'kelas',
            'penilaians',
            'raporMetadatas',
            'tahfidzPenilaians',
            'mengajars',
        ])
            ->orderByDesc('is_active')
            ->orderByDesc('tahun_mulai')
            ->get();

        $stats = [
            'total' => $tahunAjaran->count(),
            'active' => $tahunAjaran->where('is_active', true)->count(),
            'latest' => $tahunAjaran->first(),
        ];

        return view('lembaga.tahun-ajaran', compact('tahunAjaran', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            $record = TahunAjaran::create($data);

            if ($record->is_active) {
                TahunAjaran::where('id', '!=', $record->id)->update(['is_active' => false]);
            }
        });

        return back()->with('status', __('Tahun ajaran berhasil ditambahkan.'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $data = $this->validatedData($request, $tahunAjaran->id);

        DB::transaction(function () use ($data, $tahunAjaran) {
            $tahunAjaran->update($data);

            if ($data['is_active'] ?? false) {
                TahunAjaran::where('id', '!=', $tahunAjaran->id)->update(['is_active' => false]);
            }
        });

        return back()->with('status', __('Tahun ajaran berhasil diperbarui.'));
    }

    /**
     * Menghapus tahun ajaran.
     *
     * Penghapusan ditolak bila tahun ajaran masih menyimpan siswa, kelas, nilai,
     * atau rapor agar tidak ada data yang hilang tanpa disadari.
     *
     * @param  TahunAjaran  $tahunAjaran  Instance tahun ajaran dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        if ($tahunAjaran->is_active) {
            return back()->with('status', __('Nonaktifkan tahun ajaran ini sebelum menghapus.'));
        }

        $alasan = (new PenghapusanDataService)->alasanTahunAjaranTidakBisaDihapus($tahunAjaran);

        if ($alasan !== null) {
            return back()->withErrors(['tahun_ajaran' => $alasan]);
        }

        $tahunAjaran->delete();

        return back()->with('status', __('Tahun ajaran berhasil dihapus.'));
    }

    public function toggleActive(TahunAjaran $tahunAjaran): RedirectResponse
    {
        DB::transaction(function () use ($tahunAjaran) {
            if (! $tahunAjaran->is_active) {
                // Activating: deactivate all others first
                TahunAjaran::where('id', '!=', $tahunAjaran->id)->update(['is_active' => false]);
                $tahunAjaran->update(['is_active' => true]);
            } else {
                // Deactivating
                $tahunAjaran->update(['is_active' => false]);
            }
        });

        $status = $tahunAjaran->fresh()->is_active;

        return back()->with('status', $status ? __('Tahun ajaran diaktifkan.') : __('Tahun ajaran dinonaktifkan.'));
    }

    public function activate(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        // Choose target from request dropdown (dashboard) or route model binding
        /** @var TahunAjaran $target */
        $target = $request->filled('tahun_ajaran_id')
            ? TahunAjaran::findOrFail($request->integer('tahun_ajaran_id'))
            : $tahunAjaran;

        $shouldActivate = ! $request->boolean('skip_activation');

        if ($shouldActivate) {
            DB::transaction(function () use ($target) {
                $target->update(['is_active' => true]);
                TahunAjaran::where('id', '!=', $target->id)->update(['is_active' => false]);
            });

            $target->refresh();
        }

        $request->session()->put([
            'selected_tahun_ajaran_id' => $target->id,
            'selected_semester' => $target->semester,
            'selected_tahun_ajaran_is_active' => (bool) $target->is_active,
        ]);

        $request->session()->save();

        return back()->with('status', $shouldActivate ? __('Tahun ajaran diaktifkan.') : __('Tahun ajaran diganti.'));
    }

    public function switchSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'semester' => ['nullable', 'string', 'max:20'],
        ]);

        /** @var TahunAjaran $tahun */
        $tahun = TahunAjaran::findOrFail($data['tahun_ajaran_id']);
        $semester = ($data['semester'] ?? null) ?: $tahun->semester;

        $request->session()->put([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => $semester,
            'selected_tahun_ajaran_is_active' => (bool) $tahun->is_active,
        ]);

        $request->session()->save();

        return back()->with('status', __('Tahun ajaran diganti.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $currentYear = (int) date('Y');

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:50',
            ],
            'tahun_mulai' => ['required', 'integer', 'between:'.($currentYear - 10).','.($currentYear + 10)],
            'tahun_selesai' => ['required', 'integer', 'gte:tahun_mulai', 'between:'.($currentYear - 10).','.($currentYear + 11)],
            'semester' => ['required', 'string', 'max:20'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Check unique constraint for nama + semester combination
        $exists = TahunAjaran::where('nama', $data['nama'])
            ->where('semester', $data['semester'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'nama' => __('Kombinasi nama tahun ajaran dan semester ini sudah ada.'),
            ]);
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
