<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RaporMetadata;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaporDataController extends Controller
{
    private function ensureContext(): array
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran & semester terlebih dahulu di dashboard.'));
        }

        $tahun = TahunAjaran::find($tahunId);

        return [$tahunId, $semester, $tahun];
    }

    private function kelasListForUser(Request $request, int $tahunId)
    {
        $role = $request->user()->role ?? null;
        $guru = null;
        $kelasQuery = Kelas::query()->where('tahun_ajaran_id', $tahunId)->orderBy('nama');

        if ($role === 'guru') {
            $guru = Guru::where('user_id', $request->user()->id)->first();
            if (! $guru) {
                abort(403, __('Akun Anda belum terhubung dengan data guru.'));
            }
            $kelasQuery->where('guru_id', $guru->id);
        }

        return [$kelasQuery->get(), $guru];
    }

    private function findKelasOrAbort(?int $kelasId, $kelasList): ?Kelas
    {
        if (! $kelasId) {
            return $kelasList->first();
        }

        return $kelasList->firstWhere('id', $kelasId);
    }

    public function absen(Request $request): View
    {
        [$tahunId, $semester, $tahun] = $this->ensureContext();
        [$kelasList, $guru] = $kelasListAndGuru = $this->kelasListForUser($request, $tahunId);
        $kelasId = $request->integer('kelas_id');
        $kelas = $this->findKelasOrAbort($kelasId, $kelasList);

        $siswas = collect();
        $absen = [];
        if ($kelas) {
            $siswas = $kelas->siswas()->orderBy('nama')->get();
            foreach ($siswas as $siswa) {
                $meta = RaporMetadata::where('tahun_ajaran_id', $tahunId)
                    ->where('semester', $semester)
                    ->where('siswa_id', $siswa->id)
                    ->first();
                $absen[$siswa->id] = [
                    'sakit' => $meta?->sakit ?? 0,
                    'izin' => $meta?->izin ?? 0,
                    'alpa' => $meta?->alpa ?? 0,
                ];
            }
        }

        return view('rapor.absen', compact('kelasList', 'kelas', 'kelasId', 'tahunId', 'semester', 'tahun', 'siswas', 'absen'));
    }

    public function absenStore(Request $request): RedirectResponse
    {
        [$tahunId, $semester] = $this->ensureContext();
        $kelasId = $request->integer('kelas_id');
        if (! $kelasId) {
            return back()->withErrors(['kelas_id' => __('Pilih kelas terlebih dahulu.')]);
        }

        $request->validate([
            'absen' => ['required', 'array'],
            'absen.*.sakit' => ['nullable', 'integer', 'min:0', 'max:365'],
            'absen.*.izin' => ['nullable', 'integer', 'min:0', 'max:365'],
            'absen.*.alpa' => ['nullable', 'integer', 'min:0', 'max:365'],
        ]);

        $kelas = Kelas::findOrFail($kelasId);
        $waliId = $kelas->guru_id;

        foreach ($request->input('absen') as $siswaId => $row) {
            $meta = RaporMetadata::firstOrCreate(
                [
                    'tahun_ajaran_id' => $tahunId,
                    'semester' => $semester,
                    'siswa_id' => $siswaId,
                ],
                [
                    'kelas_id' => $kelas->id,
                    'wali_guru_id' => $waliId,
                    'tanggal_rapor' => now(),
                ]
            );

            $meta->kelas_id = $kelas->id;
            $meta->wali_guru_id = $waliId;
            $meta->sakit = (int) ($row['sakit'] ?? 0);
            $meta->izin = (int) ($row['izin'] ?? 0);
            $meta->alpa = (int) ($row['alpa'] ?? 0);
            $meta->save();
        }

        return back()->with('status', __('Data absen disimpan.'));
    }

    public function prestasi(Request $request): View
    {
        [$tahunId, $semester, $tahun] = $this->ensureContext();
        [$kelasList, $guru] = $this->kelasListForUser($request, $tahunId);
        $kelasId = $request->integer('kelas_id');
        $kelas = $this->findKelasOrAbort($kelasId, $kelasList);

        $siswas = collect();
        $prestasi = [];
        if ($kelas) {
            $siswas = $kelas->siswas()->orderBy('nama')->get();
            foreach ($siswas as $siswa) {
                $meta = RaporMetadata::where('tahun_ajaran_id', $tahunId)
                    ->where('semester', $semester)
                    ->where('siswa_id', $siswa->id)
                    ->first();
                $prestasi[$siswa->id] = $meta?->prestasi[0]['jenis'] ?? '';
            }
        }

        return view('rapor.prestasi', compact('kelasList', 'kelas', 'kelasId', 'tahunId', 'semester', 'tahun', 'siswas', 'prestasi'));
    }

    public function prestasiStore(Request $request): RedirectResponse
    {
        [$tahunId, $semester] = $this->ensureContext();
        $kelasId = $request->integer('kelas_id');
        if (! $kelasId) {
            return back()->withErrors(['kelas_id' => __('Pilih kelas terlebih dahulu.')]);
        }

        $request->validate([
            'prestasi' => ['required', 'array'],
            'prestasi.*' => ['nullable', 'string', 'max:255'],
        ]);

        $kelas = Kelas::findOrFail($kelasId);
        $waliId = $kelas->guru_id;

        foreach ($request->input('prestasi') as $siswaId => $val) {
            $meta = RaporMetadata::firstOrCreate(
                [
                    'tahun_ajaran_id' => $tahunId,
                    'semester' => $semester,
                    'siswa_id' => $siswaId,
                ],
                [
                    'kelas_id' => $kelas->id,
                    'wali_guru_id' => $waliId,
                    'tanggal_rapor' => now(),
                ]
            );

            $meta->kelas_id = $kelas->id;
            $meta->wali_guru_id = $waliId;

            $text = trim((string) $val);
            $meta->prestasi = $text === '' ? [] : [['jenis' => $text, 'keterangan' => null]];
            $meta->save();
        }

        return back()->with('status', __('Data prestasi disimpan.'));
    }

    public function catatan(Request $request): View
    {
        [$tahunId, $semester, $tahun] = $this->ensureContext();
        [$kelasList, $guru] = $this->kelasListForUser($request, $tahunId);
        $kelasId = $request->integer('kelas_id');
        $kelas = $this->findKelasOrAbort($kelasId, $kelasList);

        $siswas = collect();
        $catatan = [];
        if ($kelas) {
            $siswas = $kelas->siswas()->orderBy('nama')->get();
            foreach ($siswas as $siswa) {
                $meta = RaporMetadata::where('tahun_ajaran_id', $tahunId)
                    ->where('semester', $semester)
                    ->where('siswa_id', $siswa->id)
                    ->first();
                $catatan[$siswa->id] = $meta?->catatan_wali ?? '';
            }
        }

        return view('rapor.catatan', compact('kelasList', 'kelas', 'kelasId', 'tahunId', 'semester', 'tahun', 'siswas', 'catatan'));
    }

    public function catatanStore(Request $request): RedirectResponse
    {
        [$tahunId, $semester] = $this->ensureContext();
        $kelasId = $request->integer('kelas_id');
        if (! $kelasId) {
            return back()->withErrors(['kelas_id' => __('Pilih kelas terlebih dahulu.')]);
        }

        $request->validate([
            'catatan' => ['required', 'array'],
            'catatan.*' => ['nullable', 'string'],
        ]);

        $kelas = Kelas::findOrFail($kelasId);
        $waliId = $kelas->guru_id;

        foreach ($request->input('catatan') as $siswaId => $val) {
            $meta = RaporMetadata::firstOrCreate(
                [
                    'tahun_ajaran_id' => $tahunId,
                    'semester' => $semester,
                    'siswa_id' => $siswaId,
                ],
                [
                    'kelas_id' => $kelas->id,
                    'wali_guru_id' => $waliId,
                    'tanggal_rapor' => now(),
                ]
            );

            $meta->kelas_id = $kelas->id;
            $meta->wali_guru_id = $waliId;
            $meta->catatan_wali = trim((string) $val);
            $meta->save();
        }

        return back()->with('status', __('Catatan wali disimpan.'));
    }
}
