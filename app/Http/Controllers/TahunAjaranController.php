<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::orderByDesc('is_active')
            ->orderByDesc('tahun_mulai')
            ->get();

        $stats = [
            'total' => $tahunAjaran->count(),
            'active' => $tahunAjaran->where('is_active', true)->count(),
            'latest' => $tahunAjaran->first(),
        ];

        return view('lembaga.tahun-ajaran', compact('tahunAjaran', 'stats'));
    }

    public function store(Request $request)
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

    public function update(Request $request, TahunAjaran $tahunAjaran)
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

    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->is_active) {
            return back()->with('status', __('Nonaktifkan tahun ajaran ini sebelum menghapus.'));
        }

        $tahunAjaran->delete();

        return back()->with('status', __('Tahun ajaran berhasil dihapus.'));
    }

    public function activate(Request $request, TahunAjaran $tahunAjaran)
    {
        // Choose target from request dropdown (dashboard) or route model binding
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

        return back()->with('status', $shouldActivate ? __('Tahun ajaran diaktifkan.') : __('Tahun ajaran diganti.'));
    }

    public function switchSession(Request $request)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'semester' => ['nullable', 'string', 'max:20'],
        ]);

        $tahun = TahunAjaran::findOrFail($data['tahun_ajaran_id']);
        $semester = ($data['semester'] ?? null) ?: $tahun->semester;

        $request->session()->put([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => $semester,
            'selected_tahun_ajaran_is_active' => (bool) $tahun->is_active,
        ]);

        return back()->with('status', __('Tahun ajaran diganti.'));
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $currentYear = (int) date('Y');

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tahun_ajarans', 'nama')->ignore($ignoreId),
            ],
            'tahun_mulai' => ['required', 'integer', 'between:' . ($currentYear - 10) . ',' . ($currentYear + 10)],
            'tahun_selesai' => ['required', 'integer', 'gte:tahun_mulai', 'between:' . ($currentYear - 10) . ',' . ($currentYear + 11)],
            'semester' => ['nullable', 'string', 'max:20'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
