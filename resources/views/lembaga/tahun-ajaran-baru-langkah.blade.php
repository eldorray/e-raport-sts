@php
    $daftarLangkah = [1 => __('Pilih tahun ajaran'), 2 => __('Pratinjau'), 3 => __('Selesai')];
@endphp

{{-- Di HP hanya langkah yang sedang berjalan yang menampilkan judul, supaya indikator muat dalam satu baris. --}}
<ol class="mb-6 flex flex-wrap items-center gap-2 text-sm max-md:mb-4 max-md:flex-nowrap" aria-label="{{ __('Langkah wizard') }}">
    @foreach ($daftarLangkah as $nomor => $judul)
        <li class="flex items-center gap-2 {{ $nomor === $langkah ? 'max-md:min-w-0' : 'max-md:shrink-0' }}">
            <span @if ($nomor === $langkah) aria-current="step" @endif
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 font-semibold max-md:min-h-8 max-md:min-w-8 max-md:justify-center {{ $nomor === $langkah
                    ? 'bg-blue-600 text-white max-md:min-w-0'
                    : ($nomor < $langkah
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200 max-md:px-0'
                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 max-md:px-0') }}">
                <span>{{ $nomor }}</span>
                <span class="{{ $nomor === $langkah ? 'max-md:truncate' : 'max-md:sr-only' }}">{{ $judul }}</span>
            </span>
            @if (! $loop->last)
                <i class="fas fa-chevron-right text-xs text-gray-400" aria-hidden="true"></i>
            @endif
        </li>
    @endforeach
</ol>
