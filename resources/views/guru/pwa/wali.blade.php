<x-layouts.pwa :title="__('Wali Kelas')" :subtitle="$kelas?->nama ?? __('Cetak dokumen kelas')" :back="route('guru.pwa.beranda')">
    <div x-data="waliKelas({{ Illuminate\Support\Js::from(['total' => $siswas->count()]) }})" x-init="siap()">
        @if (! $guru)
            <div
                class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200">
                <p class="font-semibold">{{ __('Akun ini belum tertaut ke data guru.') }}</p>
                <p class="mt-1 leading-relaxed">
                    {{ __('Minta admin menautkan akun Anda di menu Data Guru agar kelas wali Anda muncul di sini.') }}
                </p>
            </div>
        @elseif (! $kelas)
            <div
                class="rounded-3xl border border-slate-200 bg-white p-5 text-center text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <i class="fas fa-user-graduate mb-2 text-2xl text-slate-300 dark:text-slate-600"></i>
                <p class="font-semibold">{{ __('Anda bukan wali kelas pada tahun ajaran ini.') }}</p>
                <p class="mt-1 leading-relaxed text-slate-500 dark:text-slate-400">
                    {{ __('Menu ini dipakai wali kelas untuk mencetak rapor dan leger kelasnya. Kelas akan muncul setelah admin menetapkan Anda sebagai wali kelas pada tahun ajaran :tahun.', ['tahun' => $tahunAjaran?->nama ?? __('yang aktif')]) }}
                </p>

                <a href="{{ route('guru.pwa.nilai') }}"
                    class="mt-4 inline-flex h-11 items-center gap-2 rounded-2xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition active:scale-[0.99]">
                    <i class="fas fa-pen-to-square"></i>
                    {{ __('Isi Nilai') }}
                </a>
            </div>
        @else
            @php
                $totalSiswa = max(1, (int) $ringkasan['siswa']);
                $persenSiap = (int) round(((int) $ringkasan['siapCetak'] / $totalSiswa) * 100);
            @endphp

            {{-- Kartu kelas wali --}}
            <section
                class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-300">
                        <i class="fas fa-user-graduate text-lg"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('Wali kelas') }}</p>
                        <p class="truncate text-lg font-bold leading-tight">{{ $kelas->nama }}</p>
                        <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">
                            {{ $tahunAjaran?->nama ?? '-' }} • {{ $semester ?: '-' }}{{ $kelas->tingkat ? ' • '.$kelas->tingkat : '' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <p class="text-lg font-bold leading-none">{{ $ringkasan['siswa'] }}</p>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ __('Siswa') }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <p class="text-lg font-bold leading-none">{{ $ringkasan['mapel'] }}</p>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ __('Mapel') }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <p class="text-lg font-bold leading-none">{{ $ringkasan['siapCetak'] }}</p>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ __('Siap cetak') }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $persenSiap }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ __(':lengkap dari :total siswa nilainya lengkap dan rapor siap dicetak.', ['lengkap' => $ringkasan['siapCetak'], 'total' => $ringkasan['siswa']]) }}
                    </p>
                </div>
            </section>

            {{-- Cetak dokumen kelas --}}
            <section class="mt-3">
                <a href="{{ route('rapor.ledger', ['kelas' => $kelas->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                    target="_blank" rel="noopener"
                    class="flex items-center gap-3 rounded-2xl bg-emerald-600 p-4 text-white shadow-sm transition active:scale-[0.99]">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                        <i class="fas fa-table-list text-lg"></i>
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold">{{ __('Cetak Leger Kelas') }}</span>
                        <span
                            class="mt-0.5 block text-[11px] leading-relaxed text-emerald-50">{{ __('Tabel nilai semua mapel plus peringkat kelas.') }}</span>
                    </span>

                    <i class="fas fa-arrow-up-right-from-square shrink-0 text-emerald-100"></i>
                </a>

                <p class="mt-2 flex items-start gap-2 px-1 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                    <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
                    <span>{{ __('Halaman cetak terbuka di tab baru. Di HP, tekan Bagikan → Cetak atau Simpan PDF agar bisa dikirim lewat WhatsApp.') }}</span>
                </p>
            </section>

            {{-- Daftar siswa --}}
            <section class="mt-4">
                <div class="flex items-center justify-between px-1">
                    <h2 class="text-sm font-semibold">{{ __('Cetak Rapor Siswa') }}</h2>
                    @if ($siswas->isNotEmpty())
                        <span class="text-[11px] text-slate-500 dark:text-slate-400"
                            x-text="tampil + ' {{ __('dari') }} {{ $siswas->count() }} {{ __('siswa') }}'"></span>
                    @endif
                </div>

                @if ($siswas->isEmpty())
                    <div
                        class="mt-2 rounded-3xl border border-slate-200 bg-white p-5 text-center text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <i class="fas fa-users-slash mb-2 text-2xl text-slate-300 dark:text-slate-600"></i>
                        <p class="font-semibold">{{ __('Belum ada siswa di kelas ini.') }}</p>
                        <p class="mt-1 leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ __('Siswa akan muncul setelah admin memindahkannya ke kelas Anda.') }}
                        </p>
                    </div>
                @else
                    <div class="relative mt-2">
                        <i
                            class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input type="search" x-model="cari" autocomplete="off"
                            placeholder="{{ __('Cari nama atau NIS') }}"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-3 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 dark:border-slate-800 dark:bg-slate-900">
                    </div>

                    <ul x-ref="daftar" class="mt-2 space-y-2">
                        @foreach ($siswas as $siswa)
                            @php
                                $progresBaris = $progresSiswa[$siswa->id] ?? ['terisi' => 0, 'lengkap' => 0];
                                $lengkap = $ringkasan['mapel'] > 0 && $progresBaris['lengkap'] >= $ringkasan['mapel'];
                                $inisial = collect(explode(' ', (string) $siswa->nama))->filter()->take(2)
                                    ->map(fn (string $kata): string => mb_strtoupper(mb_substr($kata, 0, 1)))
                                    ->implode('');
                                $tautanTahfidz = in_array($siswa->id, $tahfidzSiap, true)
                                    ? route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester])
                                    : null;
                            @endphp

                            <li data-siswa data-nama="{{ Str::lower($siswa->nama) }}" data-nis="{{ $siswa->nis }}"
                                x-show="cocok($el)"
                                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <button type="button"
                                    @click="pilihSiswa({{ Illuminate\Support\Js::from([
                                        'nama' => $siswa->nama,
                                        'nis' => $siswa->nis,
                                        'kelas' => $kelas->nama,
                                        'rapor' => route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]),
                                        'tahfidz' => $tautanTahfidz,
                                    ], JSON_UNESCAPED_SLASHES) }})"
                                    class="flex w-full items-center gap-3 p-3 text-left transition active:bg-slate-50 dark:active:bg-slate-800/60">
                                    <span
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-sm font-bold {{ $lengkap ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ $inisial ?: 'S' }}
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold">{{ $siswa->nama }}</span>
                                        <span class="mt-0.5 block text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ __('NIS') }} {{ $siswa->nis ?: '-' }}
                                            @if (! $lengkap)
                                                • {{ __('nilai :lengkap/:mapel mapel', ['lengkap' => $progresBaris['lengkap'], 'mapel' => $ringkasan['mapel']]) }}
                                            @endif
                                        </span>
                                    </span>

                                    @if ($lengkap)
                                        <span
                                            class="shrink-0 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            {{ __('Siap cetak') }}
                                        </span>
                                    @elseif ($progresBaris['terisi'] === 0)
                                        <span
                                            class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                            {{ __('Kosong') }}
                                        </span>
                                    @else
                                        <span
                                            class="shrink-0 rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                            {{ __('Sebagian') }}
                                        </span>
                                    @endif

                                    <i class="fas fa-chevron-right shrink-0 text-slate-300 dark:text-slate-600"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Data pendukung rapor (versi web) --}}
            <section class="mt-4">
                <h2 class="px-1 text-sm font-semibold">{{ __('Data Pendukung Rapor') }}</h2>
                <p class="mt-1 px-1 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                    {{ __('Diisi lewat versi web, lalu ikut tercetak di rapor.') }}</p>

                <div class="mt-2 space-y-2">
                    @foreach ([
        ['url' => route('rapor.absen', ['kelas_id' => $kelas->id]), 'ikon' => 'fa-calendar-check', 'label' => __('Data Absen')],
        ['url' => route('rapor.prestasi', ['kelas_id' => $kelas->id]), 'ikon' => 'fa-trophy', 'label' => __('Prestasi Siswa')],
        ['url' => route('rapor.catatan', ['kelas_id' => $kelas->id]), 'ikon' => 'fa-note-sticky', 'label' => __('Catatan Wali')],
    ] as $tautan)
                        <a href="{{ $tautan['url'] }}" target="_blank" rel="noopener"
                            class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                            <i class="fas {{ $tautan['ikon'] }} w-5 text-center text-slate-400"></i>
                            {{ $tautan['label'] }}
                            <i class="fas fa-arrow-up-right-from-square ml-auto text-xs text-slate-300 dark:text-slate-600"></i>
                        </a>
                    @endforeach
                </div>
            </section>

            @if ($tahunAjaran && ! $tahunAjaran->is_active)
                <p
                    class="mt-4 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                    <i class="fas fa-lock"></i>
                    {{ __('Tahun ajaran tidak aktif — data hanya bisa dilihat dan dicetak.') }}
                </p>
            @endif
        @endif

        {{-- Lembar pilihan cetak per siswa --}}
        <div x-cloak x-show="sheetPilihan" x-transition.opacity
            class="fixed inset-0 z-40 flex items-end bg-slate-900/60 backdrop-blur-sm" @click.self="tutupPilihan()">
            <div x-show="sheetPilihan" x-transition
                class="w-full rounded-t-3xl bg-white p-4 pb-[calc(1.25rem+env(safe-area-inset-bottom))] shadow-xl dark:bg-slate-900">
                <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200 dark:bg-slate-700"></div>

                <p class="truncate text-base font-semibold" x-text="terpilih.nama"></p>
                <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400"
                    x-text="'{{ __('NIS') }} ' + (terpilih.nis || '-') + ' • ' + terpilih.kelas"></p>

                <div class="mt-4 space-y-2">
                    <a :href="terpilih.rapor" target="_blank" rel="noopener"
                        class="flex items-center gap-3 rounded-2xl bg-emerald-600 p-4 text-white shadow-sm transition active:scale-[0.99]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                            <i class="fas fa-print text-lg"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold">{{ __('Cetak Rapor') }}</span>
                            <span
                                class="mt-0.5 block text-[11px] text-emerald-50">{{ __('Buka halaman rapor siap cetak.') }}</span>
                        </span>
                        <i class="fas fa-arrow-up-right-from-square shrink-0 text-emerald-100"></i>
                    </a>

                    <template x-if="terpilih.tahfidz">
                        <a :href="terpilih.tahfidz" target="_blank" rel="noopener"
                            class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm font-semibold shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                            <i class="fas fa-book-quran w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                            {{ __('Cetak Raport Tahfidz') }}
                            <i class="fas fa-arrow-up-right-from-square ml-auto text-xs text-slate-300 dark:text-slate-600"></i>
                        </a>
                    </template>

                    <template x-if="!terpilih.tahfidz">
                        <p
                            class="rounded-2xl border border-dashed border-slate-300 px-4 py-3 text-[11px] leading-relaxed text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            {{ __('Raport tahfidz belum diisi untuk siswa ini.') }}
                        </p>
                    </template>
                </div>

                <button type="button" @click="tutupPilihan()"
                    class="mt-4 h-12 w-full rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 transition active:scale-[0.99] dark:border-slate-700 dark:text-slate-300">
                    {{ __('Tutup') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('waliKelas', (konfigurasi) => ({
                cari: '',
                total: konfigurasi.total,
                sheetPilihan: false,
                terpilih: { nama: '', nis: '', kelas: '', rapor: '#', tahfidz: null },

                get tampil() {
                    const daftar = this.$refs.daftar;

                    if (!daftar) {
                        return this.total;
                    }

                    return Array.from(daftar.querySelectorAll('li[data-siswa]'))
                        .filter((el) => this.cocok(el))
                        .length;
                },

                siap() {
                    window.addEventListener('popstate', () => {
                        this.sheetPilihan = false;
                    });
                },

                cocok(el) {
                    const kunci = this.cari.trim().toLowerCase();

                    if (kunci === '') {
                        return true;
                    }

                    return (el.dataset.nama || '').includes(kunci) || (el.dataset.nis || '').includes(kunci);
                },

                pilihSiswa(data) {
                    this.terpilih = data;
                    this.sheetPilihan = true;
                    history.pushState({ pwaSheet: true }, '');
                },

                tutupPilihan() {
                    this.sheetPilihan = false;

                    if (history.state && history.state.pwaSheet) {
                        history.back();
                    }
                },
            }));
        });
    </script>
</x-layouts.pwa>
