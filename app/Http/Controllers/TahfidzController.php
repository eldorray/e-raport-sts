<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MengajarTahfidz;
use App\Models\Siswa;
use App\Models\TahfidzPenilaian;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mengelola penilaian Tahfidz Al-Qur'an.
 */
class TahfidzController extends Controller
{
    /**
     * Menampilkan daftar siswa untuk input penilaian tahfidz.
     */
    public function index(Request $request): View
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        $guru = null;
        $kelasIds = [];

        if (! $isAdmin) {
            $guru = Guru::where('user_id', $user->id)->first();
            if (! $guru) {
                abort(403, __('Akun Anda belum terhubung dengan data guru.'));
            }

            // Get kelas yang di-assign ke guru ini
            $kelasIds = MengajarTahfidz::where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunId)
                ->where('semester', $semester)
                ->pluck('kelas_id')
                ->toArray();

            if (empty($kelasIds)) {
                abort(403, __('Anda tidak memiliki assignment tahfidz untuk semester ini.'));
            }
        }

        $kelasId = $request->input('kelas_id');

        // Query kelas berdasarkan role
        $kelasList = Kelas::where('tahun_ajaran_id', $tahunId)
            ->when(! $isAdmin, fn ($q) => $q->whereIn('id', $kelasIds))
            ->orderBy('nama')
            ->get();

        // Jika guru dan belum pilih kelas, auto-select kelas pertama
        if (! $isAdmin && ! $kelasId && $kelasList->isNotEmpty()) {
            $kelasId = $kelasList->first()->id;
        }

        $siswas = collect();
        $selectedKelas = null;

        if ($kelasId) {
            // Validasi akses kelas untuk guru
            if (! $isAdmin && ! in_array($kelasId, $kelasIds)) {
                abort(403, __('Anda tidak memiliki akses ke kelas ini.'));
            }

            $selectedKelas = Kelas::find($kelasId);
            $siswas = Siswa::where('kelas_id', $kelasId)
                ->where('is_active', true)
                ->orderBy('nama')
                ->get()
                ->map(function ($siswa) use ($tahunId, $semester) {
                    $penilaian = TahfidzPenilaian::where('siswa_id', $siswa->id)
                        ->where('tahun_ajaran_id', $tahunId)
                        ->where('semester', $semester)
                        ->first();
                    $siswa->tahfidz = $penilaian;
                    $siswa->jumlah_surah_30 = $penilaian?->jumlah_surah_juz30 ?? 0;
                    $siswa->jumlah_surah_29 = $penilaian?->jumlah_surah_juz29 ?? 0;
                    return $siswa;
                });
        }

        $pembimbingList = Guru::where('is_active', true)->orderBy('nama')->get();

        // Check if tahun ajaran is active (guru can only edit on active tahun ajaran)
        $tahunAjaran = TahunAjaran::find($tahunId);
        $canEdit = $isAdmin || ($tahunAjaran && $tahunAjaran->is_active);

        return view('tahfidz.index', compact(
            'kelasList',
            'siswas',
            'selectedKelas',
            'kelasId',
            'pembimbingList',
            'tahunId',
            'semester',
            'isAdmin',
            'guru',
            'canEdit'
        ));
    }

    /**
     * Menampilkan form penilaian tahfidz per siswa.
     */
    public function show(Request $request, Siswa $siswa): View
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        // Authorize access
        $this->authorizeAccess($request, $siswa, $tahunId, $semester);

        $penilaian = TahfidzPenilaian::firstOrNew([
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $tahunId,
            'semester' => $semester,
        ]);

        // Jika guru, pre-fill pembimbing_id dengan guru yang login
        $user = $request->user();
        $guru = null;
        if ($user->role !== 'admin') {
            $guru = Guru::where('user_id', $user->id)->first();
            if ($guru && ! $penilaian->pembimbing_id) {
                $penilaian->pembimbing_id = $guru->id;
            }
        }

        $pembimbingList = Guru::where('is_active', true)->orderBy('nama')->get();
        $surahList = TahfidzPenilaian::SURAH_LIST;
        $surahListJuz29 = TahfidzPenilaian::SURAH_LIST_JUZ29;
        $predikatList = TahfidzPenilaian::PREDIKAT_MAP;

        // Determine which juz is being edited (default: 30)
        $juz = (int) $request->input('juz', 30);
        if (! in_array($juz, [29, 30])) {
            $juz = 30;
        }

        // Check if tahun ajaran is active (guru can only edit on active tahun ajaran)
        $isAdmin = $user->role === 'admin';
        $tahunAjaran = TahunAjaran::find($tahunId);
        $canEdit = $isAdmin || ($tahunAjaran && $tahunAjaran->is_active);

        return view('tahfidz.input', compact(
            'siswa',
            'penilaian',
            'pembimbingList',
            'surahList',
            'surahListJuz29',
            'predikatList',
            'tahunId',
            'semester',
            'canEdit',
            'juz'
        ));
    }

    /**
     * Menyimpan penilaian tahfidz.
     */
    public function store(Request $request, Siswa $siswa)
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        // Authorize access
        $this->authorizeAccess($request, $siswa, $tahunId, $semester);

        // Block guru from editing inactive tahun ajaran
        $user = $request->user();
        if ($user->role !== 'admin') {
            $tahunAjaran = TahunAjaran::find($tahunId);
            if (! $tahunAjaran || ! $tahunAjaran->is_active) {
                return back()->withErrors(['tahun_ajaran' => __('Tidak dapat menyimpan data pada tahun ajaran yang tidak aktif.')]);
            }
        }

        $juz = (int) $request->input('juz', 30);
        if (! in_array($juz, [29, 30])) {
            $juz = 30;
        }

        // Common validation rules
        $rules = [
            'pembimbing_id' => 'nullable|exists:gurus,id',
            'predikat_adab' => 'nullable|in:A,B,C,D',
            'deskripsi_adab' => 'nullable|string|max:100',
            'predikat_tajwid' => 'nullable|in:A,B,C,D',
            'deskripsi_tajwid' => 'nullable|string|max:100',
            'predikat_makhorijul' => 'nullable|in:A,B,C,D',
            'deskripsi_makhorijul' => 'nullable|string|max:100',
        ];

        // Add juz-specific validation
        if ($juz === 30) {
            $rules['surah_hafalan'] = 'nullable|array';
            $rules['surah_hafalan.*'] = 'string|in:' . implode(',', array_keys(TahfidzPenilaian::SURAH_LIST));
        } else {
            $rules['surah_hafalan_29'] = 'nullable|array';
            $rules['surah_hafalan_29.*'] = 'string|in:' . implode(',', array_keys(TahfidzPenilaian::SURAH_LIST_JUZ29));
        }

        $validated = $request->validate($rules);

        // Ensure surah_hafalan is always an array (empty array if no checkboxes selected)
        if ($juz === 30) {
            $validated['surah_hafalan'] = $validated['surah_hafalan'] ?? [];
        } else {
            $validated['surah_hafalan_29'] = $validated['surah_hafalan_29'] ?? [];
        }

        TahfidzPenilaian::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tahun_ajaran_id' => $tahunId,
                'semester' => $semester,
            ],
            $validated
        );

        return redirect()
            ->route('tahfidz.index', ['kelas_id' => $siswa->kelas_id])
            ->with('success', __('Penilaian tahfidz Juz :juz berhasil disimpan.', ['juz' => $juz]));
    }

    /**
     * Reset penilaian tahfidz siswa.
     */
    public function reset(Request $request, Siswa $siswa)
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        // Authorize access
        $this->authorizeAccess($request, $siswa, $tahunId, $semester);

        // Block guru from resetting inactive tahun ajaran
        $user = $request->user();
        if ($user->role !== 'admin') {
            $tahunAjaran = TahunAjaran::find($tahunId);
            if (! $tahunAjaran || ! $tahunAjaran->is_active) {
                return back()->withErrors(['tahun_ajaran' => __('Tidak dapat mereset data pada tahun ajaran yang tidak aktif.')]);
            }
        }

        TahfidzPenilaian::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->delete();

        return redirect()
            ->route('tahfidz.show', $siswa)
            ->with('success', __('Penilaian tahfidz berhasil direset.'));
    }

    /**
     * Memeriksa akses guru terhadap siswa berdasarkan assignment tahfidz.
     */
    private function authorizeAccess(Request $request, Siswa $siswa, int $tahunId, string $semester): void
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return;
        }

        $guru = Guru::where('user_id', $user->id)->first();
        if (! $guru) {
            abort(403, __('Akun Anda belum terhubung dengan data guru.'));
        }

        $hasAccess = MengajarTahfidz::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->where('semester', $semester)
            ->where('kelas_id', $siswa->kelas_id)
            ->exists();

        if (! $hasAccess) {
            abort(403, __('Anda tidak memiliki akses ke siswa ini.'));
        }
    }
}
