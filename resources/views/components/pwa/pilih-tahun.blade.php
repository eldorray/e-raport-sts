{{--
    Lembar bawah pemilih tahun ajaran & semester.
    Dipasang oleh layout PWA dan dibuka lewat bukaTahun() (state `sheetTahun` milik layout).
    Setiap baris tahun ajaran mewakili satu semester; pilihan dikirim ke tahun-ajaran.switch-session,
    yang menyimpan pilihan di sesi lalu kembali ke halaman ini.
--}}
@props([
    'daftar',
    'terpilih' => null,
])

<div x-cloak x-show="sheetTahun" x-transition.opacity
    class="fixed inset-0 z-50 flex items-end bg-slate-900/60 backdrop-blur-sm sm:items-center sm:justify-center sm:p-4"
    @click.self="sheetTahun = false" @keydown.escape.window="sheetTahun = false" role="dialog" aria-modal="true"
    aria-labelledby="judul-pilih-tahun">
    <div x-show="sheetTahun" x-transition
        class="flex max-h-[85dvh] w-full max-w-lg flex-col rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl dark:bg-slate-900">
        <div class="px-5 pt-3">
            <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-200 dark:bg-slate-700"></div>
            <h2 id="judul-pilih-tahun" class="text-base font-bold">{{ __('Pilih tahun ajaran') }}</h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ __('Kelas, nilai, dan rapor yang tampil mengikuti pilihan ini.') }}</p>
        </div>

        <form method="POST" action="{{ route('tahun-ajaran.switch-session') }}" @submit="menggantiTahun = true"
            class="mt-3 flex-1 space-y-4 overflow-y-auto overscroll-contain px-5 pb-2">
            @csrf
            @method('PATCH')

            @forelse ($daftar->groupBy('nama') as $nama => $semesters)
                <section>
                    <p class="mb-1.5 px-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                        {{ $nama }}</p>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($semesters as $tahun)
                            @php $dipilih = $terpilih !== null && (int) $tahun->id === (int) $terpilih; @endphp

                            {{-- Pilihan yang sedang aktif cukup menutup lembar, tidak perlu dikirim ulang --}}
                            <button type="{{ $dipilih ? 'button' : 'submit' }}"
                                name="tahun_ajaran_id" value="{{ $tahun->id }}" :disabled="menggantiTahun || offline"
                                @if ($dipilih) data-tahun-terpilih="{{ $tahun->id }}" aria-current="true" @click="sheetTahun = false" @endif
                                @if ($tahun->is_active) data-tahun-aktif="{{ $tahun->id }}" @endif
                                class="flex min-h-14 items-center gap-2 rounded-2xl border px-3 py-2.5 text-left transition active:scale-[0.98] disabled:opacity-60 {{ $dipilih ? 'border-emerald-500 bg-emerald-50 text-emerald-800 dark:border-emerald-500 dark:bg-emerald-950/50 dark:text-emerald-200' : 'border-slate-200 text-slate-700 dark:border-slate-700 dark:text-slate-200' }}">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold">
                                        {{ __('Semester :semester', ['semester' => $tahun->semester]) }}</span>
                                    @if ($tahun->is_active)
                                        <span
                                            class="mt-1 inline-flex rounded-full bg-emerald-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ __('Aktif') }}</span>
                                    @endif
                                </span>

                                @if ($dipilih)
                                    <i class="fas fa-circle-check shrink-0 text-emerald-600 dark:text-emerald-400"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </section>
            @empty
                <p
                    class="rounded-2xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    {{ __('Belum ada data tahun ajaran. Admin perlu menambahkannya terlebih dahulu.') }}
                </p>
            @endforelse
        </form>

        <div class="border-t border-slate-100 px-5 pb-[calc(1rem+env(safe-area-inset-bottom))] pt-3 dark:border-slate-800">
            <p x-cloak x-show="offline"
                class="mb-2 flex items-center gap-2 text-xs font-semibold text-amber-700 dark:text-amber-300">
                <i class="fas fa-wifi"></i>{{ __('Sambungkan internet untuk mengganti tahun ajaran.') }}
            </p>
            <p x-cloak x-show="menggantiTahun"
                class="mb-2 flex items-center gap-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                <i class="fas fa-spinner fa-spin"></i>{{ __('Mengganti tahun ajaran…') }}
            </p>
            <button type="button" @click="sheetTahun = false"
                class="h-12 w-full rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 transition active:scale-[0.99] dark:border-slate-700 dark:text-slate-300">
                {{ __('Tutup') }}
            </button>
        </div>
    </div>
</div>
