<?php

namespace App\Providers;

use App\Models\Guru;
use App\Models\Ekskul;
use App\Models\Mengajar;
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
                ->groupBy('mata_pelajaran_id');

            $ekskulAssignments = Ekskul::where('guru_id', $guru->id)
                ->orderBy('nama')
                ->get();

            $view->with('sidebarAssignments', $assignments);
            $view->with('sidebarEkskul', $ekskulAssignments);
        });
    }
}
