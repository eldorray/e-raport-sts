<x-layouts.app>

    @php
        $currentYear = $selectedTahunAjaran ? App\Models\TahunAjaran::find($selectedTahunAjaran) : null;
        $isActive = $currentYear?->is_active ?? false;
    @endphp

    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Dashboard') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Welcome to the dashboard') }}</p>
            @if ($waliKelasNama ?? false)
                <h1 class="text-xl font-semibold text-emerald-700 dark:text-emerald-300 mt-1">
                    Selamat datang, {{ $guruModel->nama ?? auth()->user()->name }} • Wali Kelas {{ $waliKelasNama }}
                </h1>
            @endif
        </div>
        @if ($selectedTahunAjaran)
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
            {{-- Progress Penilaian Card --}}
            @php
                $progress = $guruStats['penilaian_progress'];
                $progressColor = $progress < 50 ? 'red' : ($progress < 80 ? 'amber' : 'emerald');
                $progressBg = [
                    'red' => 'bg-red-500',
                    'amber' => 'bg-amber-500',
                    'emerald' => 'bg-emerald-500',
                ][$progressColor];
                $progressBgLight = [
                    'red' => 'bg-red-100 dark:bg-red-900/30',
                    'amber' => 'bg-amber-100 dark:bg-amber-900/30',
                    'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30',
                ][$progressColor];
                $progressText = [
                    'red' => 'text-red-600 dark:text-red-400',
                    'amber' => 'text-amber-600 dark:text-amber-400',
                    'emerald' => 'text-emerald-600 dark:text-emerald-400',
                ][$progressColor];
                $progressLabel =
                    $progress < 50
                        ? 'Perlu Perhatian'
                        : ($progress < 80
                            ? 'Dalam Proses'
                            : ($progress < 100
                                ? 'Hampir Selesai'
                                : 'Selesai'));
            @endphp
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 col-span-1 md:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Pengisian Nilai</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <p class="text-3xl font-bold {{ $progressText }}">{{ $progress }}%</p>
                            <span class="text-sm font-semibold {{ $progressText }}">{{ $progressLabel }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $penilaianFilled }} / {{ $targetPenilaian }} entri
                            nilai</p>
                    </div>
                    <div class="{{ $progressBgLight }} p-3 rounded-full">
                        @if ($progress >= 100)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $progressText }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $progressText }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        @endif
                    </div>
                </div>
                {{-- Progress Bar --}}
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="{{ $progressBg }} h-3 rounded-full transition-all duration-500 ease-out"
                        style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif
    </div>

</x-layouts.app>
