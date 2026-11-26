@props(['title' => null])
@php
    $headerRute = request()->route()?->getName();
    $headerJudul = $title ?? \App\Support\MenuAplikasi::judul($headerRute);
    $headerPeran = Auth::user()->role === 'admin' ? 'Admin' : 'Guru';

    $headerTahunOptions = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();
    $headerYear = $headerTahunOptions->firstWhere('id', session('selected_tahun_ajaran_id'))
        ?? $headerTahunOptions->firstWhere('is_active', true);
    $headerSemester = session('selected_semester') ?? $headerYear?->semester;

    $tombolIkon = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-zinc-600 transition duration-200 hover:bg-zinc-200 active:scale-[0.97] dark:text-zinc-300 dark:hover:bg-zinc-800';
@endphp

<header
    class="sticky top-0 z-20 flex flex-wrap items-center gap-x-3 gap-y-2 bg-zinc-100/70 px-4 py-3 backdrop-blur-lg backdrop-saturate-150 sm:px-8 dark:bg-zinc-900/70">
    <button type="button" @click="toggleSidebar" aria-label="{{ __('Buka atau tutup menu') }}"
        class="{{ $tombolIkon }} -ml-2">
        <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2" stroke-linecap="round">
            <path d="M4 7h16M4 12h16M4 17h16" />
        </svg>
    </button>

    <div class="flex min-w-0 items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
        <span class="max-sm:hidden">{{ $headerPeran }}</span>
        @if ($headerJudul)
            <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0 max-sm:hidden" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 6l6 6-6 6" />
            </svg>
            <span class="truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $headerJudul }}</span>
        @endif
    </div>

    <div class="ml-auto flex items-center gap-3">
        <div x-data="digitalClock()" x-init="start()"
            class="hidden items-center gap-2 text-sm text-zinc-600 lg:flex dark:text-zinc-400">
            <span><span x-text="dayName"></span>, <span x-text="date"></span></span>
            <span class="font-semibold tabular-nums text-zinc-900 dark:text-zinc-100" x-text="time"></span>
        </div>

        @if ($headerYear)
            <div x-data="{ open: false }" class="relative" @keydown.escape="open = false">
                <button type="button" @click="open = !open" aria-haspopup="true" :aria-expanded="open"
                    class="flex h-10 items-center gap-2.5 rounded-xl border border-zinc-200 bg-white px-3.5 text-sm font-medium text-zinc-900 transition duration-200 hover:opacity-90 active:scale-[0.97] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <span @class([
                        'h-2 w-2 shrink-0 rounded-full',
                        'bg-green-500' => $headerYear->is_active,
                        'bg-zinc-400' => !$headerYear->is_active,
                    ])></span>
                    <span class="whitespace-nowrap">{{ $headerYear->nama }}@if ($headerSemester) • {{ $headerSemester }}@endif</span>
                    <svg aria-hidden="true" class="h-4 w-4 text-zinc-600 transition-transform duration-200 dark:text-zinc-400"
                        :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <form method="POST" action="{{ route('tahun-ajaran.switch-session') }}" x-show="open"
                    style="display: none" @click.outside="open = false"
                    x-transition:enter="transition duration-200 ease-[cubic-bezier(.32,.72,0,1)]"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1.5"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition duration-150 ease-in"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 top-12 z-50 max-h-80 w-64 origin-top-right overflow-y-auto rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-[0_12px_32px_rgba(0,0,0,.12)] dark:border-zinc-700 dark:bg-zinc-800">
                    @csrf
                    @method('PATCH')
                    <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-[0.06em] text-zinc-600 dark:text-zinc-400">
                        {{ __('Ganti tahun ajaran') }}</p>
                    @foreach ($headerTahunOptions as $option)
                        <button type="submit" name="tahun_ajaran_id" value="{{ $option->id }}"
                            @if ($option->id === $headerYear->id) aria-current="true" @endif
                            class="flex min-h-10 w-full items-center justify-between gap-3 rounded-[10px] px-3 text-left text-sm text-zinc-900 transition-colors duration-150 hover:bg-zinc-100 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            <span>{{ $option->nama }} - {{ $option->semester }}
                                @if ($option->is_active)
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400">• {{ __('Aktif') }}</span>
                                @endif
                            </span>
                            @if ($option->id === $headerYear->id)
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-accent dark:text-blue-400"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12l5 5 9-10" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </form>
            </div>
        @endif
    </div>
</header>

<script>
    function digitalClock() {
        return {
            time: '',
            date: '',
            dayName: '',
            start() {
                this.update();
                setInterval(() => this.update(), 1000);
            },
            update() {
                const now = new Date(new Date().toLocaleString('en-US', {
                    timeZone: 'Asia/Jakarta'
                }));
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                    'Oktober', 'November', 'Desember'
                ];

                this.dayName = days[now.getDay()];

                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                this.time = `${h}:${m}:${s} WIB`;

                const d = now.getDate();
                const mo = months[now.getMonth()];
                const y = now.getFullYear();
                this.date = `${d} ${mo} ${y}`;
            }
        }
    }
</script>
