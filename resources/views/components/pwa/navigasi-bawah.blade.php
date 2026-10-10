{{--
    Navigasi bawah aplikasi HP. Isinya mengikuti peran pengguna:
    - admin: Beranda, Nilai (koreksi), Rapor, Menu, Akun.
    - guru : Beranda, Nilai, Wali (hanya bila wali kelas), Ekskul, Akun.
--}}
@props([
    'wali' => false,
])

@php
    if (auth()->user()?->role === 'admin') {
        $menu = [
            [
                'label' => __('Beranda'),
                'ikon' => 'fa-house',
                'url' => route('admin.pwa.beranda'),
                'aktif' => request()->routeIs('admin.pwa.beranda'),
            ],
            [
                'label' => __('Nilai'),
                'ikon' => 'fa-pen-to-square',
                'url' => route('koreksi-nilai.index'),
                'aktif' => request()->routeIs('koreksi-nilai.*'),
            ],
            [
                'label' => __('Rapor'),
                'ikon' => 'fa-file-lines',
                'url' => route('rapor.index'),
                'aktif' => request()->routeIs('rapor.index'),
            ],
            [
                'label' => __('Menu'),
                'ikon' => 'fa-grip',
                'url' => route('admin.pwa.menu'),
                'aktif' => request()->routeIs('admin.pwa.menu'),
            ],
            [
                'label' => __('Akun'),
                'ikon' => 'fa-user-gear',
                'url' => route('admin.pwa.akun'),
                'aktif' => request()->routeIs('admin.pwa.akun'),
            ],
        ];
    } else {
        $menu = array_values(array_filter([
            [
                'label' => __('Beranda'),
                'ikon' => 'fa-house',
                'url' => route('guru.pwa.beranda'),
                'aktif' => request()->routeIs('guru.pwa.beranda'),
            ],
            [
                'label' => __('Nilai'),
                'ikon' => 'fa-pen-to-square',
                'url' => route('guru.pwa.nilai'),
                'aktif' => request()->routeIs('guru.pwa.nilai*'),
            ],
            $wali
                ? [
                    'label' => __('Wali'),
                    'ikon' => 'fa-user-graduate',
                    'url' => route('guru.pwa.wali'),
                    'aktif' => request()->routeIs('guru.pwa.wali'),
                ]
                : null,
            [
                'label' => __('Ekskul'),
                'ikon' => 'fa-medal',
                'url' => route('guru.pwa.ekskul'),
                'aktif' => request()->routeIs('guru.pwa.ekskul*'),
            ],
            [
                'label' => __('Akun'),
                'ikon' => 'fa-user-gear',
                'url' => route('guru.pwa.akun'),
                'aktif' => request()->routeIs('guru.pwa.akun') || request()->routeIs('settings.*'),
            ],
        ]));
    }
@endphp

<nav aria-label="{{ __('Menu utama') }}"
    class="safe-bawah fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
    <div class="mx-auto grid max-w-lg {{ count($menu) === 5 ? 'grid-cols-5' : 'grid-cols-4' }}">
        @foreach ($menu as $item)
            <a href="{{ $item['url'] }}" @if ($item['aktif']) aria-current="page" @endif
                class="flex flex-col items-center gap-1 px-1 pb-2 pt-2.5 text-[11px] font-medium transition {{ $item['aktif'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
                <span
                    class="flex h-9 w-14 items-center justify-center rounded-2xl transition {{ $item['aktif'] ? 'bg-emerald-50 dark:bg-emerald-950/60' : '' }}">
                    <i class="fas {{ $item['ikon'] }} text-lg"></i>
                </span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
