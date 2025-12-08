<?php

namespace App\Http\Controllers;

use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(): View
    {
        $siswas = Siswa::with('kelas')
            ->orderBy('nama')
            ->get();

        return view('siswa.index', compact('siswas'));
    }

    public function show(Siswa $siswa): View
    {
        return view('siswa.show', compact('siswa'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tahunId = session('selected_tahun_ajaran_id');

        if (! $tahunId) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $data = $this->validatedData($request);
        $data['tahun_ajaran_id'] = $tahunId;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('siswa', 'public');
        }

        Siswa::create($data);

        return back()->with('status', __('Siswa berhasil ditambahkan.'));
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $data = $this->validatedData($request, $siswa->id);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('siswa', 'public');
            if ($siswa->photo_path) {
                Storage::disk('public')->delete($siswa->photo_path);
            }
        }

        $siswa->update($data);

        return back()->with('status', __('Siswa berhasil diperbarui.'));
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        if ($siswa->photo_path) {
            Storage::disk('public')->delete($siswa->photo_path);
        }

        $siswa->delete();

        return back()->with('status', __('Siswa dihapus.'));
    }

    public function import(Request $request): RedirectResponse
    {
        if (! session('selected_tahun_ajaran_id')) {
            return back()->withErrors(['tahun_ajaran' => __('Pilih tahun ajaran terlebih dahulu.')]);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new SiswaImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors([
                'file' => __('Gagal memproses file: :message', ['message' => $e->getMessage()]),
            ]);
        }

        $failuresCount = $import->failures()->count();
        $skippedCount = count($import->skipped);
        $failureMessages = [];

        if ($failuresCount > 0) {
            foreach ($import->failures() as $failure) {
                $failureMessages[] = __('Baris :row: :errors', [
                    'row' => $failure->row(),
                    'errors' => implode('; ', $failure->errors()),
                ]);
            }
        }

        $statusParts = [];
        $statusParts[] = __(':count siswa berhasil diimpor.', ['count' => $import->imported]);

        if ($skippedCount > 0) {
            $statusParts[] = __(':count baris dilewati (duplikat/invalid).', ['count' => $skippedCount]);
        }

        if ($failuresCount > 0) {
            $statusParts[] = __(':count baris gagal validasi.', ['count' => $failuresCount]);
            $statusParts[] = implode(' | ', $failureMessages);
        }

        $response = back()->with('status', implode(' ', $statusParts));

        if (! empty($failureMessages)) {
            return $response->withErrors(['file' => $failureMessages]);
        }

        return $response;
    }

    public function template()
    {
        return Excel::download(new SiswaTemplateExport(), 'template-siswa.xlsx');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $tahunId = session('selected_tahun_ajaran_id');

        $rules = [
            'nis' => [
                'required',
                'string',
                'max:30',
                Rule::unique('siswas', 'nis')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('siswas', 'nisn')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                    ->ignore($ignoreId),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'status_keluarga' => ['nullable', 'string', 'max:50'],
            'anak_ke' => ['nullable', 'integer', 'min:1'],
            'telpon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'sekolah_asal' => ['nullable', 'string', 'max:150'],
            'tanggal_diterima' => ['nullable', 'date'],
            'kelas_diterima' => ['nullable', 'string', 'max:50'],
            'nama_ayah' => ['nullable', 'string', 'max:150'],
            'nama_ibu' => ['nullable', 'string', 'max:150'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:100'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:100'],
            'alamat_orang_tua' => ['nullable', 'string'],
            'nama_wali' => ['nullable', 'string', 'max:150'],
            'pekerjaan_wali' => ['nullable', 'string', 'max:100'],
            'alamat_wali' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];

        return $request->validate($rules);
    }
}
