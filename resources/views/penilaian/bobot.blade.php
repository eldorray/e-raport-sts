<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">Pengaturan Bobot Penilaian</h1>
            <p class="text-zinc-600 dark:text-zinc-400 mt-1 text-sm">Berlaku untuk semua mata pelajaran yang Anda nilai.
            </p>
        </div>
    </div>

    <div
        class="overflow-hidden rounded-[20px] bg-white shadow-[0_1px_2px_rgba(0,0,0,.05)] dark:border-zinc-700 dark:bg-zinc-800">
        <form method="POST" action="{{ route('penilaian.bobot.update') }}" class="space-y-4 p-6 max-md:p-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="bobot_sumatif" class="block text-sm font-semibold text-zinc-800 dark:text-zinc-100">Bobot
                        Sumatif (%)</label>
                    <input id="bobot_sumatif" name="bobot_sumatif" type="number" step="0.01" min="0"
                        max="100" value="{{ old('bobot_sumatif', $bobotSumatif) }}"
                        class="mt-1 h-10 w-full rounded-xl border-0 bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent px-3 text-sm font-medium tabular-nums text-zinc-900 max-md:h-11 max-md:text-base dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus:bg-zinc-900 dark:text-zinc-100">
                    @error('bobot_sumatif')
                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="bobot_sts" class="block text-sm font-semibold text-zinc-800 dark:text-zinc-100">Bobot
                        STS (%)</label>
                    <input id="bobot_sts" name="bobot_sts" type="number" step="0.01" min="0" max="100"
                        value="{{ old('bobot_sts', $bobotSts) }}"
                        class="mt-1 h-10 w-full rounded-xl border-0 bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent px-3 text-sm font-medium tabular-nums text-zinc-900 max-md:h-11 max-md:text-base dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus:bg-zinc-900 dark:text-zinc-100">
                    @error('bobot_sts')
                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <p class="text-xs text-zinc-600 dark:text-zinc-300">Total harus 100%. Pengaturan ini tersimpan per akun
                (admin maupun guru) dan diterapkan ke semua penilaian mapel yang Anda isi.</p>

            <div class="flex items-center justify-between max-md:flex-col max-md:items-stretch max-md:gap-3">
                <div class="text-xs text-zinc-600 dark:text-zinc-400">
                    {{ __('Terakhir diperbarui untuk akun:') }} <span
                        class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $user->name }}</span>
                </div>
                <button type="submit"
                    class="inline-flex h-10 items-center gap-2 rounded-xl bg-accent px-4 text-sm font-semibold text-white transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97] focus:outline-none focus-visible:ring-4 focus-visible:ring-accent/30 disabled:cursor-not-allowed disabled:opacity-60 max-md:h-11 max-md:justify-center">
                    Simpan Bobot
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
