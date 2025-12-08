<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Pengaturan Bobot Penilaian</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">Berlaku untuk semua mata pelajaran yang Anda nilai.
            </p>
        </div>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form method="POST" action="{{ route('penilaian.bobot.update') }}" class="space-y-4 p-6">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="bobot_sumatif" class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Bobot
                        Sumatif (%)</label>
                    <input id="bobot_sumatif" name="bobot_sumatif" type="number" step="0.01" min="0"
                        max="100" value="{{ old('bobot_sumatif', $bobotSumatif) }}"
                        class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @error('bobot_sumatif')
                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="bobot_sts" class="block text-sm font-semibold text-gray-800 dark:text-gray-100">Bobot
                        STS (%)</label>
                    <input id="bobot_sts" name="bobot_sts" type="number" step="0.01" min="0" max="100"
                        value="{{ old('bobot_sts', $bobotSts) }}"
                        class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @error('bobot_sts')
                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <p class="text-xs text-gray-600 dark:text-gray-300">Total harus 100%. Pengaturan ini tersimpan per akun
                (admin maupun guru) dan diterapkan ke semua penilaian mapel yang Anda isi.</p>

            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Terakhir diperbarui untuk akun:') }} <span
                        class="font-semibold text-gray-800 dark:text-gray-100">{{ $user->name }}</span>
                </div>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-900">
                    Simpan Bobot
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
