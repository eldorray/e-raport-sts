@php
    $namaMapel = $mengajar->mataPelajaran?->nama_mapel ?? __('Input Nilai');
    $namaKelas = $mengajar->kelas?->nama ?? '—';
@endphp

<x-layouts.pwa :title="$namaMapel" :subtitle="$namaKelas.' • '.($tahunAjaran?->nama ?? '').' • '.($semester ?: '-')" :back="route('guru.pwa.nilai')">
    <div x-data="inputNilai({{ Illuminate\Support\Js::from([
        'total' => $siswas->count(),
        'bobotSumatif' => $bobotSumatif,
        'bobotSts' => $bobotSts,
    ]) }})"
        x-init="siap()">
        {{-- Ringkasan progres --}}
        <section
            class="mb-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('Nilai lengkap') }}</p>
                    <p class="mt-0.5 text-2xl font-bold tabular-nums">
                        <span x-text="lengkap">0</span><span
                            class="text-base font-medium text-slate-400">/{{ $siswas->count() }}</span>
                    </p>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span x-text="terisiTotal"></span> {{ __('berisi nilai') }}
                </p>
            </div>

            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                    :style="`width: ${persen}%`"></div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px]">
                <span
                    class="rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ __('Sumatif :bobot%', ['bobot' => $bobotSumatif]) }}
                </span>
                <span
                    class="rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ __('STS :bobot%', ['bobot' => $bobotSts]) }}
                </span>
                <a href="{{ route('penilaian.bobot.edit') }}"
                    class="ml-auto font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Ubah bobot') }}</a>
            </div>
        </section>

        @if (! $canEdit)
            <div
                class="mb-3 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                <i class="fas fa-lock"></i>
                {{ __('Tahun ajaran tidak aktif — nilai hanya bisa dilihat.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('guru.penilaian.store', $mengajar) }}" x-ref="form"
            @submit="menyimpan = true" class="pb-28">
            @csrf

            {{-- Materi / tujuan pembelajaran --}}
            <details class="mb-3 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm font-semibold">
                    <i class="fas fa-book-open text-slate-400"></i>
                    {{ __('Materi / Tujuan Pembelajaran') }}
                </summary>
                <div class="px-4 pb-4">
                    <input type="text" name="materi_tp" maxlength="255" @disabled(! $canEdit)
                        value="{{ $materiTp }}"
                        placeholder="{{ __('mis. Bab 3 — Operasi Pecahan') }}"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900" />
                </div>
            </details>

            {{-- Pencarian siswa (menempel di bawah header) --}}
            <div
                class="sticky top-[calc(4rem+env(safe-area-inset-top))] z-20 -mx-4 mb-3 border-b border-slate-200 bg-slate-100/95 px-4 py-2 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input type="search" x-model="cari" autocomplete="off"
                            placeholder="{{ __('Cari nama atau NIS…') }}"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-900" />
                    </div>

                    @if ($canEdit)
                        <button type="button" @click="sheetIsiCepat = true"
                            class="flex h-12 shrink-0 items-center gap-2 rounded-2xl bg-emerald-600 px-4 text-sm font-semibold text-white transition active:scale-95">
                            <i class="fas fa-bolt"></i>
                            <span class="hidden min-[380px]:inline">{{ __('Isi Cepat') }}</span>
                        </button>
                    @endif
                </div>

                <p class="mt-1.5 px-1 text-[11px] text-slate-500 dark:text-slate-400">
                    <span x-text="tampil"></span> {{ __('dari') }} {{ $siswas->count() }} {{ __('siswa ditampilkan') }}
                </p>
            </div>

            @if ($siswas->isEmpty())
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 text-center text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <i class="fas fa-user-slash mb-2 text-2xl text-slate-300 dark:text-slate-600"></i>
                    <p class="font-semibold">{{ __('Belum ada siswa di kelas ini.') }}</p>
                    <p class="mt-1 text-slate-500 dark:text-slate-400">
                        {{ __('Tambahkan siswa melalui menu Siswa atau Wali Kelas terlebih dahulu.') }}</p>
                </div>
            @else
                <ul class="space-y-2" @input="hitung()" @change="hitung()">
                    @foreach ($siswas as $index => $siswa)
                        @php
                            $baris = $nilaiBySiswa->get($siswa->id);
                            $nilaiAwal = function ($nilai): string {
                                return $nilai === null ? '' : rtrim(rtrim(number_format((float) $nilai, 2, '.', ''), '0'), '.');
                            };
                        @endphp
                        <li data-siswa="{{ $siswa->id }}" data-nama="{{ $siswa->nama }}"
                            data-nis="{{ $siswa->nis }}" x-show="cocok(@js($siswa->nama), @js($siswa->nis))"
                            class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $index + 1 }}</span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $siswa->nama }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        NIS {{ $siswa->nis ?: '—' }}</p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold tabular-nums text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ __('Akhir') }}: <span data-nilai-akhir>—</span>
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <label class="block">
                                    <span
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Sumatif') }}</span>
                                    <input type="text" inputmode="decimal" enterkeyhint="next" autocomplete="off"
                                        data-nilai="sumatif" name="nilai_sumatif[{{ $siswa->id }}]"
                                        value="{{ $nilaiAwal($baris?->nilai_sumatif) }}"
                                        @blur="normalisasi($event)" @disabled(! $canEdit) placeholder="—"
                                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 text-center text-lg font-bold tabular-nums outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900" />
                                </label>

                                <label class="block">
                                    <span
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('STS') }}</span>
                                    <input type="text" inputmode="decimal" enterkeyhint="next" autocomplete="off"
                                        data-nilai="sts" name="nilai_sts[{{ $siswa->id }}]"
                                        value="{{ $nilaiAwal($baris?->nilai_sts) }}"
                                        @blur="normalisasi($event)" @disabled(! $canEdit) placeholder="—"
                                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 text-center text-lg font-bold tabular-nums outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900" />
                                </label>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($canEdit && $siswas->isNotEmpty())
                {{-- Bilah simpan menempel di atas navigasi bawah --}}
                <div
                    class="fixed inset-x-0 bottom-[calc(4.5rem+env(safe-area-inset-bottom))] z-20 px-4">
                    <div
                        class="mx-auto flex max-w-lg items-center gap-3 rounded-2xl border border-slate-200 bg-white/95 p-2.5 shadow-lg backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
                        <div class="min-w-0 flex-1 px-1">
                            <p class="text-xs font-semibold">
                                <span x-text="dirty ? '{{ __('Belum disimpan') }}' : '{{ __('Tersimpan') }}'"></span>
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                <span x-text="`${lengkap}/{{ $siswas->count() }} {{ __('lengkap') }}`"></span>
                            </p>
                        </div>

                        <button type="submit" :disabled="menyimpan"
                            class="flex h-12 items-center gap-2 rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white transition hover:bg-emerald-700 active:scale-[0.98] disabled:opacity-60">
                            <i class="fas" :class="menyimpan ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i>
                            <span x-text="menyimpan ? '{{ __('Menyimpan…') }}' : '{{ __('Simpan') }}'"></span>
                        </button>
                    </div>
                </div>
            @endif
        </form>

        @if ($canEdit)
            {{-- Lembar isi cepat --}}
            <div x-cloak x-show="sheetIsiCepat" x-transition.opacity
                class="fixed inset-0 z-40 flex items-end bg-slate-900/60 backdrop-blur-sm"
                @click.self="sheetIsiCepat = false">
                <div x-show="sheetIsiCepat" x-transition
                    class="mx-auto w-full max-w-lg rounded-t-3xl bg-white p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] shadow-2xl dark:bg-slate-900">
                    <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-200 dark:bg-slate-700"></div>

                    <h2 class="text-base font-bold">{{ __('Isi nilai sekaligus') }}</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('Masukkan satu nilai, lalu pilih ke mana nilai itu diterapkan.') }}</p>

                    <label class="mt-4 block">
                        <span class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Nilai') }}</span>
                        <input type="text" inputmode="decimal" x-model="nilaiCepat" placeholder="mis. 85"
                            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 text-center text-2xl font-bold tabular-nums outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950" />
                    </label>

                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ([70, 75, 80, 85, 90, 100] as $nilai)
                            <button type="button" @click="nilaiCepat = '{{ $nilai }}'"
                                class="rounded-full bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-600 transition active:scale-95 dark:bg-slate-800 dark:text-slate-300">{{ $nilai }}</button>
                        @endforeach
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Diterapkan ke') }}</p>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="targetCepat" value="kosong"
                                        class="h-4 w-4 text-emerald-600" />
                                    {{ __('Yang masih kosong') }}
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="targetCepat" value="semua"
                                        class="h-4 w-4 text-emerald-600" />
                                    {{ __('Semua siswa') }}
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ __('Jenis nilai') }}</p>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="bidangCepat" value="sumatif"
                                        class="h-4 w-4 text-emerald-600" />
                                    {{ __('Sumatif') }}
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="bidangCepat" value="sts"
                                        class="h-4 w-4 text-emerald-600" />
                                    {{ __('STS') }}
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="bidangCepat" value="keduanya"
                                        class="h-4 w-4 text-emerald-600" />
                                    {{ __('Keduanya') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex gap-2">
                        <button type="button" @click="sheetIsiCepat = false"
                            class="h-12 flex-1 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 transition active:scale-[0.99] dark:border-slate-700 dark:text-slate-300">
                            {{ __('Batal') }}
                        </button>
                        <button type="button" @click="terapkanIsiCepat()"
                            class="h-12 flex-[1.4] rounded-2xl bg-emerald-600 text-sm font-bold text-white transition active:scale-[0.99]">
                            {{ __('Terapkan') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('inputNilai', (konfigurasi) => ({
                total: konfigurasi.total,
                bobotSumatif: konfigurasi.bobotSumatif,
                bobotSts: konfigurasi.bobotSts,
                cari: '',
                lengkap: 0,
                terisiTotal: 0,
                tampil: konfigurasi.total,
                persen: 0,
                dirty: false,
                menyimpan: false,
                sheetIsiCepat: false,
                nilaiCepat: '',
                targetCepat: 'kosong',
                bidangCepat: 'keduanya',

                siap() {
                    this.hitung();
                    this.dirty = false;

                    this.$watch('cari', () => this.hitungTampil());

                    window.addEventListener('beforeunload', (peristiwa) => {
                        if (!this.dirty || this.menyimpan) {
                            return;
                        }

                        peristiwa.preventDefault();
                        peristiwa.returnValue = '';
                    });
                },

                kolom() {
                    return Array.from(this.$refs.form.querySelectorAll('input[data-nilai]'));
                },

                baris() {
                    return Array.from(this.$refs.form.querySelectorAll('li[data-siswa]'));
                },

                hitungTampil() {
                    this.tampil = this.baris()
                        .filter((baris) => this.cocok(baris.dataset.nama, baris.dataset.nis))
                        .length;
                },

                hitung() {
                    const kolom = this.kolom();
                    const terisi = kolom.filter((el) => el.value.trim() !== '').length;

                    let lengkap = 0;

                    this.baris().forEach((baris) => {
                        const sumatif = baris.querySelector('input[data-nilai="sumatif"]');
                        const sts = baris.querySelector('input[data-nilai="sts"]');
                        const nilaiAkhir = baris.querySelector('[data-nilai-akhir]');

                        if (sumatif.value.trim() !== '' && sts.value.trim() !== '') {
                            lengkap += 1;
                        }

                        if (nilaiAkhir) {
                            nilaiAkhir.textContent = this.nilaiAkhir(sumatif.value, sts.value);
                        }
                    });

                    this.lengkap = lengkap;
                    this.terisiTotal = terisi;
                    this.persen = this.total === 0 ? 0 : Math.round((lengkap / this.total) * 100);
                    this.hitungTampil();
                    this.dirty = true;
                },

                nilaiAkhir(sumatif, sts) {
                    const kosong = (nilai) => nilai.trim() === '' || isNaN(parseFloat(nilai));

                    if (kosong(sumatif) || kosong(sts)) {
                        return '—';
                    }

                    const total = (parseFloat(sumatif) * this.bobotSumatif + parseFloat(sts) * this.bobotSts) / 100;

                    return Number.isInteger(total) ? String(total) : total.toFixed(1);
                },

                cocok(nama, nis) {
                    const kata = this.cari.trim().toLowerCase();

                    if (kata === '') {
                        return true;
                    }

                    return String(nama).toLowerCase().includes(kata) ||
                        String(nis ?? '').toLowerCase().includes(kata);
                },

                normalisasi(peristiwa) {
                    const input = peristiwa.target;
                    const angka = parseFloat(String(input.value).replace(',', '.'));

                    if (String(input.value).trim() === '' || isNaN(angka)) {
                        input.value = '';

                        return;
                    }

                    const dibatasi = Math.min(100, Math.max(0, angka));
                    input.value = Number.isInteger(dibatasi) ? String(dibatasi) : dibatasi.toFixed(1);
                },

                terapkanIsiCepat() {
                    const angka = parseFloat(String(this.nilaiCepat).replace(',', '.'));

                    if (isNaN(angka)) {
                        return;
                    }

                    const nilai = String(Math.min(100, Math.max(0, angka)));

                    this.kolom().forEach((el) => {
                        const cocokBidang = this.bidangCepat === 'keduanya' || this.bidangCepat === el.dataset.nilai;

                        if (!cocokBidang) {
                            return;
                        }

                        if (this.targetCepat === 'kosong' && el.value.trim() !== '') {
                            return;
                        }

                        el.value = nilai;
                    });

                    this.sheetIsiCepat = false;
                    this.hitung();
                },
            }));
        });
    </script>
</x-layouts.pwa>
