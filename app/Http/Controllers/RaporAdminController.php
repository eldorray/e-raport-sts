<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\PrintSetting;
use App\Models\SchoolProfile;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaporAdminController extends Controller
{
    public function index(Request $request): View
    {
        $tahunId = session('selected_tahun_ajaran_id');
        $semester = session('selected_semester');

        $role = $request->user()->role ?? null;
        $guru = null;
        if ($role === 'guru') {
            $guru = Guru::where('user_id', Auth::id())->first();
            if (! $guru) {
                abort(403, __('Akun Anda belum terhubung dengan data guru.'));
            }
        }

        $tingkat = $request->input('tingkat');
        $kelasId = $request->input('kelas_id');

        $kelasQuery = Kelas::query()->orderBy('nama');
        if ($tingkat) {
            $kelasQuery->where('tingkat', $tingkat);
        }
        if ($guru) {
            $kelasQuery->where('guru_id', $guru->id);
            if (! $kelasId) {
                $kelasId = $kelasQuery->first()?->id;
            }
        }
        $kelasList = $kelasQuery->get();

        if ($role === 'guru' && ! $kelasId && $kelasList->isNotEmpty()) {
            $kelasId = $kelasList->first()->id;
        }

        $tingkatOptions = Kelas::query()->select('tingkat')->whereNotNull('tingkat')->distinct()->orderBy('tingkat')->pluck('tingkat');

        $siswas = Siswa::with('kelas')
            ->when($tingkat, fn ($q) => $q->whereHas('kelas', fn ($qq) => $qq->where('tingkat', $tingkat)))
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->when($guru, function ($q) use ($guru) {
                $q->whereHas('kelas', function ($qq) use ($guru) {
                    $qq->where('guru_id', $guru->id);
                });
            })
            ->orderBy('nama')
            ->get();

        return view('rapor.index', [
            'siswas' => $siswas,
            'kelasList' => $kelasList,
            'tingkatOptions' => $tingkatOptions,
            'tingkat' => $tingkat,
            'kelasId' => $kelasId,
            'tahunId' => $tahunId,
            'semester' => $semester,
            'role' => $role,
            'isGuru' => $role === 'guru',
        ]);
    }

    public function ledger(Request $request, Kelas $kelas): View
    {
        $tahunId = $request->integer('tahun_ajaran_id') ?: session('selected_tahun_ajaran_id');
        $semester = $request->input('semester') ?: session('selected_semester');
        if (! $tahunId || ! $semester) {
            abort(422, __('Pilih tahun ajaran dan semester terlebih dahulu.'));
        }

        $tahun = TahunAjaran::find($tahunId);

        $user = $request->user();
        $role = $user->role ?? null;
        if ($role === 'guru' && $kelas->guru_id !== optional(Guru::where('user_id', $user->id)->first())->id) {
            abort(403, __('Anda tidak memiliki akses ke kelas ini.'));
        }

        $siswas = $kelas->siswas()->orderBy('nama')->get();

        $mapels = Mengajar::with(['mataPelajaran', 'guru.user'])
            ->where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $tahunId)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->orderBy('mata_pelajaran_id')
            ->get();

        $nilai = [];
        foreach ($siswas as $siswa) {
            $row = ['siswa' => $siswa, 'mapels' => [], 'total' => 0, 'count' => 0];
            foreach ($mapels as $mengajar) {
                $penilaian = Penilaian::where('mengajar_id', $mengajar->id)
                    ->where('siswa_id', $siswa->id)
                    ->where('tahun_ajaran_id', $tahunId)
                    ->when($semester, fn ($q) => $q->where('semester', $semester))
                    ->first();

                $guruUser = $mengajar->guru?->user;
                $bobotSumatif = $guruUser?->bobot_sumatif ?? (float) config('rapor.bobot_sumatif', 50);
                $bobotSts = $guruUser?->bobot_sts ?? (float) config('rapor.bobot_sts', 50);

                $rapor = null;
                if ($penilaian && $penilaian->nilai_sumatif !== null && $penilaian->nilai_sts !== null && abs(($bobotSumatif + $bobotSts) - 100) <= 0.01) {
                    $rapor = round((($penilaian->nilai_sumatif * $bobotSumatif) + ($penilaian->nilai_sts * $bobotSts)) / 100);
                }

                $row['mapels'][$mengajar->mata_pelajaran_id] = $rapor;
                if ($rapor !== null) {
                    $row['total'] += $rapor;
                    $row['count'] += 1;
                }
            }
            $nilai[] = $row;
        }

        $school = SchoolProfile::first();
        $printSetting = PrintSetting::first();

        $printPlace = $printSetting?->tempat_cetak
            ?? $school?->city
            ?? 'Tangerang';

        $printDate = $printSetting?->tanggal_cetak ?? now();

        $watermarkText = null;
        if ($printSetting) {
            $watermarkText = trim((string) $printSetting->watermark);
            if ($watermarkText === '') {
                $watermarkText = null;
            }
        } else {
            $watermarkText = $school?->name ?: 'MI Daarul Hikmah';
        }

        $watermarkDataUrl = null;
        if ($watermarkText) {
            $svg = sprintf(
                "<svg xmlns='http://www.w3.org/2000/svg' width='150' height='100' viewBox='0 0 150 100'><text x='0' y='30' fill='#2e6b3a' font-size='14' font-family='Times New Roman,serif' transform='rotate(30 0 30)'>%s</text></svg>",
                $watermarkText,
            );
            $watermarkDataUrl = 'data:image/svg+xml,'.rawurlencode($svg);
        }

        return view('rapor.ledger', [
            'kelas' => $kelas,
            'tahun' => $tahun,
            'semester' => $semester,
            'mapels' => $mapels,
            'nilai' => $nilai,
            'school' => $school,
            'printPlace' => $printPlace,
            'printDate' => $printDate,
            'watermarkDataUrl' => $watermarkDataUrl,
        ]);
    }
}
