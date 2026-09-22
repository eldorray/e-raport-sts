<?php

namespace App\Providers;

use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\Mengajar;
use App\Models\Penilaian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app.sidebar', function ($view) {
            $user = Auth::user();

            if (! $user || $user->role !== 'guru') {
                $view->with('sidebarAssignments', collect());
                $view->with('sidebarEkskul', collect());

                return;
            }

            $guru = Guru::where('user_id', $user->id)->first();
            $tahunId = session('selected_tahun_ajaran_id');
            $semester = session('selected_semester');

            if (! $guru || ! $tahunId) {
                $view->with('sidebarAssignments', collect());
                $view->with('sidebarEkskul', collect());

                return;
            }

            $mengajarList = Mengajar::with(['kelas.siswas', 'mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunId)
                ->when($semester, fn ($q) => $q->where('semester', $semester))
                ->orderBy('mata_pelajaran_id')
                ->orderBy('kelas_id')
                ->get();

            // Ambil jumlah siswa yang sudah dinilai per mengajar dalam SATU query
            // (menghindari N+1 query count per mengajar di dalam loop).
            $filledCounts = Penilaian::where('tahun_ajaran_id', $tahunId)
                ->when($semester, fn ($q) => $q->where('semester', $semester))
                ->whereIn('mengajar_id', $mengajarList->pluck('id'))
                ->whereNotNull('nilai_sumatif')
                ->select('mengajar_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT siswa_id) as filled'))
                ->groupBy('mengajar_id')
                ->pluck('filled', 'mengajar_id');

            $assignments = $mengajarList
                ->map(function ($m) use ($filledCounts) {
                    $target = $m->kelas?->siswas?->count() ?? 0;
                    $filled = (int) ($filledCounts->get($m->id) ?? 0);

                    $m->penilaian_target = $target;
                    $m->penilaian_filled = $filled;
                    $m->penilaian_done = $target > 0 && $filled >= $target;

                    return $m;
                })
                ->groupBy('mata_pelajaran_id');

            $mapelStatus = $assignments->map(function ($items) {
                // done only if all classes for the mapel have all their students graded
                return $items->every(function ($m) {
                    $target = $m->penilaian_target ?? 0;
                    $filled = $m->penilaian_filled ?? 0;

                    return $target > 0 && $filled >= $target;
                });
            });

            $ekskulAssignments = Ekskul::where('guru_id', $guru->id)
                ->orderBy('nama')
                ->get();

            // Check apakah guru adalah wali kelas pada tahun ajaran yang dipilih
            $isWaliKelas = \App\Models\Kelas::where('guru_id', $guru->id)
                ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                ->exists();

            // Check apakah guru ada assignment tahfidz
            $hasTahfidzAssignment = \App\Models\MengajarTahfidz::where('guru_id', $guru->id)
                ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                ->when($semester, fn ($q) => $q->where('semester', $semester))
                ->exists();

            $view->with('sidebarAssignments', $assignments);
            $view->with('sidebarMapelStatus', $mapelStatus);
            $view->with('sidebarEkskul', $ekskulAssignments);
            $view->with('isWaliKelas', $isWaliKelas);
            $view->with('hasTahfidzAssignment', $hasTahfidzAssignment);
        });
    }
}
