@php
    $daftarLangkah = [1 => __('Pilih tahun ajaran'), 2 => __('Pratinjau'), 3 => __('Selesai')];
@endphp

<ol class="mb-6 flex flex-wrap items-center gap-2 text-sm" aria-label="{{ __('Langkah wizard') }}">
    @foreach ($daftarLangkah as $nomor => $judul)
        <li class="flex items-center gap-2">
            <span @if ($nomor === $langkah) aria-current="step" @endif
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 font-semibold {{ $nomor === $langkah
                    ? 'bg-blue-600 text-white'
                    : ($nomor < $langkah
                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200'
                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400') }}">
                <span>{{ $nomor }}</span> {{ $judul }}
            </span>
            @if (! $loop->last)
                <i class="fas fa-chevron-right text-xs text-gray-400" aria-hidden="true"></i>
            @endif
        </li>
    @endforeach
</ol>
