@php
    $jumlahPeserta = $peserta->count();
@endphp

<x-layouts.pwa :title="$ekskul->nama" :subtitle="__('Ekskul').' • '.($tahunAjaran?->nama ?? '').' • '.($semester ?: '-')" :back="route('guru.pwa.ekskul')">
    <div x-data="ekskulNilai({{ Illuminate\Support\Js::from(['total' => $jumlahPeserta]) }})" x-init="siap()">
        {{-- Ringkasan --}}
        <section
            class="mb-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('Peserta sudah dinilai') }}</p>
                    <p class="mt-0.5 text-2xl font-bold tabular-nums">
                        <span x-text="dinilai">0</span><span
                            class="text-base font-medium text-slate-400">/{{ $jumlahPeserta }}</span>
                    </p>
                </div>
                <span
                    class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                    <i class="fas fa-medal mr-1"></i>{{ __('Ekskul') }}
                </span>
            </div>

            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" :style="`width: ${persen}%`"></div>
            </div>
        </section>

        @if (! $canEdit)
            <div
                class="mb-3 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                <i class="fas fa-lock"></i>
                {{ __('Tahun ajaran tidak aktif — nilai ekskul hanya bisa dilihat.') }}
            </div>
        @endif

        {{-- Pencarian & tambah peserta --}}
        <div
            class="sticky top-[calc(4rem+env(safe-area-inset-top))] z-20 -mx-4 mb-3 border-b border-slate-200 bg-slate-100/95 px-4 py-2 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="search" x-model="cari" autocomplete="off" placeholder="{{ __('Cari peserta…') }}"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-900" />
                </div>

                @if ($canEdit)
                    <button type="button" @click="bukaTambah()"
                        class="flex h-12 shrink-0 items-center gap-2 rounded-2xl bg-emerald-600 px-4 text-sm font-semibold text-white transition active:scale-95">
                        <i class="fas fa-user-plus"></i>
                        <span class="hidden min-[380px]:inline">{{ __('Peserta') }}</span>
                    </button>
                @endif
            </div>

            <p class="mt-1.5 px-1 text-[11px] text-slate-500 dark:text-slate-400">
                <span x-text="tampil"></span> {{ __('dari') }} {{ $jumlahPeserta }} {{ __('peserta ditampilkan') }}
            </p>
        </div>

        @if ($peserta->isEmpty())
            <div
                class="rounded-3xl border border-slate-200 bg-white p-5 text-center text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <i class="fas fa-users mb-2 text-2xl text-slate-300 dark:text-slate-600"></i>
                <p class="font-semibold">{{ __('Belum ada peserta ekskul ini.') }}</p>
                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    {{ __('Tambahkan siswa lewat tombol "Peserta" di atas.') }}</p>
            </div>
        @else
            <form method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}" x-ref="form"
                @submit="menyimpan = true" class="pb-28">
                @csrf

                <ul class="space-y-2" @input="hitung()" @change="hitung()">
                    @foreach ($peserta as $index => $siswa)
                        @php $baris = $nilaiBySiswa->get($siswa->id); @endphp
                        <li data-siswa="{{ $siswa->id }}" data-nama="{{ $siswa->nama }}"
                            data-nis="{{ $siswa->nis }}" x-show="cocok(@js($siswa->nama), @js($siswa->nis))"
                            class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $index + 1 }}</span>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $siswa->nama }}</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ $siswa->kelas?->nama ?? '—' }}</p>
                                </div>

                                <label class="shrink-0">
                                    <span class="sr-only">{{ __('Nilai') }}</span>
                                    <input type="text" inputmode="decimal" enterkeyhint="next" autocomplete="off"
                                        data-nilai="ekskul" name="nilai[{{ $siswa->id }}]"
                                        value="{{ $baris?->nilai !== null ? rtrim(rtrim(number_format((float) $baris->nilai, 2, '.', ''), '0'), '.') : '' }}"
                                        @blur="normalisasi($event)" @disabled(! $canEdit) placeholder="—"
                                        class="h-12 w-20 rounded-2xl border border-slate-200 bg-slate-50 text-center text-lg font-bold tabular-nums outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900" />
                                </label>
                            </div>

                            <div class="mt-2 flex items-center gap-2">
                                <button type="button" @click="putarCatatan({{ $siswa->id }})"
                                    class="flex items-center gap-1.5 rounded-xl px-2 py-1 text-[11px] font-semibold text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                                    <i class="fas fa-note-sticky"></i>
                                    <span x-text="catatanTerbuka[{{ $siswa->id }}] ? '{{ __('Sembunyikan catatan') }}' : '{{ __('Catatan') }}'"></span>
                                </button>

                                @if ($canEdit)
                                    <button type="submit" form="remove-{{ $siswa->id }}"
                                        class="ml-auto flex items-center gap-1.5 rounded-xl px-2 py-1 text-[11px] font-semibold text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/40"
                                        onclick="return confirm('{{ __('Batalkan siswa ini dari penilaian ekskul?') }}');">
                                        <i class="fas fa-user-minus"></i>{{ __('Batalkan') }}
                                    </button>
                                @endif
                            </div>

                            <div x-cloak x-show="catatanTerbuka[{{ $siswa->id }}]" x-transition class="mt-2">
                                <textarea name="catatan[{{ $siswa->id }}]" rows="2" maxlength="255" @disabled(! $canEdit)
                                    placeholder="{{ __('Catatan pembina, mis. sangat aktif') }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950">{{ $baris?->catatan }}</textarea>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($canEdit)
                    <div class="fixed inset-x-0 bottom-[calc(4.5rem+env(safe-area-inset-bottom))] z-20 px-4">
                        <div
                            class="mx-auto flex max-w-lg items-center gap-3 rounded-2xl border border-slate-200 bg-white/95 p-2.5 shadow-lg backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
                            <div class="min-w-0 flex-1 px-1">
                                <p class="text-xs font-semibold">
                                    <span x-text="dirty ? '{{ __('Belum disimpan') }}' : '{{ __('Tersimpan') }}'"></span>
                                </p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <span x-text="`${dinilai}/{{ $jumlahPeserta }} {{ __('peserta dinilai') }}`"></span>
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

            {{-- Form pembatalan peserta (dipanggil tombol "Batalkan" lewat atribut form) --}}
            @foreach ($peserta as $siswa)
                <form id="remove-{{ $siswa->id }}" method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}"
                    class="hidden">
                    @csrf
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                </form>
            @endforeach
        @endif

        @if ($canEdit)
            {{-- Lembar tambah peserta --}}
            <div x-cloak x-show="tambahTerbuka" x-transition.opacity
                class="fixed inset-0 z-40 flex items-end bg-slate-900/60 backdrop-blur-sm" @click.self="tutupTambah()">
                <div x-show="tambahTerbuka" x-transition
                    class="mx-auto flex max-h-[88dvh] w-full max-w-lg flex-col rounded-t-3xl bg-white pb-[env(safe-area-inset-bottom)] shadow-2xl dark:bg-slate-900">
                    <div class="px-5 pt-4">
                        <div class="mx-auto mb-3 h-1.5 w-12 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        <h2 class="text-base font-bold">{{ __('Tambah peserta ekskul') }}</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Centang siswa yang ikut ekskul ini, lalu simpan.') }}</p>

                        <div class="relative mt-3">
                            <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input type="search" x-model="cariTersedia" autocomplete="off"
                                placeholder="{{ __('Cari nama atau kelas…') }}"
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-10 pr-3 text-sm outline-none focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950" />
                        </div>

                        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                            <span x-text="terpilih.length"></span> {{ __('siswa dipilih') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}" x-ref="formTambah"
                        class="flex min-h-0 flex-1 flex-col">
                        @csrf
                        <input type="hidden" name="action" value="add">

                        <div class="mt-3 min-h-0 flex-1 overflow-y-auto px-5">
                            @forelse ($tersedia as $siswa)
                                <label data-tersedia data-nama="{{ $siswa->nama }}"
                                    data-kelas="{{ $siswa->kelas?->nama }}" data-nis="{{ $siswa->nis }}"
                                    x-show="cocokTersedia($el.dataset)"
                                    class="mb-2 flex items-center gap-3 rounded-2xl border border-slate-200 p-3 text-sm dark:border-slate-800">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}"
                                        x-model.number="terpilih"
                                        class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-semibold">{{ $siswa->nama }}</span>
                                        <span
                                            class="block text-[11px] text-slate-500 dark:text-slate-400">{{ $siswa->kelas?->nama ?? '—' }}</span>
                                    </span>
                                </label>
                            @empty
                                <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                    {{ __('Semua siswa sudah terdaftar sebagai peserta.') }}</p>
                            @endforelse
                        </div>

                        <div class="mt-3 flex gap-2 border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                            <button type="button" @click="tutupTambah()"
                                class="h-12 flex-1 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 transition active:scale-[0.99] dark:border-slate-700 dark:text-slate-300">
                                {{ __('Batal') }}
                            </button>
                            <button type="submit" :disabled="terpilih.length === 0"
                                class="h-12 flex-[1.4] rounded-2xl bg-emerald-600 text-sm font-bold text-white transition active:scale-[0.99] disabled:opacity-50">
                                {{ __('Tambahkan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ekskulNilai', (konfigurasi) => ({
                total: konfigurasi.total,
                cari: '',
                cariTersedia: '',
                tampil: konfigurasi.total,
                dinilai: 0,
                persen: 0,
                dirty: false,
                menyimpan: false,
                tambahTerbuka: false,
                catatanTerbuka: {},
                terpilih: [],

                siap() {
                    this.hitung();
                    this.dirty = false;

                    this.$watch('cari', () => this.hitungTampil());

                    window.addEventListener('popstate', () => {
                        if (this.tambahTerbuka) {
                            this.tambahTerbuka = false;
                        }
                    });

                    window.addEventListener('beforeunload', (peristiwa) => {
                        if (!this.dirty || this.menyimpan) {
                            return;
                        }

                        peristiwa.preventDefault();
                        peristiwa.returnValue = '';
                    });
                },

                baris() {
                    if (!this.$refs.form) {
                        return [];
                    }

                    return Array.from(this.$refs.form.querySelectorAll('li[data-siswa]'));
                },

                hitungTampil() {
                    this.tampil = this.baris()
                        .filter((baris) => this.cocok(baris.dataset.nama, baris.dataset.nis))
                        .length;
                },

                hitung() {
                    let dinilai = 0;

                    this.baris().forEach((baris) => {
                        const input = baris.querySelector('input[data-nilai="ekskul"]');

                        if (input.value.trim() !== '') {
                            dinilai += 1;
                        }
                    });

                    this.dinilai = dinilai;
                    this.persen = this.total === 0 ? 0 : Math.round((dinilai / this.total) * 100);
                    this.hitungTampil();
                    this.dirty = true;
                },

                cocok(nama, nis) {
                    const kata = this.cari.trim().toLowerCase();

                    if (kata === '') {
                        return true;
                    }

                    return String(nama).toLowerCase().includes(kata) ||
                        String(nis ?? '').toLowerCase().includes(kata);
                },

                cocokTersedia(dataset) {
                    const kata = this.cariTersedia.trim().toLowerCase();

                    if (kata === '') {
                        return true;
                    }

                    return [dataset.nama, dataset.kelas, dataset.nis]
                        .some((nilai) => String(nilai ?? '').toLowerCase().includes(kata));
                },

                putarCatatan(id) {
                    this.catatanTerbuka[id] = !this.catatanTerbuka[id];
                },

                bukaTambah() {
                    this.tambahTerbuka = true;
                    history.pushState({
                        pwaSheet: true
                    }, '');
                },

                tutupTambah() {
                    this.tambahTerbuka = false;

                    if (history.state && history.state.pwaSheet) {
                        history.back();
                    }
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
            }));
        });
    </script>
</x-layouts.pwa>
