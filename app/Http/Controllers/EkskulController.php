<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\Guru;
use App\Services\PenghapusanDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EkskulController extends Controller
{
    public function index(): View
    {
        $ekskuls = Ekskul::with('guru')->withCount('penilaians')->orderBy('nama')->get();
        $gurus = Guru::orderBy('nama')->get();

        return view('ekskul.index', compact('ekskuls', 'gurus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'guru_id' => ['nullable', 'exists:gurus,id'],
        ]);

        Ekskul::create($data);

        return back()->with('status', __('Ekskul berhasil ditambahkan.'));
    }

    public function update(Request $request, Ekskul $ekskul): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'guru_id' => ['nullable', 'exists:gurus,id'],
        ]);

        $ekskul->update($data);

        return back()->with('status', __('Ekskul berhasil diperbarui.'));
    }

    /**
     * Menghapus ekskul.
     *
     * Penghapusan ditolak bila ekskul masih punya nilai agar nilai ekskul tidak
     * ikut terhapus.
     *
     * @param  Ekskul  $ekskul  Instance ekskul dari route model binding
     * @return RedirectResponse Redirect ke halaman sebelumnya dengan pesan status
     */
    public function destroy(Ekskul $ekskul): RedirectResponse
    {
        $alasan = (new PenghapusanDataService)->alasanEkskulTidakBisaDihapus($ekskul);

        if ($alasan !== null) {
            return back()->withErrors(['ekskul' => $alasan]);
        }

        $ekskul->delete();

        return back()->with('status', __('Ekskul dihapus.'));
    }
}
