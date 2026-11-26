<x-layouts.app>
    @php
        $lanjut = $mode === \App\Services\TahunAjaranBaruService::MODE_LANJUT_SEMESTER;
        $aktifkan = (string) old('aktifkan', '1') === '1';
        $konfirmasi = __('Jalankan wizard dari :sumber ke :tujuan? Data tahun ajaran tujuan akan dibuat sekarang.', [
            'sumber' => $sumber->label(),
            'tujuan' => $tujuan->label(),
        ]);
    @endphp

    <div class="mb-8 flex flex-col gap-3 max-md:mb-5 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Pratinjau Tahun Ajaran Baru') }}</h1>
            <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $sumber->label() }}</span>
                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $tujuan->label() }}</span>
                @unless ($tujuan->exists)
                    <span class="pil pil-info px-2 py-0.5">{{ __('akan dibuat') }}</span>
                @endunless
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $lanjut ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' }}">
                    {{ $lanjut ? __('Lanjut semester') : __('Kenaikan kelas') }}
                </span>
            </p>
        </div>
        <a href="{{ route('tahun-ajaran-baru.create', $isian) }}"
            class="tombol tombol-netral gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:self-start">
            <i class="fas fa-arrow-left"></i> {{ __('Ubah pilihan') }}
        </a>
    </div>

    @include('lembaga.tahun-ajaran-baru-langkah', ['langkah' => 2])

    <form method="POST" action="{{ route('tahun-ajaran-baru.store') }}" class="space-y-6 max-md:space-y-4"
        x-data
        @submit="
            if (! confirm(@js($konfirmasi))) { $event.preventDefault(); return; }
            {{-- Pilihan yang sama dengan bawaan tidak perlu dikirim (server memakai bawaan), supaya sekolah besar tidak melewati batas jumlah isian. --}}
            $el.querySelectorAll('select[data-bawaan]').forEach((s) => { if (s.value === s.dataset.bawaan) s.disabled = true; });
        ">
        @csrf
        @foreach ($isian as $nama => $nilai)
            <input type="hidden" name="{{ $nama }}" value="{{ $nilai }}">
        @endforeach

        @if ($lanjut)
            @include('lembaga.tahun-ajaran-baru-pratinjau-lanjut', ['ringkasan' => $ringkasan, 'tujuan' => $tujuan])
        @else
            @include('lembaga.tahun-ajaran-baru-pratinjau-kenaikan', ['rencana' => $rencana])
        @endif

        {{-- Di HP: kartu dipecah (display: contents) supaya tombol Jalankan bisa menjadi bilah yang menempel di bawah
             layar sepanjang pratinjau. Dalam mode aplikasi bilah itu berada tepat di atas menu bawah. --}}
        <div class="kartu p-6 max-md:contents">
            <div class="flex flex-col gap-4 max-md:contents md:flex-row md:items-center md:justify-between">
                <label class="flex items-start gap-3 text-sm max-md:rounded-2xl max-md:border max-md:border-zinc-200 max-md:bg-white max-md:p-4 max-md:shadow-sm max-md:dark:border-zinc-700 max-md:dark:bg-zinc-800">
                    <input type="hidden" name="aktifkan" value="0">
                    <input type="checkbox" name="aktifkan" value="1" @checked($aktifkan)
                        class="accent-accent mt-0.5 h-4 w-4 max-md:mt-0 max-md:h-6 max-md:w-6 max-md:shrink-0">
                    <span>
                        <span class="block font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Aktifkan tahun ajaran tujuan') }}</span>
                        <span class="text-zinc-600 dark:text-zinc-400">
                            {{ __(':tujuan menjadi tahun ajaran aktif dan langsung dipilih; :sumber tidak lagi aktif.', ['tujuan' => $tujuan->label(), 'sumber' => $sumber->label()]) }}
                        </span>
                    </span>
                </label>
                <div class="flex items-center justify-end gap-3 max-md:z-20 max-md:mt-3 max-md:rounded-2xl max-md:border max-md:border-zinc-200 max-md:bg-white/95 max-md:p-3 max-md:shadow-lg max-md:backdrop-blur max-md:dark:border-zinc-700 max-md:dark:bg-zinc-800/95 max-md:in-[.mode-aplikasi]:sticky max-md:in-[.mode-aplikasi]:bottom-[calc(5.25rem+env(safe-area-inset-bottom))]">
                    <a href="{{ route('tahun-ajaran-baru.create', $isian) }}"
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11">{{ __('Kembali') }}</a>
                    <button type="submit"
                        class="tombol tombol-aksen gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:flex-1 max-md:text-base">
                        <i class="fas fa-play"></i> {{ __('Jalankan') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
