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
                <a href="{{ route('wali-kelas.siswa.index') }}"
                    class="inline-flex items-center gap-2 mt-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 max-md:mt-1 max-md:min-h-11">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ __('Kelola Siswa Kelas Saya') }}
                </a>
            @endif
        </div>
        @if ($selectedTahunAjaran)
            <div class="flex flex-col gap-2 max-md:mt-2">
                <div
                    class="inline-flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 shadow-sm max-md:flex max-md:flex-col max-md:items-stretch dark:border-blue-800 dark:bg-blue-900/40 dark:text-blue-100">
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
                        class="flex items-center gap-2 max-md:w-full">
                        @csrf
                        @method('PATCH')
                        <select name="tahun_ajaran_id" aria-label="{{ __('Tahun Ajaran') }}"
                            class="rounded-lg border border-blue-200 bg-white px-2 py-1 text-xs font-semibold text-blue-700 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300/50 max-md:h-11 max-md:min-w-0 max-md:flex-1 max-md:px-3 max-md:py-0 max-md:text-base dark:border-blue-700 dark:bg-blue-900/60 dark:text-blue-100">
                            @foreach ($tahunAjaranOptions as $option)
                                <option value="{{ $option->id }}" @selected($option->id === $selectedTahunAjaran)>
                                    {{ $option->nama }} - {{ $option->semester }}
                                    {{ $option->is_active ? '• Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/60 max-md:h-11 max-md:shrink-0 max-md:rounded-lg max-md:px-4 max-md:text-sm">
                            {{ __('Ganti') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 max-md:grid-cols-2 max-md:gap-3 max-md:mb-4">
        @if ($isAdmin && $adminStats)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 max-md:min-w-0 max-md:p-4">
                <div class="flex items-center justify-between max-md:flex-col-reverse max-md:items-start max-md:gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Siswa</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1 max-md:text-2xl">{{ $adminStats['siswa'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Semua tahun ajaran</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full max-md:p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 max-md:min-w-0 max-md:p-4">
                <div class="flex items-center justify-between max-md:flex-col-reverse max-md:items-start max-md:gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Guru</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1 max-md:text-2xl">{{ $adminStats['guru'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Semua guru terdaftar</p>
                    </div>
                    <div class="bg-emerald-100 dark:bg-emerald-900 p-3 rounded-full max-md:p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A7 7 0 1116.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 max-md:min-w-0 max-md:p-4">
                <div class="flex items-center justify-between max-md:flex-col-reverse max-md:items-start max-md:gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Mapel</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1 max-md:text-2xl">{{ $adminStats['mapel'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Semua mata pelajaran</p>
                    </div>
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full max-md:p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 max-md:min-w-0 max-md:p-4">
                <div class="flex items-center justify-between max-md:flex-col-reverse max-md:items-start max-md:gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Rombel</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1 max-md:text-2xl">{{ $adminStats['rombel'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Kelas pada tahun dipilih</p>
                    </div>
                    <div class="bg-orange-100 dark:bg-orange-900 p-3 rounded-full max-md:p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500 dark:text-orange-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </div>
                </div>
            </div>
        @elseif ($isGuru && $guruStats)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 max-md:min-w-0 max-md:p-4">
                <div class="flex items-center justify-between max-md:flex-col-reverse max-md:items-start max-md:gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mapel Diampu</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1 max-md:text-2xl">
                            {{ $guruStats['mapel_diampu'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Tahun & semester dipilih</p>
                    </div>
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full max-md:p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 max-md:min-w-0 max-md:p-4">
                <div class="flex items-center justify-between max-md:flex-col-reverse max-md:items-start max-md:gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Siswa</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1 max-md:text-2xl">{{ $guruStats['siswa'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Di kelas yang diajar</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full max-md:p-2">
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
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 col-span-1 md:col-span-2 max-md:col-span-2 max-md:p-4">
                <div class="flex items-center justify-between mb-4 max-md:gap-3">
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

            {{-- Wali Kelas Card --}}
            @if ($waliKelasNama ?? false)
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700 col-span-1 md:col-span-2 max-md:col-span-2 max-md:p-4">
                    <div class="flex items-center justify-between max-md:flex-col max-md:items-stretch max-md:gap-4">
                        <div class="flex items-center gap-4 max-md:min-w-0">
                            <div class="bg-emerald-100 dark:bg-emerald-900 p-4 rounded-xl max-md:shrink-0 max-md:p-3">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-8 w-8 text-emerald-600 dark:text-emerald-300" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Wali Kelas') }}
                                </p>
                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $waliKelasNama }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Kelola data siswa di kelas Anda') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('wali-kelas.siswa.index') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 max-md:justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ __('Kelola Siswa') }}
                        </a>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Status Pengisian Nilai per Mapel --}}
    @if ($penilaianStatus->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6 max-md:mb-4 max-md:min-w-0">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 max-md:px-4">
                <div class="flex items-center justify-between max-md:flex-col max-md:items-stretch max-md:gap-3">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 max-md:text-base">
                            {{ __('Status Pengisian Nilai per Mapel') }}
                        </h2>
                    </div>
                    @if ($isAdmin)
                        <div class="flex items-center gap-2">
                            <label for="filterKelas" class="text-xs font-medium text-gray-500 dark:text-gray-400 max-md:shrink-0 max-md:text-sm">{{ __('Filter Kelas:') }}</label>
                            <select id="filterKelas"
                                class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs text-gray-700 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300/50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 max-md:h-11 max-md:min-w-0 max-md:flex-1 max-md:px-3 max-md:py-0 max-md:text-base">
                                <option value="">{{ __('Semua Kelas') }}</option>
                                @foreach ($penilaianStatus->pluck('kelas')->unique()->sort() as $kls)
                                    <option value="{{ $kls }}">{{ $kls }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
            {{-- HP: kartu per mapel; md ke atas: tabel. Keduanya ikut disaring oleh Filter Kelas (kelas .penilaian-row). --}}
            <ul class="divide-y divide-gray-200 md:hidden dark:divide-gray-700">
                @foreach ($penilaianStatus as $status)
                    @php
                        $pColor = $status->progress < 50 ? 'red' : ($status->progress < 80 ? 'amber' : 'emerald');
                        $barBg = ['red' => 'bg-red-500', 'amber' => 'bg-amber-500', 'emerald' => 'bg-emerald-500'][$pColor];
                        $textColor = ['red' => 'text-red-600 dark:text-red-400', 'amber' => 'text-amber-600 dark:text-amber-400', 'emerald' => 'text-emerald-600 dark:text-emerald-400'][$pColor];
                        $badgeBg = ['red' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300', 'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'][$pColor];
                        $label = $status->progress >= 100 ? 'Selesai' : ($status->progress == 0 ? 'Belum Diisi' : $status->progress . '%');
                    @endphp
                    <li class="penilaian-row px-4 py-3" data-kelas="{{ $status->kelas }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $status->mapel }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $status->guru }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $status->kelas }}</span>
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="h-2 min-w-0 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-600">
                                <div class="{{ $barBg }} h-2 rounded-full" style="width: {{ $status->progress }}%"></div>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeBg }}">{{ $label }}</span>
                            <span class="shrink-0 text-xs tabular-nums">
                                <span class="font-semibold {{ $textColor }}">{{ $status->filled }}</span><span class="text-gray-400">/</span><span class="text-gray-500">{{ $status->total_siswa }}</span>
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="overflow-x-auto max-md:hidden">
                <table class="w-full text-sm" id="penilaianStatusTable">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Mata Pelajaran') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Guru Pengampu') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Kelas') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Status') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Terisi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($penilaianStatus as $status)
                            @php
                                $pColor = $status->progress < 50 ? 'red' : ($status->progress < 80 ? 'amber' : 'emerald');
                                $barBg = ['red' => 'bg-red-500', 'amber' => 'bg-amber-500', 'emerald' => 'bg-emerald-500'][$pColor];
                                $textColor = ['red' => 'text-red-600 dark:text-red-400', 'amber' => 'text-amber-600 dark:text-amber-400', 'emerald' => 'text-emerald-600 dark:text-emerald-400'][$pColor];
                                $badgeBg = ['red' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300', 'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'][$pColor];
                                $label = $status->progress >= 100 ? 'Selesai' : ($status->progress == 0 ? 'Belum Diisi' : $status->progress . '%');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition penilaian-row"
                                data-kelas="{{ $status->kelas }}">
                                <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $status->mapel }}</td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ $status->guru }}</td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ $status->kelas }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
                                            <div class="{{ $barBg }} h-2 rounded-full transition-all duration-500"
                                                style="width: {{ $status->progress }}%"></div>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeBg }}">
                                            {{ $label }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="font-semibold {{ $textColor }}">{{ $status->filled }}</span>
                                    <span class="text-gray-400">/</span>
                                    <span class="text-gray-500">{{ $status->total_siswa }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($isAdmin)
            <script>
                document.getElementById('filterKelas')?.addEventListener('change', function() {
                    const val = this.value;
                    document.querySelectorAll('.penilaian-row').forEach(row => {
                        row.style.display = (!val || row.dataset.kelas === val) ? '' : 'none';
                    });
                });
            </script>
        @endif
    @endif

    {{-- Recent Login Logs (Admin Only) --}}
    @if ($isAdmin && $recentLogins->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-md:min-w-0">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 max-md:px-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 max-md:text-base">{{ __('Log Login Terakhir') }}
                    </h2>
                </div>
            </div>
            {{-- HP: kartu per login; md ke atas: tabel --}}
            <ul class="divide-y divide-gray-200 md:hidden dark:divide-gray-700">
                @foreach ($recentLogins as $log)
                    @php
                        $role = $log->user?->role ?? 'user';
                        $roleColor = match ($role) {
                            'admin' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                            'guru' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        };
                    @endphp
                    <li class="flex items-start gap-3 px-4 py-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                            {{ strtoupper(substr($log->user?->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $log->user?->name ?? '-' }}</p>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold {{ $roleColor }}">{{ ucfirst($role) }}</span>
                            </div>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $log->user?->email ?? '-' }}</p>
                            <p class="mt-1 flex flex-wrap gap-x-3 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $log->logged_in_at?->format('d M Y H:i') }}</span>
                                <span class="min-w-0 break-all font-mono">{{ $log->ip_address ?? '-' }}</span>
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="overflow-x-auto max-md:hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('User') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Role') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('IP Address') }}</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Waktu Login') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($recentLogins as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-semibold text-sm">
                                            {{ strtoupper(substr($log->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $log->user?->name ?? '-' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->user?->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $role = $log->user?->role ?? 'user';
                                        $roleColor = match ($role) {
                                            'admin' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                            'guru'
                                                => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleColor }}">
                                        {{ ucfirst($role) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400 font-mono text-xs">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    <div>
                                        <p class="font-medium">{{ $log->logged_in_at?->format('d M Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $log->logged_in_at?->format('H:i:s') }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-layouts.app>
