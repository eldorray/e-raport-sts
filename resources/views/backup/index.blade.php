<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Backup & Restore Database') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Kelola backup dan restore database aplikasi') }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Backup Section --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Backup Database') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Download seluruh database dalam format .sql') }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-700/50 dark:text-gray-300">
                    <p class="font-medium mb-2">{{ __('File backup akan berisi:') }}</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-500 dark:text-gray-400">
                        <li>{{ __('Struktur tabel (CREATE TABLE)') }}</li>
                        <li>{{ __('Semua data (INSERT INTO)') }}</li>
                        <li>{{ __('Tidak termasuk tabel sesi, cache, dan antrean') }}</li>
                    </ul>
                </div>

                <a href="{{ route('backup.download') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    {{ __('Download Backup') }}
                </a>
            </div>
        </div>

        {{-- Restore Section --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Restore Database') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Dilakukan dari panel hosting, bukan dari aplikasi') }}</p>
                </div>
            </div>

            <div class="rounded-lg bg-amber-50 p-4 text-sm text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                <p>
                    {{ __('Untuk mengembalikan data, impor file .sql hasil download di atas melalui panel hosting (phpMyAdmin atau perintah mysql import).') }}
                </p>
                <p class="mt-2 text-amber-600 dark:text-amber-400">
                    {{ __('Restore akan menimpa data yang ada. Download backup data saat ini terlebih dahulu.') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
