<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $mataPelajaran = MataPelajaran::orderByRaw('COALESCE(NULLIF(urutan, ""), "9999")')
            ->orderBy('urutan')
            ->get();

        return view('lembaga.matapelajaran', compact('mataPelajaran'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:20', 'unique:mata_pelajarans,kode'],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'jumlah_jam' => ['nullable', 'integer', 'min:0'],
            'kelompok' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'urutan' => ['nullable', 'string', 'max:50'],
        ]);

        MataPelajaran::create($data);

        return back()->with('status', __('Mata pelajaran berhasil ditambahkan.'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $data = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mata_pelajarans', 'kode')->ignore($mataPelajaran->id),
            ],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'jumlah_jam' => ['nullable', 'integer', 'min:0'],
            'kelompok' => ['nullable', 'string', 'max:100'],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'urutan' => ['nullable', 'string', 'max:50'],
        ]);

        $mataPelajaran->update($data);

        return back()->with('status', __('Mata pelajaran berhasil diperbarui.'));
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();

        return back()->with('status', __('Mata pelajaran berhasil dihapus.'));
    }
}
