<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
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
        ]);
    }
}
