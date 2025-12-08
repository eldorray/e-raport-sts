<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Pelajaran Saya') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Daftar mata pelajaran yang ditugaskan admin kepada Anda pada tahun ajaran & semester terpilih.') }}
            </p>
        </div>
    </div>

    @if (!$tahunId)
        <div
            class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 shadow-sm dark:border-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-100">
            {{ __('Pilih tahun ajaran terlebih dahulu di dashboard.') }}
        </div>
    @elseif (!$guru)
        <div
            class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm dark:border-red-800 dark:bg-red-900/40 dark:text-red-100">
            {{ __('Akun Anda belum terhubung dengan data guru.') }}
        </div>
    @else
        <div class="mb-4 grid gap-3 md:grid-cols-2">
            <div
                class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900 shadow-sm dark:border-blue-500/60 dark:bg-blue-900/40 dark:text-blue-50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-200">Bobot Penilaian Saya
                        </p>
                        <p class="mt-1 text-base font-semibold">Sumatif
                            {{ number_format(auth()->user()->bobot_sumatif ?? config('rapor.bobot_sumatif', 50), 2) }}%
                            • STS {{ number_format(auth()->user()->bobot_sts ?? config('rapor.bobot_sts', 50), 2) }}%
                        </p>
                        <p class="mt-1 text-xs text-blue-800/80 dark:text-blue-100/80">Berlaku untuk semua mapel yang
                            Anda nilai.</p>
                    </div>
                    <a href="{{ route('penilaian.bobot.edit') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-blue-500 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 dark:border-blue-300 dark:bg-blue-800 dark:text-white dark:hover:bg-blue-700">
                        Ubah Bobot
                    </a>
                </div>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @if ($assignments->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Belum ada penugasan mengajar untuk Anda di tahun ajaran ini.') }}
                </div>
            @else
                <div class="px-6 pb-6 overflow-x-auto">
                    <table id="guru-subjects-table"
                        class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                        <thead
                            class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-3">{{ __('Kelas') }}</th>
                                <th class="px-3 py-3">{{ __('Mata Pelajaran') }}</th>
                                <th class="px-3 py-3">{{ __('Semester') }}</th>
                                <th class="px-3 py-3">{{ __('JTM') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
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
