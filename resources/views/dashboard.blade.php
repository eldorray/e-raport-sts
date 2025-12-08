<x-layouts.app>

    @php
        $sessionYearId = session('selected_tahun_ajaran_id');
        $sessionSemester = session('selected_semester');
        $fallbackYearId = App\Models\TahunAjaran::where('is_active', true)->value('id');
        $selectedTahunAjaran = $sessionYearId ?: $fallbackYearId;
        $yearModel = $selectedTahunAjaran ? App\Models\TahunAjaran::find($selectedTahunAjaran) : null;
        $selectedSemester = $sessionSemester ?? $yearModel?->semester;
        $tahunAjaranOptions = App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();
        $isAdmin = auth()->user()->role === 'admin';
        $isGuru = auth()->user()->role === 'guru';
        $waliKelasNama = null;

        $adminStats = null;
        $guruStats = null;

        if ($isAdmin) {
            $adminStats = [
                'siswa' => \App\Models\Siswa::count(),
                'guru' => \App\Models\Guru::count(),
                'mapel' => \App\Models\MataPelajaran::count(),
                'rombel' => \App\Models\Kelas::when(
                    $selectedTahunAjaran,
                    fn($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran),
                )->count(),
            ];
        }

        if ($isGuru) {
            $guruModel = \App\Models\Guru::where('user_id', auth()->id())->first();
            if ($guruModel) {
                $waliKelasNama = \App\Models\Kelas::where('guru_id', $guruModel->id)
                    ->when($selectedTahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran))
                    ->value('nama');
            }
            $mengajarList = collect();
            if ($guruModel) {
                $mengajarList = \App\Models\Mengajar::with('kelas.siswas')
                    ->where('guru_id', $guruModel->id)
                    ->when($selectedTahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran))
                    ->when($selectedSemester, fn($q) => $q->where('semester', $selectedSemester))
                    ->get();
            }

            $mapelDiampu = $mengajarList->pluck('mata_pelajaran_id')->unique()->count();

            // Hitung siswa yang diampu berdasarkan kelas dari jadwal mengajar (bukan dari entri penilaian)
            $totalSiswaGuru = $mengajarList
                ->flatMap(fn($m) => $m->kelas?->siswas ?? collect())
                ->pluck('id')
                ->unique()
                ->count();
            $penilaianFilled = 0;
            if ($guruModel) {
                $penilaianFilled = \App\Models\Penilaian::where('guru_id', $guruModel->id)
                    ->when($selectedTahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaran))
                    ->when($selectedSemester, fn($q) => $q->where('semester', $selectedSemester))
                    ->count();
            }
            $progress = $totalSiswaGuru > 0 ? round(min(100, ($penilaianFilled / $totalSiswaGuru) * 100)) : 0;

            $guruStats = [
                'mapel_diampu' => $mapelDiampu,
                'siswa' => $totalSiswaGuru,
                'penilaian_progress' => $progress,
            ];
        }
    @endphp

    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Dashboard') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Welcome to the dashboard') }}</p>
            @if ($waliKelasNama)
                <h1 class="text-xl font-semibold text-emerald-700 dark:text-emerald-300 mt-1">
                    Selamat datang, {{ $guruModel->nama ?? auth()->user()->name }} • Wali Kelas {{ $waliKelasNama }}
                </h1>
            @endif
        </div>
        @if ($selectedTahunAjaran)
            @php
                $currentYear = App\Models\TahunAjaran::find($selectedTahunAjaran);
                $isActive = $currentYear?->is_active ?? false;
            @endphp
            <div class="flex flex-col gap-2">
                <div
                    class="inline-flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 shadow-sm dark:border-blue-800 dark:bg-blue-900/40 dark:text-blue-100">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/80 text-blue-600 shadow-sm dark:bg-blue-800/60 dark:text-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M8 7h12M8 12h12m-5 5h5M4 7h.01M4 12h.01M4 17h.01" />
                            </svg>
                        </div>
                        <div class="flex flex-col leading-tight">
                            <span
                                class="text-[11px] uppercase tracking-wide text-blue-500 dark:text-blue-200">{{ __('Tahun Ajaran Saat Ini') }}</span>
                            <span class="text-base font-bold">
                                {{ $currentYear->nama ?? __('Tidak ditemukan') }}
                                @if ($selectedSemester)
                                    • {{ $selectedSemester }}
                                @endif
                            </span>
                            <span class="text-[11px] font-medium text-blue-500/80 dark:text-blue-200/80">
                                {{ $isActive ? __('Status: Aktif') : __('Status: Nonaktif') }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('tahun-ajaran.switch-session') }}"
                        class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="tahun_ajaran_id"
                            class="rounded-lg border border-blue-200 bg-white px-2 py-1 text-xs font-semibold text-blue-700 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300/50 dark:border-blue-700 dark:bg-blue-900/60 dark:text-blue-100">
                            @foreach ($tahunAjaranOptions as $option)
                                <option value="{{ $option->id }}" @selected($option->id === $selectedTahunAjaran)>
                                    {{ $option->nama }} {{ $option->is_active ? '• Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/60">
                            {{ __('Ganti') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        @if ($isAdmin && $adminStats)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Siswa</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $adminStats['siswa'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Semua tahun ajaran</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Guru</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $adminStats['guru'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Semua guru terdaftar</p>
                    </div>
                    <div class="bg-emerald-100 dark:bg-emerald-900 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A7 7 0 1116.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Mapel</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $adminStats['mapel'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Semua mata pelajaran</p>
                    </div>
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Rombel</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $adminStats['rombel'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Kelas pada tahun dipilih</p>
                    </div>
                    <div class="bg-orange-100 dark:bg-orange-900 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500 dark:text-orange-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </div>
                </div>
            </div>
        @elseif ($isGuru && $guruStats)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mapel Diampu</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">
                            {{ $guruStats['mapel_diampu'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Tahun & semester dipilih</p>
                    </div>
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Siswa</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $guruStats['siswa'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Di kelas yang diajar</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pengisian Penilaian</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">
                            {{ $guruStats['penilaian_progress'] }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Berdasarkan entri penilaian</p>
                    </div>
                    <div class="bg-emerald-100 dark:bg-emerald-900 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
        @endif
    </div>

</x-layouts.app>
