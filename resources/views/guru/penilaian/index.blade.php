<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Mapel Saya') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Klik mapel lalu kelas untuk mengisi nilai Sumatif atau STS per siswa.') }}
            </p>
        </div>
    </div>

    @if (!$tahunId)
        <div
            class="rounded-2xl bg-amber-100 p-4 text-sm text-amber-900 dark:bg-amber-900/40 dark:text-amber-100">
            {{ __('Pilih tahun ajaran terlebih dahulu di dashboard.') }}
        </div>
    @elseif (!$guru)
        <div
            class="rounded-2xl bg-rose-100 p-4 text-sm text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">
            {{ __('Akun Anda belum terhubung dengan data guru.') }}
        </div>
    @elseif ($groupedAssignments->isEmpty())
        <div
            class="rounded-2xl border border-zinc-200 bg-white p-6 text-center text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            {{ __('Belum ada penugasan mengajar untuk Anda di tahun ajaran ini.') }}
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($groupedAssignments as $mataPelajaranId => $items)
                @php($mapel = $items->first()->mataPelajaran)
                <div class="rounded-[20px] bg-white shadow-[0_1px_2px_rgba(0,0,0,.05)] animate-rise transition duration-300 ease-[cubic-bezier(.32,.72,0,1)] hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgba(0,0,0,.08)] motion-reduce:animate-none motion-reduce:transition-none dark:bg-zinc-800"
                    style="animation-delay: {{ $loop->index * 70 }}ms">
                    <div class="px-5 pb-2 pt-5">
                        <div class="text-[15px] font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $mapel->nama_mapel }}
                        </div>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">{{ __('Klik kelas untuk isi nilai') }}</p>
                    </div>
                    <ul class="flex flex-col gap-1.5 p-3 pt-1">
                        @foreach ($items as $assignment)
                            <li>
                                <a href="{{ route('guru.penilaian.show', ['mengajar' => $assignment, 'jenis' => 'sumatif']) }}"
                                    class="flex min-h-11 items-center justify-between gap-3 rounded-[14px] px-3.5 text-sm font-medium bg-zinc-100 text-zinc-900 dark:bg-zinc-700/60 dark:text-zinc-100 transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97]">
                                    <span class="min-w-0 truncate">{{ $assignment->kelas->nama }}</span>
                                    <span class="inline-flex shrink-0 items-center gap-1 text-[13px] font-semibold text-accent-strong dark:text-blue-300">
                                        {{ __('Isi Nilai') }}
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 6l6 6-6 6" />
                                        </svg>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
