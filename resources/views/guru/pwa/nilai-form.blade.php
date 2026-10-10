@php
    $namaMapel = $mengajar->mataPelajaran?->nama_mapel ?? __('Input Nilai');
    $namaKelas = $mengajar->kelas?->nama ?? '—';
@endphp

<x-layouts.pwa :title="$namaMapel" :subtitle="$namaKelas.' • '.($tahunAjaran?->nama ?? '').' • '.($semester ?: '-')" :back="route('guru.pwa.nilai')" :nav="false">
    <div x-data="inputNilai({{ Illuminate\Support\Js::from([
        'total' => $siswas->count(),
        'bobotSumatif' => $bobotSumatif,
        'bobotSts' => $bobotSts,
        'bisaUbah' => $canEdit,
        'kunciDraft' => 'nilai-draft:'.$mengajar->id.':'.$tahunId.':'.$semester,
        'hapusDraft' => session()->has('status') && ! $errors->any(),
        'belumTersimpan' => $errors->any() && session()->hasOldInput(),
    ]) }})"
        x-init="siap()">
        {{-- Ringkasan progres (dibuat ringkas agar daftar siswa langsung terlihat) --}}
        <section
            class="mb-3 rounded-2xl border border-slate-200 bg-white px-3.5 py-3 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-baseline justify-between gap-3">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    {{ __('Nilai lengkap') }}
                    <span class="ml-1 text-lg font-bold tabular-nums text-slate-800 dark:text-slate-100"><span
                            x-text="lengkap">0</span><span
                            class="text-xs font-medium text-slate-400">/{{ $siswas->count() }}</span></span>
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    <span x-text="terisiTotal"></span> {{ __('berisi nilai') }}
                </p>
            </div>

            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                    :style="`width: ${persen}%`"></div>
            </div>

            <div class="mt-2 flex items-center gap-1.5 text-[11px]">
                <span
                    class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ __('Sumatif :bobot%', ['bobot' => $bobotSumatif]) }}
                </span>
                <span
                    class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    {{ __('STS :bobot%', ['bobot' => $bobotSts]) }}
                </span>
                <a href="{{ route('guru.pwa.akun') }}#bobot"
                    class="-my-1 ml-auto px-1 py-1 font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Ubah bobot') }}</a>
            </div>
        </section>

        @if (! $canEdit)
            <div
                class="mb-3 flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                <i class="fas fa-lock"></i>
                {{ __('Tahun ajaran tidak aktif — nilai hanya bisa dilihat.') }}
            </div>
        @endif

        @if ($canEdit)
            {{-- Draf isian yang belum tersimpan (disimpan di perangkat) --}}
            <div x-cloak x-show="adaDraft" x-transition
                class="mb-3 rounded-2xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span x-text="@js(__('Ada isian yang belum tersimpan dari :jam.')).replace(':jam', jamDraft)"></span>
                </div>
                <div class="mt-2 flex gap-2">
                    <button type="button" @click="pulihkanDraft()"
                        class="h-10 flex-1 rounded-xl bg-amber-600 px-3 font-bold text-white transition active:scale-[0.98]">
                        {{ __('Pulihkan') }}
                    </button>
                    <button type="button" @click="buangDraft()"
                        class="h-10 flex-1 rounded-xl border border-amber-300 px-3 font-semibold text-amber-800 transition active:scale-[0.98] dark:border-amber-800 dark:text-amber-200">
                        {{ __('Buang') }}
                    </button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('guru.penilaian.store', $mengajar) }}" x-ref="form"
            @submit="kirim($event)"
            class="{{ $canEdit && $siswas->isNotEmpty() ? 'pb-[calc(4.5rem+env(safe-area-inset-bottom))]' : '' }}">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            {{-- Materi / tujuan pembelajaran --}}
            <details class="mb-3 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <summary class="flex min-h-11 cursor-pointer items-center gap-2 px-3.5 py-2.5 text-sm font-semibold">
                    <i class="fas fa-book-open text-slate-400"></i>
                    {{ __('Materi / Tujuan Pembelajaran') }}
                </summary>
                <div class="px-3.5 pb-3.5">
                    <input type="text" name="materi_tp" maxlength="255" @disabled(! $canEdit)
                        value="{{ old('materi_tp', $materiTp) }}" @input="simpanDraft()"
                        placeholder="{{ __('mis. Bab 3 — Operasi Pecahan') }}"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 text-base outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900" />
                </div>
            </details>

            {{-- Pencarian siswa + judul kolom (menempel di bawah header) --}}
            <div
                class="sticky top-[calc(4rem+env(safe-area-inset-top))] z-20 -mx-4 mb-2 border-b border-slate-200 bg-slate-100/95 px-4 pb-1.5 pt-2 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input type="search" x-model="cari" autocomplete="off" aria-label="{{ __('Cari siswa') }}"
                            placeholder="{{ __('Cari nama atau NIS…') }}"
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-white pl-10 pr-3 text-base outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-900" />
                    </div>

                    @if ($canEdit)
                        <button type="button" @click="bukaIsiCepat()"
                            class="flex h-11 shrink-0 items-center gap-2 rounded-2xl bg-emerald-600 px-4 text-sm font-semibold text-white transition active:scale-95">
                            <i class="fas fa-bolt"></i>
                            <span class="hidden min-[380px]:inline">{{ __('Isi Cepat') }}</span>
                        </button>
                    @endif
                </div>

                @if ($siswas->isNotEmpty())
                    {{-- Judul kolom; border transparan menyamakan posisi dengan bingkai daftar siswa --}}
                    <div class="mt-1.5 border-x border-transparent" aria-hidden="true">
                        <div
                            class="flex items-center gap-1.5 pl-2 pr-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <span class="w-5 shrink-0 text-center">#</span>
                            <span class="min-w-0 flex-1 truncate">
                                {{ __('Siswa') }}
                                <span x-cloak x-show="cari.trim() !== ''" class="normal-case tracking-normal"
                                    x-text="`· ${tampil} {{ __('dari') }} ${total}`"></span>
                            </span>
                            <span class="w-14 shrink-0 text-center">{{ __('Sumatif') }}</span>
                            <span class="w-14 shrink-0 text-center">{{ __('STS') }}</span>
                            <span class="w-9 shrink-0 text-center">{{ __('Akhir') }}</span>
                        </div>
                    </div>
                @endif
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
                {{-- Satu baris per siswa: nomor, nama + NIS, Sumatif, STS, nilai akhir --}}
                @php
                    $terkoreksi = $nilaiBySiswa->filter(fn ($nilai) => $nilai->dikoreksi_pada !== null);
                    $koreksiTerakhir = $terkoreksi->sortByDesc('dikoreksi_pada')->first();
                @endphp
                @if ($koreksiTerakhir)
                    <p data-ringkasan-koreksi
                        class="mb-2 flex items-start gap-2 rounded-2xl bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                        <i class="fas fa-user-pen mt-0.5"></i>
                        <span>{{ __(':jumlah nilai siswa dikoreksi admin, terakhir oleh :nama pada :waktu.', [
                            'jumlah' => $terkoreksi->count(),
                            'nama' => $koreksiTerakhir->pengoreksi?->name ?? __('Admin'),
                            'waktu' => $koreksiTerakhir->dikoreksi_pada->translatedFormat('d M Y H:i'),
                        ]) }}</span>
                    </p>
                @endif

                <ul data-baris-ringkas
                    class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900"
                    @input="hitung(); simpanDraft()" @change="hitung()">
                    @foreach ($siswas as $index => $siswa)
                        @php
                            $baris = $nilaiBySiswa->get($siswa->id);
                            $nilaiAwal = function ($nilai): string {
                                return $nilai === null ? '' : rtrim(rtrim(number_format((float) $nilai, 2, '.', ''), '0'), '.');
                            };
                        @endphp
                        <li data-siswa="{{ $siswa->id }}" data-nama="{{ $siswa->nama }}"
                            data-nis="{{ $siswa->nis }}" x-show="cocok(@js($siswa->nama), @js($siswa->nis))"
                            class="flex items-center gap-1.5 py-1 pl-2 pr-1.5">
                            <span
                                class="w-5 shrink-0 text-center text-[11px] font-semibold tabular-nums text-slate-400 dark:text-slate-500">{{ $index + 1 }}</span>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold leading-tight">{{ $siswa->nama }}</p>
                                <p class="truncate text-[11px] leading-tight text-slate-500 dark:text-slate-400">
                                    NIS {{ $siswa->nis ?: '—' }}
                                    @if ($baris?->dikoreksi_pada)
                                        <span class="font-semibold text-amber-700 dark:text-amber-300"
                                            title="{{ __('Dikoreksi :nama, :waktu', ['nama' => $baris->pengoreksi?->name ?? __('Admin'), 'waktu' => $baris->dikoreksi_pada->translatedFormat('d M Y H:i')]) }}">
                                            · {{ __('dikoreksi') }}</span>
                                    @endif
                                </p>
                            </div>

                            <input type="text" inputmode="decimal" enterkeyhint="next" autocomplete="off"
                                data-nilai="sumatif" name="nilai_sumatif[{{ $siswa->id }}]"
                                aria-label="{{ __('Sumatif :nama', ['nama' => $siswa->nama]) }}"
                                value="{{ old('nilai_sumatif.'.$siswa->id, $nilaiAwal($baris?->nilai_sumatif)) }}"
                                @blur="normalisasi($event)" @keydown.enter.prevent="kolomBerikut($event)"
                                @disabled(! $canEdit) placeholder="—"
                                class="h-11 w-14 shrink-0 rounded-xl border border-slate-200 bg-slate-50 px-1 text-center text-base font-bold tabular-nums outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900" />

                            <input type="text" inputmode="decimal" enterkeyhint="next" autocomplete="off"
                                data-nilai="sts" name="nilai_sts[{{ $siswa->id }}]"
                                aria-label="{{ __('STS :nama', ['nama' => $siswa->nama]) }}"
                                value="{{ old('nilai_sts.'.$siswa->id, $nilaiAwal($baris?->nilai_sts)) }}"
                                @blur="normalisasi($event)" @keydown.enter.prevent="kolomBerikut($event)"
                                @disabled(! $canEdit) placeholder="—"
                                class="h-11 w-14 shrink-0 rounded-xl border border-slate-200 bg-slate-50 px-1 text-center text-base font-bold tabular-nums outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/30 disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-900" />

                            <span data-nilai-akhir
                                class="w-9 shrink-0 text-center text-sm font-bold tabular-nums text-slate-700 dark:text-slate-200">—</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($canEdit && $siswas->isNotEmpty())
                {{-- Bilah simpan menggantikan navigasi bawah di halaman ini --}}
                <div
                    class="safe-bawah fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
                    <div class="mx-auto flex max-w-lg items-center gap-3 py-2">
                        <div class="min-w-0 flex-1">
                            <p class="flex items-center gap-1.5 text-xs font-semibold">
                                <span class="h-2 w-2 shrink-0 rounded-full"
                                    :class="dirty ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                                <span x-text="dirty ? '{{ __('Belum disimpan') }}' : '{{ __('Tersimpan') }}'"></span>
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                <span x-text="`${lengkap}/{{ $siswas->count() }} {{ __('lengkap') }}`"></span>
                            </p>
                        </div>

                        {{-- `offline` berasal dari komponen Alpine pada layout PWA (scope induk) --}}
                        <button type="submit" :disabled="menyimpan || offline"
                            class="flex h-12 items-center gap-2 rounded-2xl bg-emerald-600 px-6 text-sm font-bold text-white transition hover:bg-emerald-700 active:scale-[0.98] disabled:opacity-60">
                            <i class="fas"
                                :class="menyimpan ? 'fa-spinner fa-spin' : (offline ? 'fa-wifi' : 'fa-floppy-disk')"></i>
                            <span
                                x-text="menyimpan ? '{{ __('Menyimpan…') }}' : (offline ? '{{ __('Offline') }}' : '{{ __('Simpan') }}')"></span>
                        </button>
                    </div>
                </div>
            @endif
        </form>

        @if ($canEdit)
            {{-- Lembar isi cepat --}}
            <div x-cloak x-show="sheetIsiCepat" x-transition.opacity
                class="fixed inset-0 z-40 flex items-end bg-slate-900/60 backdrop-blur-sm"
                @click.self="tutupIsiCepat()">
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
                        <button type="button" @click="tutupIsiCepat()"
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
                kunciDraft: konfigurasi.kunciDraft,
                draft: null,
                adaDraft: false,
                jamDraft: '',

                siap() {
                    this.hitung();
                    // Isian dari penyimpanan yang gagal (old input) memang belum tersimpan.
                    this.dirty = konfigurasi.belumTersimpan;

                    if (konfigurasi.hapusDraft) {
                        this.buangDraft();
                    } else if (konfigurasi.bisaUbah) {
                        this.periksaDraft();
                    }

                    this.$watch('cari', () => this.hitungTampil());

                    window.addEventListener('popstate', () => {
                        this.sheetIsiCepat = false;
                    });

                    window.addEventListener('beforeunload', (peristiwa) => {
                        if (!this.dirty || this.menyimpan) {
                            return;
                        }

                        peristiwa.preventDefault();
                        peristiwa.returnValue = '';
                    });
                },

                bukaIsiCepat() {
                    this.sheetIsiCepat = true;
                    history.pushState({
                        pwaSheet: true
                    }, '');
                },

                tutupIsiCepat() {
                    this.sheetIsiCepat = false;

                    if (history.state && history.state.pwaSheet) {
                        history.back();
                    }
                },

                kolom() {
                    return Array.from(this.$refs.form.querySelectorAll('input[data-nilai]'));
                },

                kolomMateri() {
                    return this.$refs.form.querySelector('input[name="materi_tp"]');
                },

                isianSaatIni() {
                    const isian = {
                        sumatif: {},
                        sts: {},
                        materi: this.kolomMateri()?.value ?? '',
                    };

                    this.baris().forEach((baris) => {
                        const id = baris.dataset.siswa;

                        isian.sumatif[id] = baris.querySelector('input[data-nilai="sumatif"]').value;
                        isian.sts[id] = baris.querySelector('input[data-nilai="sts"]').value;
                    });

                    return isian;
                },

                simpanDraft() {
                    try {
                        window.localStorage.setItem(this.kunciDraft, JSON.stringify({
                            ...this.isianSaatIni(),
                            savedAt: Date.now(),
                        }));
                    } catch (galat) {
                        // Penyimpanan perangkat tidak tersedia (mis. mode privat); draf dilewati.
                    }
                },

                bacaDraft() {
                    try {
                        const mentah = window.localStorage.getItem(this.kunciDraft);

                        return mentah ? JSON.parse(mentah) : null;
                    } catch (galat) {
                        return null;
                    }
                },

                periksaDraft() {
                    const draft = this.bacaDraft();

                    if (!draft || typeof draft !== 'object') {
                        return;
                    }

                    const isian = this.isianSaatIni();
                    const berbeda = (simpanan, sekarang) => Object.keys(simpanan ?? {})
                        .some((id) => id in sekarang && String(simpanan[id] ?? '') !== sekarang[id]);

                    const adaPerbedaan = berbeda(draft.sumatif, isian.sumatif) ||
                        berbeda(draft.sts, isian.sts) ||
                        (typeof draft.materi === 'string' && draft.materi !== isian.materi);

                    if (!adaPerbedaan) {
                        return;
                    }

                    const waktu = new Date(Number(draft.savedAt) || Date.now());

                    this.draft = draft;
                    this.jamDraft = String(waktu.getHours()).padStart(2, '0') + ':' +
                        String(waktu.getMinutes()).padStart(2, '0');
                    this.adaDraft = true;
                },

                pulihkanDraft() {
                    const draft = this.draft;

                    if (!draft) {
                        return;
                    }

                    this.baris().forEach((baris) => {
                        const id = baris.dataset.siswa;

                        if (draft.sumatif && id in draft.sumatif) {
                            baris.querySelector('input[data-nilai="sumatif"]').value = String(draft.sumatif[id] ?? '');
                        }

                        if (draft.sts && id in draft.sts) {
                            baris.querySelector('input[data-nilai="sts"]').value = String(draft.sts[id] ?? '');
                        }
                    });

                    const materi = this.kolomMateri();

                    if (materi && typeof draft.materi === 'string') {
                        materi.value = draft.materi;
                    }

                    this.adaDraft = false;
                    this.draft = null;
                    this.hitung();
                },

                buangDraft() {
                    try {
                        window.localStorage.removeItem(this.kunciDraft);
                    } catch (galat) {
                        // Abaikan: penyimpanan perangkat tidak tersedia.
                    }

                    this.adaDraft = false;
                    this.draft = null;
                },

                kolomBerikut(peristiwa) {
                    const kolom = this.kolom().filter((el) => {
                        const baris = el.closest('li[data-siswa]');

                        return !el.disabled && (!baris || this.cocok(baris.dataset.nama, baris.dataset.nis));
                    });
                    const posisi = kolom.indexOf(peristiwa.target);
                    const berikut = posisi === -1 ? null : kolom[posisi + 1];

                    if (berikut) {
                        berikut.focus();
                        berikut.select();

                        return;
                    }

                    peristiwa.target.blur();
                },

                kirim(peristiwa) {
                    if (!navigator.onLine) {
                        peristiwa.preventDefault();

                        return;
                    }

                    // Server memvalidasi `numeric`, jadi "8,5" harus menjadi "8.5" sebelum dikirim.
                    this.kolom().forEach((el) => this.rapikan(el));
                    this.menyimpan = true;
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

                angka(nilai) {
                    return parseFloat(String(nilai).trim().replace(',', '.'));
                },

                nilaiAkhir(sumatif, sts) {
                    const angkaSumatif = this.angka(sumatif);
                    const angkaSts = this.angka(sts);

                    if (isNaN(angkaSumatif) || isNaN(angkaSts)) {
                        return '—';
                    }

                    const total = (angkaSumatif * this.bobotSumatif + angkaSts * this.bobotSts) / 100;

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

                rapikan(input) {
                    const angka = this.angka(input.value);
                    let hasil = '';

                    if (String(input.value).trim() !== '' && !isNaN(angka)) {
                        const dibatasi = Math.min(100, Math.max(0, angka));
                        hasil = Number.isInteger(dibatasi) ? String(dibatasi) : dibatasi.toFixed(1);
                    }

                    if (input.value === hasil) {
                        return false;
                    }

                    input.value = hasil;

                    return true;
                },

                normalisasi(peristiwa) {
                    if (this.rapikan(peristiwa.target)) {
                        this.hitung();
                        this.simpanDraft();
                    }
                },

                terapkanIsiCepat() {
                    const angka = this.angka(this.nilaiCepat);

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
                    this.simpanDraft();
                },
            }));
        });
    </script>
</x-layouts.pwa>
