<x-layouts.app>
    @php
        $lanjut = $hasil['mode'] === \App\Services\TahunAjaranBaruService::MODE_LANJUT_SEMESTER;
        $kartu = [
            ['label' => __('Kelas dibuat'), 'nilai' => $hasil['kelas_dibuat'], 'ikon' => 'fa-chalkboard'],
            ['label' => __('Siswa disalin'), 'nilai' => $hasil['siswa_disalin'], 'ikon' => 'fa-user-graduate'],
        ];

        if (! $lanjut) {
            $kartu[] = ['label' => __('Tinggal kelas'), 'nilai' => $hasil['siswa_tinggal'], 'ikon' => 'fa-redo'];
            $kartu[] = ['label' => __('Lulus (tidak disalin)'), 'nilai' => $hasil['siswa_lulus'], 'ikon' => 'fa-graduation-cap'];
        }

        $kartu[] = ['label' => __('Jadwal mengajar disalin'), 'nilai' => $hasil['mengajar_disalin'], 'ikon' => 'fa-book-open'];
        $kartu[] = ['label' => __('Penugasan tahfidz disalin'), 'nilai' => $hasil['mengajar_tahfidz_disalin'], 'ikon' => 'fa-quran'];

        $langkahBerikut = [
            ['route' => 'kelas.index', 'label' => $lanjut ? __('Periksa kelas dan wali kelas') : __('Atur wali kelas'), 'ikon' => 'fa-chalkboard-teacher'],
            ['route' => 'rombel.index', 'label' => __('Periksa rombel kelas'), 'ikon' => 'fa-users'],
            ['route' => 'mengajar.index', 'label' => __('Periksa jadwal mengajar'), 'ikon' => 'fa-book-open'],
            ['route' => 'siswa.index', 'label' => __('Tambah siswa baru'), 'ikon' => 'fa-user-plus'],
        ];
    @endphp

    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Tahun Ajaran Baru Selesai') }}</h1>
        <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $hasil['sumber'] }}</span>
            <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $hasil['tujuan'] }}</span>
            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $lanjut ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' }}">
                {{ $lanjut ? __('Lanjut semester') : __('Kenaikan kelas') }}
            </span>
        </p>
    </div>

    @include('lembaga.tahun-ajaran-baru-langkah', ['langkah' => 3])

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($kartu as $item)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <i class="fas {{ $item['ikon'] }} mr-1"></i> {{ $item['label'] }}
                </p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $item['nilai'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 space-y-3 text-sm">
        @if ($hasil['tujuan_dibuat'])
            <p class="text-gray-600 dark:text-gray-400">
                <i class="fas fa-check-circle mr-1 text-emerald-600"></i>
                {{ __('Tahun ajaran :tujuan dibuat oleh wizard.', ['tujuan' => $hasil['tujuan']]) }}
            </p>
        @endif

        @if ($hasil['diaktifkan'])
            <p class="text-gray-600 dark:text-gray-400">
                <i class="fas fa-check-circle mr-1 text-emerald-600"></i>
                {{ __(':tujuan sekarang aktif dan sedang dipilih.', ['tujuan' => $hasil['tujuan']]) }}
            </p>
        @else
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-100">
                <i class="fas fa-info-circle mr-1"></i>
                {{ __(':tujuan belum diaktifkan. Aktifkan dari menu Tahun Ajaran bila sudah siap, atau pilih tahun ajaran itu di bagian atas untuk memeriksa datanya.', ['tujuan' => $hasil['tujuan']]) }}
            </div>
        @endif

        @if ($hasil['siswa_tanpa_kelas'] > 0)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-100">
                <i class="fas fa-info-circle mr-1"></i>
                {{ __(':jumlah siswa aktif tanpa kelas di :sumber tidak ikut disalin.', ['jumlah' => $hasil['siswa_tanpa_kelas'], 'sumber' => $hasil['sumber']]) }}
            </div>
        @endif

        @unless ($lanjut)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-100">
                <i class="fas fa-info-circle mr-1"></i>
                {{ __('Wali kelas belum diatur untuk kelas-kelas baru. Atur di menu Kelas.') }}
            </div>
        @endunless
    </div>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Langkah berikutnya') }}</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($langkahBerikut as $tautan)
                <a href="{{ route($tautan['route']) }}"
                    class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-blue-900/20">
                    <i class="fas {{ $tautan['ikon'] }} text-blue-600"></i> {{ $tautan['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
