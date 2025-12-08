<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\Guru;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EkskulController extends Controller
{
    public function index(): View
    {
        $ekskuls = Ekskul::with('guru')->orderBy('nama')->get();
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

    public function destroy(Ekskul $ekskul): RedirectResponse
    {
        $ekskul->delete();

        return back()->with('status', __('Ekskul dihapus.'));
    }
}
