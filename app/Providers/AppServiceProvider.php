<?php

namespace App\Providers;

use App\Models\Guru;
use App\Models\Ekskul;
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

            $assignments = Mengajar::with(['kelas', 'mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunId)
                ->when($semester, fn ($q) => $q->where('semester', $semester))
                ->orderBy('mata_pelajaran_id')
                ->orderBy('kelas_id')
                ->get()
                ->map(function ($m) use ($tahunId, $semester) {
                    $target = $m->kelas?->siswas?->count() ?? 0;
                    $filled = Penilaian::where('mengajar_id', $m->id)
                        ->where('tahun_ajaran_id', $tahunId)
                        ->when($semester, fn ($q) => $q->where('semester', $semester))
                        ->whereNotNull('nilai_sumatif')
                        ->distinct('siswa_id')
                        ->count('siswa_id');

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

            $view->with('sidebarAssignments', $assignments);
            $view->with('sidebarMapelStatus', $mapelStatus);
            $view->with('sidebarEkskul', $ekskulAssignments);
            $view->with('isWaliKelas', $isWaliKelas);
        });
    }
}
