<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Imports\GuruImport;
use App\Exports\GuruTemplateExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class GuruController extends Controller
{
    public function index(): View
    {
        $gurus = Guru::with('user')->orderBy('nama')->paginate(15);

        $tahunId = session('selected_tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->value('id');
        $guruIds = $gurus->pluck('id');

        $jtmMengajar = [];
        if ($guruIds->isNotEmpty()) {
            $mengajar = Mengajar::with('mataPelajaran')
                ->whereIn('guru_id', $guruIds)
                ->when($tahunId, fn($q) => $q->where('tahun_ajaran_id', $tahunId))
                ->get();

            foreach ($mengajar as $m) {
                $jam = $m->jtm ?? $m->mataPelajaran?->jumlah_jam ?? 0;
                $jtmMengajar[$m->guru_id] = ($jtmMengajar[$m->guru_id] ?? 0) + (int) $jam;
            }
        }

        return view('guru.index', [
            'gurus' => $gurus,
            'jtmMengajar' => $jtmMengajar,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $user = User::create([
            'name' => $data['nama'],
            'email' => $this->buildEmail($data['nip']),
            'password' => Hash::make($data['password']),
            'role' => 'guru',
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        Guru::create([
            'user_id' => $user->id,
            'nama' => $data['nama'],
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'pendidikan' => $data['pendidikan'] ?? null,
            'wali_kelas' => $data['wali_kelas'] ?? null,
            'jtm' => $data['jtm'] ?? null,
            'initial_password' => $data['password'],
            'is_active' => $data['is_active'],
        ]);

        return back()->with('status', __('Guru berhasil ditambahkan.'));
    }

    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $data = $this->validatedData($request, $guru->id);

        $updateUser = [
            'name' => $data['nama'],
            'email' => $guru->user->email ?: $this->buildEmail($data['nip']),
            'role' => 'guru',
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $updateUser['password'] = Hash::make($data['password']);
        }

        $guru->user->update($updateUser);

        $guru->update([
            'nama' => $data['nama'],
            'nip' => $data['nip'],
            'nik' => $data['nik'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'pendidikan' => $data['pendidikan'] ?? null,
            'wali_kelas' => $data['wali_kelas'] ?? null,
            'jtm' => $data['jtm'] ?? null,
            'initial_password' => $data['password'] ?: $guru->initial_password,
            'is_active' => $data['is_active'],
        ]);

        return back()->with('status', __('Guru berhasil diperbarui.'));
    }

    public function destroy(Guru $guru): RedirectResponse
    {
        $guru->user?->delete();
        $guru->delete();

        return back()->with('status', __('Guru dihapus.'));
    }

    public function toggleStatus(Guru $guru): RedirectResponse
    {
        $new = ! $guru->is_active;
        $guru->update(['is_active' => $new]);
        $guru->user?->update(['is_active' => $new]);

        return back()->with('status', $new ? __('Guru diaktifkan.') : __('Guru dinonaktifkan.'));
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new GuruImport();

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
        $statusParts[] = __(':count guru berhasil diimpor.', ['count' => $import->imported]);

        if ($skippedCount > 0) {
            $statusParts[] = __(':count baris dilewati (duplikat/invalid).', ['count' => $skippedCount]);
        }

        if ($failuresCount > 0) {
            $statusParts[] = __(':count baris gagal validasi.', ['count' => $failuresCount]);
        }

        if (! empty($failureMessages)) {
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
        return Excel::download(new GuruTemplateExport(), 'template-guru.xlsx');
    }

    private function validatedData(Request $request, ?int $ignoreGuruId = null): array
    {
        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:30', Rule::unique('gurus', 'nip')->ignore($ignoreGuruId)],
            'nik' => ['nullable', 'string', 'max:30', Rule::unique('gurus', 'nik')->ignore($ignoreGuruId)],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'pendidikan' => ['nullable', 'string', 'max:100'],
            'wali_kelas' => ['nullable', 'string', 'max:50'],
            'jtm' => ['nullable', 'integer', 'min:0'],
            'password' => [$ignoreGuruId ? 'nullable' : 'required', 'string', 'min:3'],
            'is_active' => ['required', 'boolean'],
        ];

        $data = $request->validate($rules);
        $data['password'] = $request->filled('password') ? $request->string('password') : '';

        return $data;
    }

    private function buildEmail(string $nip): string
    {
        return Str::slug($nip, '.') . '@guru.local';
    }
}
