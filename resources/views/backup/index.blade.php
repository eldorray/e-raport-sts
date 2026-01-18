<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Backup & Restore Database') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Kelola backup dan restore database aplikasi') }}</p>
    </div>

    {{-- Status Messages --}}
    @if (session('status'))
        <div
            class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

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
                        <li>{{ __('Tidak termasuk tabel sistem Laravel') }}</li>
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
                        {{ __('Upload file .sql untuk mengembalikan data') }}</p>
                </div>
            </div>

            <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <div class="rounded-lg border-2 border-dashed border-gray-300 p-4 text-center dark:border-gray-600">
                    <input type="file" name="backup_file" id="backup_file" accept=".sql,.txt" required
                        class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-400 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    <p class="mt-2 text-xs text-gray-400">{{ __('Format: .sql (Maks. 50MB)') }}</p>
                </div>

                @error('backup_file')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="rounded-lg bg-amber-50 p-4 text-sm dark:bg-amber-900/20">
                    <div class="flex gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="text-amber-700 dark:text-amber-300">
                            <p class="font-semibold">{{ __('Peringatan!') }}</p>
                            <p class="mt-1 text-amber-600 dark:text-amber-400">
                                {{ __('Restore akan menimpa data yang ada. Pastikan Anda sudah mem-backup data saat ini.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    onclick="return confirm('{{ __('Yakin ingin restore? Data yang ada akan ditimpa!') }}')"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    {{ __('Restore dari Backup') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
