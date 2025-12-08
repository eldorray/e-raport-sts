<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Mapel Saya') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Klik mapel lalu kelas untuk mengisi nilai Sumatif atau STS per siswa.') }}
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
    @elseif ($groupedAssignments->isEmpty())
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            {{ __('Belum ada penugasan mengajar untuk Anda di tahun ajaran ini.') }}
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($groupedAssignments as $mataPelajaranId => $items)
                @php($mapel = $items->first()->mataPelajaran)
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $mapel->nama_mapel }}
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Klik kelas untuk isi nilai') }}</p>
                    </div>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($items as $assignment)
                            <li class="flex items-center justify-between px-4 py-3">
                                <div class="text-sm text-gray-800 dark:text-gray-100">{{ $assignment->kelas->nama }}
                                </div>
                                <a href="{{ route('guru.penilaian.show', ['mengajar' => $assignment->id, 'jenis' => 'sumatif']) }}"
                                    class="inline-flex items-center gap-2 rounded-lg border border-blue-500 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-100 dark:hover:bg-blue-900/40">
                                    {{ __('Isi Nilai') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
