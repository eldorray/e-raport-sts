@php
    $kelasList = collect($ringkasan['kelas']);
@endphp

<div class="grid gap-4 max-md:grid-cols-3 max-md:gap-2 sm:grid-cols-3">
    @foreach ([
        ['label' => __('Kelas'), 'nilai' => $kelasList->count(), 'ikon' => 'fa-chalkboard'],
        ['label' => __('Siswa aktif'), 'nilai' => $kelasList->sum('siswa_aktif'), 'ikon' => 'fa-user-graduate'],
        ['label' => __('Jadwal mengajar'), 'nilai' => $kelasList->sum('mengajar'), 'ikon' => 'fa-book-open'],
    ] as $kartu)
        <div class="kartu p-5 max-md:min-w-0 max-md:p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-600 max-md:text-[11px] max-md:normal-case max-md:tracking-normal dark:text-zinc-400">
                <i class="fas {{ $kartu['ikon'] }} mr-1"></i> {{ $kartu['label'] }}
            </p>
            <p class="mt-2 text-2xl font-bold text-zinc-900 max-md:mt-1 max-md:text-xl dark:text-zinc-100">{{ $kartu['nilai'] }}</p>
        </div>
    @endforeach
</div>

<div class="kartu overflow-hidden">
    <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-5 max-md:px-4 max-md:py-4 dark:border-zinc-700 dark:bg-zinc-900/40">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Lanjut semester') }}</h2>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Setiap kelas disalin dengan nama, tingkat, dan wali kelas yang sama. Siswa aktif masuk ke kelas bernama sama, dan jadwal mengajar (termasuk tahfidz) disalin ke semester :semester.', ['semester' => $tujuan->semester]) }}
        </p>
    </div>
    {{-- HP: daftar kartu per kelas; md ke atas: tabel --}}
    <ul class="divide-y divide-zinc-200 md:hidden dark:divide-zinc-700">
        @foreach ($kelasList as $kelas)
            <li class="px-4 py-3">
                <p class="flex flex-wrap items-center gap-2 font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Kelas :nama', ['nama' => $kelas['nama']]) }}
                    <span class="pil px-2 py-0.5">{{ __('Tingkat :tingkat', ['tingkat' => $kelas['tingkat']]) }}</span>
                </p>
                <p class="mt-1 truncate text-sm text-zinc-600 dark:text-zinc-400">{{ __('Wali Kelas') }}: {{ $kelas['wali'] ?? '—' }}</p>
                <dl class="mt-2 flex flex-wrap gap-2 text-xs">
                    <div class="inline-flex items-center gap-1 rounded-full bg-accent/10 px-2.5 py-1 font-semibold text-accent-strong dark:bg-blue-900/40 dark:text-blue-200">
                        <dt>{{ __('Siswa aktif') }}</dt>
                        <dd>{{ $kelas['siswa_aktif'] }}</dd>
                    </div>
                    <div class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                        <dt>{{ __('Jadwal mengajar') }}</dt>
                        <dd>{{ $kelas['mengajar'] }}</dd>
                    </div>
                </dl>
            </li>
        @endforeach
    </ul>
    <div class="overflow-x-auto px-6 pb-6 max-md:hidden">
        <table class="tabel min-w-full text-left text-sm text-zinc-700 dark:text-zinc-200">
            <thead>
                <tr>
                    <th class="px-4 py-3">{{ __('Kelas') }}</th>
                    <th class="px-4 py-3">{{ __('Tingkat') }}</th>
                    <th class="px-4 py-3">{{ __('Wali Kelas') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Siswa aktif') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Jadwal mengajar') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kelasList as $kelas)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ $kelas['nama'] }}</td>
                        <td class="px-4 py-3">{{ $kelas['tingkat'] }}</td>
                        <td class="px-4 py-3">{{ $kelas['wali'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $kelas['siswa_aktif'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $kelas['mengajar'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($ringkasan['siswa_nonaktif'] > 0 || $ringkasan['siswa_tanpa_kelas'] > 0)
    <div class="bg-amber-50 p-5 text-sm text-amber-800 max-md:p-4 dark:bg-amber-900/30 dark:text-amber-100 rounded-2xl">
        <i class="fas fa-info-circle mr-1"></i>
        @if ($ringkasan['siswa_nonaktif'] > 0)
            {{ __(':jumlah siswa nonaktif tidak ikut disalin.', ['jumlah' => $ringkasan['siswa_nonaktif']]) }}
        @endif
        @if ($ringkasan['siswa_tanpa_kelas'] > 0)
            {{ __(':jumlah siswa aktif belum punya kelas dan tidak ikut disalin; atur kelasnya lewat Rombel Kelas bila perlu.', ['jumlah' => $ringkasan['siswa_tanpa_kelas']]) }}
        @endif
    </div>
@endif
