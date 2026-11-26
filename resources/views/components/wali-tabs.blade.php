@props(['aktif'])
@php
    $tabs = [
        'siswa' => ['Siswa', route('wali-kelas.siswa.index')],
        'absen' => ['Data Absen', route('rapor.absen')],
        'prestasi' => ['Prestasi', route('rapor.prestasi')],
        'catatan' => ['Catatan Wali', route('rapor.catatan')],
    ];
    $indeks = (int) array_search($aktif, array_keys($tabs), true);
    // Mode aplikasi HP punya navigasinya sendiri; admin membuka halaman ini lewat menu Rapor.
    $tampil = auth()->user()?->role === 'guru' && ! session(\App\Http\Middleware\TandaiModeAplikasi::KUNCI_SESI);
@endphp

@if ($tampil)
    {{-- Empat halaman wali kelas tampil sebagai satu set tab. Penanda tab bergeser antar halaman
         lewat view transition lintas dokumen; browser yang belum mendukung langsung berpindah. --}}
    <style>
        @media (prefers-reduced-motion: no-preference) {
            @view-transition {
                navigation: auto;
            }

            ::view-transition-old(root),
            ::view-transition-new(root) {
                animation: none;
            }

            ::view-transition-group(wali-tab) {
                animation-duration: 0.32s;
                animation-timing-function: cubic-bezier(0.32, 0.72, 0, 1);
            }
        }
    </style>
    <div class="mb-5 overflow-x-auto">
        <nav aria-label="{{ __('Data kelas') }}"
            class="relative grid min-w-[330px] max-w-xl grid-cols-4 rounded-[14px] bg-zinc-200/70 p-1 dark:bg-zinc-800">
            <span aria-hidden="true"
                class="absolute bottom-1 left-1 top-1 w-[calc((100%-8px)/4)] rounded-[10px] bg-white shadow-[0_1px_3px_rgba(0,0,0,.14)] [view-transition-name:wali-tab] dark:bg-zinc-950"
                style="transform: translateX({{ $indeks * 100 }}%)"></span>
            @foreach ($tabs as $kunci => [$label, $url])
                <a href="{{ $url }}" @if ($kunci === $aktif) aria-current="page" @endif
                    @class([
                        'relative z-10 flex h-[34px] items-center justify-center whitespace-nowrap rounded-[10px] px-1 text-xs transition-colors duration-200 sm:px-3 sm:text-[13px]',
                        'font-semibold text-zinc-900 dark:text-zinc-50' => $kunci === $aktif,
                        'font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100' => $kunci !== $aktif,
                    ])>{{ __($label) }}</a>
            @endforeach
        </nav>
    </div>
@endif
