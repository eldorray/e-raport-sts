<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Ekskul Saya</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Pilih ekskul untuk mengisi penilaian.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($assignments as $ekskul)
            <a href="{{ route('guru.ekskul.show', $ekskul) }}"
                class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ekskul
                        </p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $ekskul->nama }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Pembina:
                            {{ optional($ekskul->guru)->nama ?? '-' }}</p>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-200">
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
                <p class="mt-3 text-sm font-semibold text-blue-600 dark:text-blue-300">Isi penilaian</p>
            </a>
        @empty
            <div
                class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-300">
                Belum ada ekskul yang Anda ampu.
            </div>
        @endforelse
    </div>
</x-layouts.app>
