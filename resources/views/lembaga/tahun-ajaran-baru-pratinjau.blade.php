<x-layouts.app>
    @php
        $lanjut = $mode === \App\Services\TahunAjaranBaruService::MODE_LANJUT_SEMESTER;
        $aktifkan = (string) old('aktifkan', '1') === '1';
        $konfirmasi = __('Jalankan wizard dari :sumber ke :tujuan? Data tahun ajaran tujuan akan dibuat sekarang.', [
            'sumber' => $sumber->label(),
            'tujuan' => $tujuan->label(),
        ]);
    @endphp

    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Pratinjau Tahun Ajaran Baru') }}</h1>
            <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $sumber->label() }}</span>
                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $tujuan->label() }}</span>
                @unless ($tujuan->exists)
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">{{ __('akan dibuat') }}</span>
                @endunless
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $lanjut ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' }}">
                    {{ $lanjut ? __('Lanjut semester') : __('Kenaikan kelas') }}
                </span>
            </p>
        </div>
        <a href="{{ route('tahun-ajaran-baru.create', $isian) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300">
            <i class="fas fa-arrow-left"></i> {{ __('Ubah pilihan') }}
        </a>
    </div>

    @include('lembaga.tahun-ajaran-baru-langkah', ['langkah' => 2])

    <form method="POST" action="{{ route('tahun-ajaran-baru.store') }}" class="space-y-6"
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

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <label class="flex items-start gap-3 text-sm">
                    <input type="hidden" name="aktifkan" value="0">
                    <input type="checkbox" name="aktifkan" value="1" @checked($aktifkan)
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>
                        <span class="block font-semibold text-gray-900 dark:text-gray-100">{{ __('Aktifkan tahun ajaran tujuan') }}</span>
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ __(':tujuan menjadi tahun ajaran aktif dan langsung dipilih; :sumber tidak lagi aktif.', ['tujuan' => $tujuan->label(), 'sumber' => $sumber->label()]) }}
                        </span>
                    </span>
                </label>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('tahun-ajaran-baru.create', $isian) }}"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300">{{ __('Kembali') }}</a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                        <i class="fas fa-play"></i> {{ __('Jalankan') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
