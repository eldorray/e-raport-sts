<x-layouts.pwa :title="__('Input Nilai')" :back="route('guru.pwa.beranda')" :daftar-tahun="$daftarTahun" :wali="$adaKelasWali">
    @if (! $guru)
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200">
            <p class="font-semibold">{{ __('Akun ini belum tertaut ke data guru.') }}</p>
            <p class="mt-1 leading-relaxed">
                {{ __('Minta admin menautkan akun Anda di menu Data Guru agar penugasan mengajar muncul di sini.') }}
            </p>
        </div>
    @elseif (! $tahunAjaran)
        <x-pwa.tahun-kosong :pesan="__('Pilih tahun ajaran dan semester untuk melihat penugasan mengajar Anda.')" />
    @elseif ($perKelas->isEmpty())
        <div class="rounded-3xl border border-slate-200 bg-white p-5 text-center text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <i class="fas fa-inbox mb-2 text-2xl text-slate-300 dark:text-slate-600"></i>
            <p class="font-semibold">{{ __('Belum ada penugasan mengajar') }}</p>
            <p class="mt-1 leading-relaxed text-slate-500 dark:text-slate-400">
                {{ __('Untuk :ta semester :semester belum ada jadwal mengajar atas nama Anda. Hubungi admin bila seharusnya ada.', ['ta' => $tahunAjaran->nama, 'semester' => $semester ?: '-']) }}
            </p>
        </div>
    @else
        <div class="mb-4 rounded-3xl border border-slate-200 bg-white p-4 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('Tahun ajaran terpilih') }}</p>
                    <p class="mt-1 truncate font-semibold">{{ $tahunAjaran->nama }} • {{ $semester ?: '-' }}</p>
                </div>
                <button type="button" @click="bukaTahun()" aria-haspopup="dialog"
                    class="h-11 shrink-0 rounded-2xl border border-slate-200 px-4 text-xs font-semibold text-emerald-700 transition active:scale-95 dark:border-slate-700 dark:text-emerald-400">
                    {{ __('Ganti') }}
                </button>
            </div>
            @if (! $tahunAjaran->is_active)
                <p class="mt-2 flex items-center gap-2 rounded-2xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                    <i class="fas fa-lock"></i>
                    {{ __('Tahun ajaran tidak aktif — nilai hanya bisa dilihat.') }}
                </p>
            @endif
        </div>

        <div class="space-y-5">
            @foreach ($perKelas as $kelasId => $daftar)
                @php $jumlahSiswa = (int) ($jumlahSiswaPerKelas[$kelasId] ?? 0); @endphp
                <section>
                    <div class="mb-2 flex items-baseline justify-between px-1">
                        <h2 class="text-sm font-bold">{{ $daftar->first()?->kelas?->nama ?? '—' }}</h2>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __(':jumlah siswa', ['jumlah' => $jumlahSiswa]) }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        @foreach ($daftar as $item)
                            @php
                                $lengkap = (int) ($progres[$item->id]['lengkap'] ?? 0);
                                $terisi = (int) ($progres[$item->id]['terisi'] ?? 0);
                                $persenItem = $jumlahSiswa > 0 ? (int) round(($lengkap / $jumlahSiswa) * 100) : 0;
                                $keadaan = $lengkap >= $jumlahSiswa && $jumlahSiswa > 0
                                    ? 'lengkap'
                                    : ($terisi > 0
                                        ? 'sebagian'
                                        : 'kosong');
                            @endphp
                            <a href="{{ route('guru.pwa.nilai.form', $item) }}"
                                class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition active:scale-[0.99] dark:border-slate-800 dark:bg-slate-900">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $item->mataPelajaran?->nama_mapel ?? '—' }}</p>
                                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-full rounded-full {{ $keadaan === 'lengkap' ? 'bg-emerald-500' : ($keadaan === 'sebagian' ? 'bg-amber-400' : 'bg-slate-300') }}"
                                            style="width: {{ $persenItem }}%"></div>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ __(':lengkap dari :total siswa lengkap', ['lengkap' => $lengkap, 'total' => $jumlahSiswa]) }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $keadaan === 'lengkap' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : ($keadaan === 'sebagian' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') }}">
                                    {{ $keadaan === 'lengkap' ? __('Lengkap') : ($keadaan === 'sebagian' ? __('Sebagian') : __('Kosong')) }}
                                </span>

                                <i class="fas fa-chevron-right shrink-0 text-slate-300 dark:text-slate-600"></i>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</x-layouts.pwa>
