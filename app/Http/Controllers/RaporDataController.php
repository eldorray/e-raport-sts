<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RaporMetadata;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RaporDataController extends Controller
{
    /**
     * Pastikan konteks tahun ajaran & semester tersedia di session.
     *
     * @return array{0: int, 1: string, 2: TahunAjaran|null}
     */
    private function ensureContext(): array
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran & semester terlebih dahulu di dashboard.'));
        }

        /** @var TahunAjaran|null $tahun */
        $tahun = TahunAjaran::find($tahunId);

        return [$tahunId, $semester, $tahun];
    }

    /**
     * Daftar kelas yang boleh diakses user pada tahun ajaran tertentu.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Collection<int, Kelas>, 1: Guru|null}
     */
    private function kelasListForUser(Request $request, int $tahunId): array
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

            // Guru bukan wali kelas tidak boleh mengakses data rapor wali
            if (! $kelasQuery->exists()) {
                abort(403, __('Anda bukan wali kelas pada tahun ajaran ini.'));
            }
        }

        return [$kelasQuery->get(), $guru];
    }

    /**
     * Temukan kelas dari daftar berdasarkan ID, atau ambil yang pertama.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Kelas>  $kelasList
     */
    private function findKelasOrAbort(?int $kelasId, \Illuminate\Database\Eloquent\Collection $kelasList): ?Kelas
    {
        if (! $kelasId) {
            return $kelasList->first();
        }

        return $kelasList->firstWhere('id', $kelasId);
    }

    /**
     * Pastikan user boleh menyimpan data untuk kelas ini.
     * Admin selalu boleh; guru harus menjadi wali kelas dari kelas tersebut.
     */
    private function authorizeKelasStore(Request $request, Kelas $kelas): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        $guru = Guru::where('user_id', $request->user()->id)->first();

        if (! $guru || $kelas->guru_id !== $guru->id) {
            abort(403, __('Anda bukan wali kelas untuk kelas ini.'));
        }
    }

    /**
     * Cek apakah form disimpan pada konteks yang berbeda dengan session.
     *
     * Terjadi bila tahun ajaran/semester diganti di tab lain setelah form dibuka:
     * kelas milik tahun ajaran lain, atau field tersembunyi `tahun_ajaran_id`/`semester`
     * (opsional) dari form berbeda dengan session.
     */
    private function isKonteksBerubah(Request $request, Kelas $kelas, int $tahunId, string $semester): bool
    {
        if ((int) $kelas->tahun_ajaran_id !== $tahunId) {
            return true;
        }

        if ($request->filled('tahun_ajaran_id') && $request->integer('tahun_ajaran_id') !== $tahunId) {
            return true;
        }

        return $request->filled('semester')
            && strcasecmp(trim((string) $request->input('semester')), trim($semester)) !== 0;
    }

    /**
     * Redirect kembali dengan pesan bahwa konteks sudah diganti di tab lain.
     */
    private function konteksBerubahResponse(): RedirectResponse
    {
        return back()
            ->withErrors(['tahun_ajaran' => __('Tahun ajaran atau semester sudah diganti di tab lain. Muat ulang halaman ini, lalu simpan lagi.')])
            ->withInput();
    }

    public function absen(Request $request): View
    {
        [$tahunId, $semester, $tahun] = $this->ensureContext();
        [$kelasList, $guru] = $kelasListAndGuru = $this->kelasListForUser($request, $tahunId);
        $kelasId = $request->integer('kelas_id');
        $kelas = $this->findKelasOrAbort($kelasId, $kelasList);

        // Check if tahun ajaran is active (guru can only edit on active tahun ajaran)
        $isAdmin = $request->user()->role === 'admin';
        $canEdit = $isAdmin || ($tahun && $tahun->is_active);

        $siswas = collect();
        $absen = [];
        if ($kelas) {
            $siswas = $kelas->siswas()->orderBy('nama')->get();
            foreach ($siswas as $siswa) {
                /** @var \App\Models\RaporMetadata|null $meta */
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

        return view('rapor.absen', compact('kelasList', 'kelas', 'kelasId', 'tahunId', 'semester', 'tahun', 'siswas', 'absen', 'canEdit'));
    }

    public function absenStore(Request $request): RedirectResponse
    {
        [$tahunId, $semester] = $this->ensureContext();

        // Block guru from editing inactive tahun ajaran
        if ($request->user()->role !== 'admin') {
            $tahun = TahunAjaran::find($tahunId);
            if (! $tahun || ! $tahun->is_active) {
                return back()
                    ->withErrors(['tahun_ajaran' => __('Tidak dapat menyimpan data pada tahun ajaran yang tidak aktif.')])
                    ->withInput();
            }
        }

        $kelasId = $request->integer('kelas_id');
        if (! $kelasId) {
            return back()->withErrors(['kelas_id' => __('Pilih kelas terlebih dahulu.')])->withInput();
        }

        $request->validate([
            'absen' => ['required', 'array'],
            'absen.*.sakit' => ['nullable', 'integer', 'min:0', 'max:255'],
            'absen.*.izin' => ['nullable', 'integer', 'min:0', 'max:255'],
            'absen.*.alpa' => ['nullable', 'integer', 'min:0', 'max:255'],
        ]);

        $kelas = Kelas::findOrFail($kelasId);
        $this->authorizeKelasStore($request, $kelas);

        if ($this->isKonteksBerubah($request, $kelas, $tahunId, $semester)) {
            return $this->konteksBerubahResponse();
        }

        $waliId = $kelas->guru_id;

        // Hanya izinkan siswa yang memang anggota kelas ini
        $validSiswaIds = $kelas->siswas()->pluck('id');

        DB::transaction(function () use ($request, $validSiswaIds, $tahunId, $semester, $kelas, $waliId) {
            foreach ($request->input('absen') as $siswaId => $row) {
                if (! $validSiswaIds->contains((int) $siswaId)) {
                    continue;
                }

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
        });

        return back()->with('status', __('Data absen disimpan.'));
    }

    public function prestasi(Request $request): View
    {
        [$tahunId, $semester, $tahun] = $this->ensureContext();
        [$kelasList, $guru] = $this->kelasListForUser($request, $tahunId);
        $kelasId = $request->integer('kelas_id');
        $kelas = $this->findKelasOrAbort($kelasId, $kelasList);

        // Check if tahun ajaran is active (guru can only edit on active tahun ajaran)
        $isAdmin = $request->user()->role === 'admin';
        $canEdit = $isAdmin || ($tahun && $tahun->is_active);

        $siswas = collect();
        $prestasi = [];
        if ($kelas) {
            $siswas = $kelas->siswas()->orderBy('nama')->get();
            foreach ($siswas as $siswa) {
                /** @var \App\Models\RaporMetadata|null $meta */
                $meta = RaporMetadata::where('tahun_ajaran_id', $tahunId)
                    ->where('semester', $semester)
                    ->where('siswa_id', $siswa->id)
                    ->first();
                $prestasi[$siswa->id] = $meta?->prestasi[0]['jenis'] ?? '';
            }
        }

        return view('rapor.prestasi', compact('kelasList', 'kelas', 'kelasId', 'tahunId', 'semester', 'tahun', 'siswas', 'prestasi', 'canEdit'));
    }

    public function prestasiStore(Request $request): RedirectResponse
    {
        [$tahunId, $semester] = $this->ensureContext();

        // Block guru from editing inactive tahun ajaran
        if ($request->user()->role !== 'admin') {
            $tahun = TahunAjaran::find($tahunId);
            if (! $tahun || ! $tahun->is_active) {
                return back()
                    ->withErrors(['tahun_ajaran' => __('Tidak dapat menyimpan data pada tahun ajaran yang tidak aktif.')])
                    ->withInput();
            }
        }

        $kelasId = $request->integer('kelas_id');
        if (! $kelasId) {
            return back()->withErrors(['kelas_id' => __('Pilih kelas terlebih dahulu.')])->withInput();
        }

        $request->validate([
            'prestasi' => ['required', 'array'],
            'prestasi.*' => ['nullable', 'string', 'max:255'],
        ]);

        $kelas = Kelas::findOrFail($kelasId);
        $this->authorizeKelasStore($request, $kelas);

        if ($this->isKonteksBerubah($request, $kelas, $tahunId, $semester)) {
            return $this->konteksBerubahResponse();
        }

        $waliId = $kelas->guru_id;

        // Hanya izinkan siswa yang memang anggota kelas ini
        $validSiswaIds = $kelas->siswas()->pluck('id');

        DB::transaction(function () use ($request, $validSiswaIds, $tahunId, $semester, $kelas, $waliId) {
            foreach ($request->input('prestasi') as $siswaId => $val) {
                if (! $validSiswaIds->contains((int) $siswaId)) {
                    continue;
                }

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
        });

        return back()->with('status', __('Data prestasi disimpan.'));
    }

    public function catatan(Request $request): View
    {
        [$tahunId, $semester, $tahun] = $this->ensureContext();
        [$kelasList, $guru] = $this->kelasListForUser($request, $tahunId);
        $kelasId = $request->integer('kelas_id');
        $kelas = $this->findKelasOrAbort($kelasId, $kelasList);

        // Check if tahun ajaran is active (guru can only edit on active tahun ajaran)
        $isAdmin = $request->user()->role === 'admin';
        $canEdit = $isAdmin || ($tahun && $tahun->is_active);

        $siswas = collect();
        $catatan = [];
        if ($kelas) {
            $siswas = $kelas->siswas()->orderBy('nama')->get();
            foreach ($siswas as $siswa) {
                /** @var \App\Models\RaporMetadata|null $meta */
                $meta = RaporMetadata::where('tahun_ajaran_id', $tahunId)
                    ->where('semester', $semester)
                    ->where('siswa_id', $siswa->id)
                    ->first();
                $catatan[$siswa->id] = $meta?->catatan_wali ?? '';
            }
        }

        return view('rapor.catatan', compact('kelasList', 'kelas', 'kelasId', 'tahunId', 'semester', 'tahun', 'siswas', 'catatan', 'canEdit'));
    }

    public function catatanStore(Request $request): RedirectResponse
    {
        [$tahunId, $semester] = $this->ensureContext();

        // Block guru from editing inactive tahun ajaran
        if ($request->user()->role !== 'admin') {
            $tahun = TahunAjaran::find($tahunId);
            if (! $tahun || ! $tahun->is_active) {
                return back()
                    ->withErrors(['tahun_ajaran' => __('Tidak dapat menyimpan data pada tahun ajaran yang tidak aktif.')])
                    ->withInput();
            }
        }

        $kelasId = $request->integer('kelas_id');
        if (! $kelasId) {
            return back()->withErrors(['kelas_id' => __('Pilih kelas terlebih dahulu.')])->withInput();
        }

        $request->validate([
            'catatan' => ['required', 'array'],
            'catatan.*' => ['nullable', 'string'],
        ]);

        $kelas = Kelas::findOrFail($kelasId);
        $this->authorizeKelasStore($request, $kelas);

        if ($this->isKonteksBerubah($request, $kelas, $tahunId, $semester)) {
            return $this->konteksBerubahResponse();
        }

        $waliId = $kelas->guru_id;

        // Hanya izinkan siswa yang memang anggota kelas ini
        $validSiswaIds = $kelas->siswas()->pluck('id');

        DB::transaction(function () use ($request, $validSiswaIds, $tahunId, $semester, $kelas, $waliId) {
            foreach ($request->input('catatan') as $siswaId => $val) {
                if (! $validSiswaIds->contains((int) $siswaId)) {
                    continue;
                }

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
        });

        return back()->with('status', __('Catatan wali disimpan.'));
    }
}
