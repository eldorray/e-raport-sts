<x-layouts.app>

    @php
        $kartu = 'rounded-[20px] bg-white shadow-[0_1px_2px_rgba(0,0,0,.05)] dark:bg-zinc-800';
        $tekan = 'transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97] motion-reduce:transition-none';
        $redup = 'text-zinc-600 dark:text-zinc-400';
        $chip = [
            'ok' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            'warn' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300',
            'bad' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            'plain' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300',
        ];
        $batang = ['ok' => 'bg-green-500', 'warn' => 'bg-amber-500', 'bad' => 'bg-rose-500'];
        $teksLevel = [
            'ok' => 'text-green-700 dark:text-green-400',
            'warn' => 'text-amber-700 dark:text-amber-400',
            'bad' => 'text-rose-700 dark:text-rose-400',
        ];
        $level = fn ($persen) => $persen < 50 ? 'bad' : ($persen < 80 ? 'warn' : 'ok');

        $biru = 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
        $hijau = 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300';
        $ungu = 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300';
        $kuning = 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300';

        $statistik = match (true) {
            $isAdmin && (bool) $adminStats => [
                ['Total Siswa', $adminStats['siswa'], 'Semua tahun ajaran', 'fas-user-graduate', $biru],
                ['Total Guru', $adminStats['guru'], 'Semua guru terdaftar', 'fas-user', $hijau],
                ['Total Mapel', $adminStats['mapel'], 'Semua mata pelajaran', 'fas-book', $ungu],
                ['Total Rombel', $adminStats['rombel'], 'Kelas pada tahun dipilih', 'fas-layer-group', $kuning],
            ],
            $isGuru && (bool) $guruStats => [
                ['Mapel Diampu', $guruStats['mapel_diampu'], 'Tahun & semester dipilih', 'fas-book', $ungu],
                ['Total Siswa', $guruStats['siswa'], 'Di kelas yang diajar', 'fas-user-graduate', $biru],
            ],
            default => [],
        };

        $aksiCepat = [
            ['Cetak Rapor', 'fas-file-lines', route('rapor.index')],
            ['Tahun Ajaran Baru', 'fas-forward', route('tahun-ajaran-baru.create')],
            ['Koreksi Nilai', 'fas-pen-to-square', route('koreksi-nilai.index')],
            ['Backup', 'fas-database', route('backup.index')],
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 dark:text-zinc-50">
                {{ __('Dashboard') }}</h1>
            @if ($waliKelasNama ?? false)
                <p class="text-sm {{ $redup }}">Selamat datang, {{ $guruModel->nama ?? auth()->user()->name }} •
                    Wali Kelas {{ $waliKelasNama }}</p>
            @else
                <p class="text-sm {{ $redup }}">{{ __('Ringkasan data pada tahun ajaran yang dipilih.') }}</p>
            @endif
        </div>
        @if ($isAdmin)
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('backup.index') }}"
                    class="{{ $tekan }} flex h-10 items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 text-sm font-medium text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                    @svg('fas-database', 'h-3.5 w-3.5')
                    {{ __('Backup') }}
                </a>
                <a href="{{ route('users.create') }}"
                    class="{{ $tekan }} flex h-10 items-center gap-2 rounded-xl bg-accent px-4 text-sm font-semibold text-white">
                    @svg('fas-plus', 'h-3.5 w-3.5')
                    {{ __('Tambah User') }}
                </a>
            </div>
        @endif
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 max-md:gap-3 lg:grid-cols-4">
        @foreach ($statistik as $i => [$judul, $nilai, $keterangan, $ikon, $warna])
            <div class="{{ $kartu }} animate-rise flex min-w-0 flex-col gap-3.5 p-5 transition duration-300 ease-[cubic-bezier(.32,.72,0,1)] hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgba(0,0,0,.08)] motion-reduce:animate-none motion-reduce:transition-none max-md:p-4"
                style="animation-delay: {{ $i * 70 }}ms">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[13px] font-medium {{ $redup }}">{{ $judul }}</span>
                    <span class="{{ $warna }} flex h-9 w-9 shrink-0 items-center justify-center rounded-xl">
                        @svg($ikon, 'h-4 w-4')
                    </span>
                </div>
                <span class="text-[32px] font-semibold leading-none tracking-[-0.03em] tabular-nums text-zinc-900 dark:text-zinc-50">{{ $nilai }}</span>
                <span class="text-xs {{ $redup }}">{{ $keterangan }}</span>
            </div>
        @endforeach

        @if ($isGuru && $guruStats)
            @php
                $progress = $guruStats['penilaian_progress'];
                $lv = $level($progress);
                $progressLabel = $progress < 50 ? 'Perlu Perhatian' : ($progress < 80 ? 'Dalam Proses' : ($progress < 100 ? 'Hampir Selesai' : 'Selesai'));
            @endphp
            <div class="{{ $kartu }} animate-rise col-span-2 flex min-w-0 flex-col justify-between gap-4 p-5 motion-reduce:animate-none max-md:p-4"
                style="animation-delay: 140ms">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[13px] font-medium {{ $redup }}">Status Pengisian Nilai</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-[32px] font-semibold leading-none tracking-[-0.03em] tabular-nums {{ $teksLevel[$lv] }}">{{ $progress }}%</span>
                            <span class="{{ $chip[$lv] }} inline-flex h-6 items-center rounded-full px-2.5 text-xs font-semibold">{{ $progressLabel }}</span>
                        </div>
                    </div>
                    <span class="text-xs tabular-nums {{ $redup }}">{{ $penilaianFilled }} / {{ $targetPenilaian }} entri nilai</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                    <div class="{{ $batang[$lv] }} animate-grow h-full origin-left rounded-full motion-reduce:animate-none"
                        style="width: {{ $progress }}%"></div>
                </div>
            </div>

            @if ($waliKelasNama ?? false)
                <div class="{{ $kartu }} animate-rise col-span-2 flex flex-wrap items-center justify-between gap-4 p-5 motion-reduce:animate-none max-md:p-4 lg:col-span-4"
                    style="animation-delay: 210ms">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="{{ $hijau }} flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                            @svg('fas-user-graduate', 'h-5 w-5')
                        </span>
                        <div class="flex min-w-0 flex-col gap-0.5">
                            <span class="text-[13px] font-medium {{ $redup }}">{{ __('Wali Kelas') }}</span>
                            <span class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $waliKelasNama }}</span>
                            <span class="text-sm {{ $redup }}">{{ __('Kelola data siswa di kelas Anda') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('wali-kelas.siswa.index') }}"
                        class="{{ $tekan }} flex h-11 items-center gap-2 rounded-xl bg-accent px-5 text-sm font-semibold text-white">
                        {{ __('Kelola Siswa') }}
                    </a>
                </div>
            @endif
        @endif
    </div>

    <div class="flex flex-wrap items-start gap-4">
        {{-- Status Pengisian Nilai per Mapel --}}
        @if ($penilaianStatus->isNotEmpty())
            <section x-data="{
                tab: 0,
                kelas: '',
                tampil(kelasBaris, lengkap) {
                    return (!this.kelas || this.kelas === kelasBaris) && (this.tab === 0 || (this.tab === 2) === lengkap);
                },
            }"
                class="{{ $kartu }} animate-rise flex min-w-0 flex-[2_1_520px] flex-col gap-4 p-5 motion-reduce:animate-none max-md:p-4"
                style="animation-delay: 280ms">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-col gap-0.5">
                        <h2 class="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                            {{ __('Status Pengisian Nilai per Mapel') }}</h2>
                        <span class="text-[13px] {{ $redup }}">{{ __('Jumlah siswa yang nilainya sudah diinput guru.') }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        @if ($isAdmin)
                            <label for="filterKelas" class="sr-only">{{ __('Filter Kelas:') }}</label>
                            <select id="filterKelas" x-model="kelas"
                                class="h-10 cursor-pointer rounded-xl border-0 bg-zinc-100 px-3 text-[13px] font-medium text-zinc-900 transition-colors duration-200 hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-accent dark:bg-zinc-700/60 dark:text-zinc-100 dark:hover:bg-zinc-700">
                                <option value="">{{ __('Semua Kelas') }}</option>
                                @foreach ($penilaianStatus->pluck('kelas')->unique()->sort() as $kls)
                                    <option value="{{ $kls }}">{{ $kls }}</option>
                                @endforeach
                            </select>
                        @endif
                        <div role="tablist" aria-label="{{ __('Saring status') }}"
                            class="relative grid grid-cols-3 rounded-[14px] bg-zinc-100 p-1 dark:bg-zinc-700/60">
                            <span aria-hidden="true"
                                class="absolute bottom-1 left-1 top-1 w-[calc((100%-8px)/3)] rounded-[10px] bg-white shadow-[0_1px_3px_rgba(0,0,0,.14)] transition-transform duration-300 ease-[cubic-bezier(.32,.72,0,1)] motion-reduce:transition-none dark:bg-zinc-900"
                                :style="`transform: translateX(${tab * 100}%)`"></span>
                            @foreach (['Semua', 'Belum lengkap', 'Lengkap'] as $i => $namaTab)
                                <button type="button" role="tab" @click="tab = {{ $i }}"
                                    :aria-selected="tab === {{ $i }}"
                                    class="relative z-10 h-8 whitespace-nowrap rounded-[10px] px-3 text-[13px] transition-colors duration-200"
                                    :class="tab === {{ $i }} ? 'font-semibold text-zinc-900 dark:text-zinc-50' :
                                        'font-medium text-zinc-600 dark:text-zinc-400'">{{ __($namaTab) }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- HP: kartu per mapel; md ke atas: tabel. Keduanya ikut disaring oleh pilihan kelas dan tab status. --}}
                <ul class="flex flex-col md:hidden">
                    @foreach ($penilaianStatus as $status)
                        @php
                            $lv = $level($status->progress);
                            $label = $status->progress >= 100 ? 'Selesai' : ($status->progress == 0 ? 'Belum Diisi' : $status->progress . '%');
                        @endphp
                        <li x-show="tampil(@js($status->kelas), {{ $status->progress >= 100 ? 'true' : 'false' }})"
                            class="border-b border-zinc-100 py-3 dark:border-zinc-700">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $status->mapel }}</p>
                                    <p class="truncate text-xs {{ $redup }}">{{ $status->guru }}</p>
                                </div>
                                <span class="{{ $chip['plain'] }} shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ $status->kelas }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="h-2 min-w-0 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                    <div class="{{ $batang[$lv] }} h-full rounded-full" style="width: {{ $status->progress }}%"></div>
                                </div>
                                <span class="{{ $chip[$lv] }} inline-flex h-6 shrink-0 items-center rounded-full px-2.5 text-xs font-semibold">{{ $label }}</span>
                                <span class="shrink-0 text-xs tabular-nums {{ $redup }}">{{ $status->filled }}/{{ $status->total_siswa }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="overflow-x-auto max-md:hidden">
                    <table class="w-full min-w-[560px] border-separate border-spacing-0 text-sm" id="penilaianStatusTable">
                        <thead>
                            <tr class="text-left text-xs font-semibold {{ $redup }}">
                                <th scope="col" class="rounded-l-xl bg-zinc-100 px-3.5 py-2.5 dark:bg-zinc-700/60">{{ __('Mata Pelajaran') }}</th>
                                <th scope="col" class="bg-zinc-100 px-3.5 py-2.5 dark:bg-zinc-700/60">{{ __('Guru Pengampu') }}</th>
                                <th scope="col" class="bg-zinc-100 px-3.5 py-2.5 dark:bg-zinc-700/60">{{ __('Kelas') }}</th>
                                <th scope="col" class="w-[30%] bg-zinc-100 px-3.5 py-2.5 dark:bg-zinc-700/60">{{ __('Terisi') }}</th>
                                <th scope="col" class="rounded-r-xl bg-zinc-100 px-3.5 py-2.5 dark:bg-zinc-700/60">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($penilaianStatus as $status)
                                @php
                                    $lv = $level($status->progress);
                                    $label = $status->progress >= 100 ? 'Selesai' : ($status->progress == 0 ? 'Belum Diisi' : $status->progress . '%');
                                    $sel = 'border-b border-zinc-100 px-3.5 py-3 dark:border-zinc-700';
                                @endphp
                                <tr x-show="tampil(@js($status->kelas), {{ $status->progress >= 100 ? 'true' : 'false' }})"
                                    class="transition-colors duration-150 hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                                    <td class="{{ $sel }} font-medium text-zinc-900 dark:text-zinc-100">{{ $status->mapel }}</td>
                                    <td class="{{ $sel }} text-zinc-700 dark:text-zinc-300">{{ $status->guru }}</td>
                                    <td class="{{ $sel }} font-semibold text-zinc-900 dark:text-zinc-100">{{ $status->kelas }}</td>
                                    <td class="{{ $sel }}">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                <div class="{{ $batang[$lv] }} animate-grow h-full origin-left rounded-full motion-reduce:animate-none"
                                                    style="width: {{ $status->progress }}%"></div>
                                            </div>
                                            <span class="whitespace-nowrap text-[13px] tabular-nums text-zinc-700 dark:text-zinc-300">{{ $status->filled }}/{{ $status->total_siswa }}</span>
                                        </div>
                                    </td>
                                    <td class="{{ $sel }}">
                                        <span class="{{ $chip[$lv] }} inline-flex h-6 items-center whitespace-nowrap rounded-full px-2.5 text-xs font-semibold">{{ $label }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($isAdmin)
            <div class="flex min-w-0 flex-[1_1_320px] flex-col gap-4">
                {{-- Log login terakhir --}}
                @if ($recentLogins->isNotEmpty())
                    <section class="{{ $kartu }} animate-rise flex flex-col gap-2 p-5 motion-reduce:animate-none max-md:p-4"
                        style="animation-delay: 350ms">
                        <h2 class="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                            {{ __('Log Login Terakhir') }}</h2>
                        <ul class="flex flex-col">
                            @foreach ($recentLogins as $log)
                                @php $role = $log->user?->role ?? 'user'; @endphp
                                <li class="flex items-center gap-3 border-b border-zinc-100 py-2.5 last:border-0 dark:border-zinc-700">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-900 dark:bg-zinc-700 dark:text-zinc-100">
                                        {{ strtoupper(substr($log->user?->name ?? 'U', 0, 1)) }}
                                    </span>
                                    <div class="flex min-w-0 flex-1 flex-col leading-snug">
                                        <span class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $log->user?->name ?? '-' }}</span>
                                        <span class="truncate text-xs {{ $redup }}">{{ $log->logged_in_at?->format('d M Y H:i') }}
                                            · <span class="font-mono">{{ $log->ip_address ?? '-' }}</span></span>
                                    </div>
                                    <span class="{{ $role === 'admin' ? $chip['bad'] : ($role === 'guru' ? $chip['ok'] : $chip['plain']) }} inline-flex h-6 shrink-0 items-center rounded-full px-2.5 text-xs font-semibold">{{ ucfirst($role) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="{{ $kartu }} animate-rise flex flex-col gap-3 p-5 motion-reduce:animate-none max-md:p-4"
                    style="animation-delay: 420ms">
                    <h2 class="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">{{ __('Aksi cepat') }}</h2>
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach ($aksiCepat as [$judul, $ikon, $url])
                            <a href="{{ $url }}"
                                class="{{ $tekan }} flex flex-col gap-2.5 rounded-2xl bg-zinc-100 p-3.5 text-[13px] font-semibold text-zinc-900 dark:bg-zinc-700/60 dark:text-zinc-100">
                                @svg($ikon, 'h-[18px] w-[18px]')
                                {{ __($judul) }}
                            </a>
                        @endforeach
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-layouts.app>
