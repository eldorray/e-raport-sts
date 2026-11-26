<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Pelajaran Saya') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Daftar mata pelajaran yang ditugaskan admin kepada Anda pada tahun ajaran & semester terpilih.') }}
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
    @else
        <div class="mb-4 grid gap-3 md:grid-cols-2">
            <div
                class="rounded-[20px] bg-white shadow-[0_1px_2px_rgba(0,0,0,.05)] p-5 text-sm text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-medium text-zinc-600 dark:text-zinc-400">Bobot Penilaian Saya
                        </p>
                        <p class="mt-1 text-base font-semibold">Sumatif
                            {{ number_format(auth()->user()->bobot_sumatif ?? config('rapor.bobot_sumatif', 50), 2) }}%
                            • STS {{ number_format(auth()->user()->bobot_sts ?? config('rapor.bobot_sts', 50), 2) }}%
                        </p>
                        <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">Berlaku untuk semua mapel yang
                            Anda nilai.</p>
                    </div>
                    <a href="{{ route('penilaian.bobot.edit') }}"
                        class="inline-flex h-9 shrink-0 items-center rounded-xl px-3.5 text-[13px] font-semibold bg-zinc-100 text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100 transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97]">
                        Ubah Bobot
                    </a>
                </div>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-[20px] bg-white shadow-[0_1px_2px_rgba(0,0,0,.05)] dark:border-zinc-700 dark:bg-zinc-800">
            @if ($assignments->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Belum ada penugasan mengajar untuk Anda di tahun ajaran ini.') }}
                </div>
            @else
                <div class="overflow-x-auto p-5">
                    <table id="guru-subjects-table"
                        class="min-w-full border-separate border-spacing-0 text-left text-sm text-zinc-700 dark:text-zinc-200">
                        <thead
                            class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 [&_th]:bg-zinc-100 [&_th:first-child]:rounded-l-xl [&_th:last-child]:rounded-r-xl dark:[&_th]:bg-zinc-700/60">
                            <tr>
                                <th class="px-3 py-3">{{ __('Kelas') }}</th>
                                <th class="px-3 py-3">{{ __('Mata Pelajaran') }}</th>
                                <th class="px-3 py-3">{{ __('Semester') }}</th>
                                <th class="px-3 py-3">{{ __('JTM') }}</th>
                            </tr>
                        </thead>
                        <tbody class="[&_td]:border-b [&_td]:border-zinc-100 dark:[&_td]:border-zinc-700">
                            @foreach ($assignments as $item)
                                <tr>
                                    <td class="px-3 py-3">{{ $item->kelas->nama ?? '—' }}</td>
                                    <td class="px-3 py-3">{{ $item->mataPelajaran->nama_mapel ?? '—' }}</td>
                                    <td class="px-3 py-3">{{ $item->semester ?? '—' }}</td>
                                    <td class="px-3 py-3">{{ $item->jtm ?? ($item->mataPelajaran->jumlah_jam ?? '—') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</x-layouts.app>
