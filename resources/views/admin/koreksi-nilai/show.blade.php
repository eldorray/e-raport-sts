@php
    $namaMapel = $mengajar->mataPelajaran->nama_mapel ?? '—';
    $namaKelas = $mengajar->kelas->nama ?? '—';
    $namaGuru = $mengajar->guru?->nama;
    $bobotSumatif = (float) $bobotSumatif;
    $bobotSts = (float) $bobotSts;
    $currentMateriTp = old('materi_tp', optional($nilaiBySiswa->first())->materi_tp);
    $formatAwal = fn ($nilai): string => $nilai === null ? '' : (string) $nilai;
    $jumlahLengkapAwal = $siswas->filter(function ($siswa) use ($nilaiBySiswa): bool {
        $nilai = $nilaiBySiswa->get($siswa->id);

        return $nilai?->nilai_sumatif !== null && $nilai?->nilai_sts !== null;
    })->count();
    $batasPredikat = collect(config('rapor.grade_boundaries', []))
        ->map(fn ($minimum, $kunci) => [
            'minimum' => (float) $minimum,
            'predikat' => config('rapor.descriptors.' . $kunci . '.predikat', ''),
        ])
        ->sortByDesc('minimum')
        ->values();
@endphp

<x-layouts.app :title="__('Koreksi Nilai')">
    <a href="{{ route('koreksi-nilai.index', ['kelas' => $mengajar->kelas_id]) }}"
        class="mb-3 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">
        <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
        {{ __('Koreksi Nilai') }}
    </a>

    <div class="mb-4">
        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Koreksi Nilai Mapel') }}</p>
        <h1 class="text-xl font-bold text-gray-800 sm:text-2xl dark:text-gray-100">
            {{ $namaMapel }} — {{ $namaKelas }}
        </h1>
        <dl class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex gap-1">
                <dt>{{ __('Guru') }}:</dt>
                <dd class="font-semibold text-gray-800 dark:text-gray-100">{{ $namaGuru ?? '—' }}</dd>
            </div>
            <div class="flex gap-1">
                <dt>{{ __('Tahun Ajaran') }}:</dt>
                <dd>{{ $mengajar->tahunAjaran?->nama ?? '—' }} • {{ __('Semester') }} {{ $semester ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    @if (!$mengajar->guru_id)
        <div
            class="mb-4 flex items-start gap-2 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
            <i class="fas fa-user-slash mt-0.5" aria-hidden="true"></i>
            <span>{{ __('Jadwal ini belum punya guru pengampu. Atur guru di menu Mengajar sebelum mengoreksi nilai.') }}</span>
        </div>
    @elseif ($semester === null)
        <div
            class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200">
            {{ __('Semester jadwal mengajar ini tidak diketahui, jadi nilainya belum bisa dikoreksi.') }}
        </div>
    @endif

    @if ($tahunDitutup)
        <div
            class="mb-4 flex items-start gap-2 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
            <i class="fas fa-lock mt-0.5" aria-hidden="true"></i>
            <span>{{ __('Tahun ajaran ini sudah ditutup. Koreksi tetap bisa disimpan dan akan tercatat atas nama Anda.') }}</span>
        </div>
    @endif

    @if ($bisaSimpan && !$bobotValid)
        <div
            class="mb-4 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
            {{ __('Total bobot penilaian guru pengampu belum 100%. Minta guru memperbaiki bobotnya terlebih dahulu.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('koreksi-nilai.store', $mengajar) }}"
        x-data="koreksiNilai(@js([
            'bobotSumatif' => $bobotSumatif,
            'bobotSts' => $bobotSts,
            'bobotValid' => $bobotValid,
            'batasPredikat' => $batasPredikat,
            'predikatTerendah' => config('rapor.descriptors.perlu_bimbingan.predikat', ''),
            'belumTersimpan' => $errors->any() && session()->hasOldInput(),
        ]))"
        x-init="siap()" @submit="menyimpan = true" @pageshow.window="menyimpan = false"
        class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @csrf

        <div class="space-y-4 rounded-t-2xl border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <div class="flex flex-col gap-1">
                <label for="materi_tp" class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    {{ __('Materi / TP') }}</label>
                <input id="materi_tp" name="materi_tp" type="text" value="{{ $currentMateriTp }}" maxlength="255"
                    @input="hitung()" @disabled(!$bisaSimpan)
                    placeholder="{{ __('Contoh: Persamaan linear satu variabel') }}"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60 sm:text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 @error('materi_tp') border-red-500 dark:border-red-500 @enderror">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Berlaku untuk semua siswa di kelas ini. Mengubahnya ikut mencatat koreksi pada setiap baris nilai.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="font-semibold text-gray-600 dark:text-gray-300">{{ __('Bobot guru') }}:</span>
                <span
                    class="rounded-full border border-gray-200 bg-white px-2.5 py-1 font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ __('Sumatif') }} {{ rtrim(rtrim(number_format($bobotSumatif, 2, '.', ''), '0'), '.') }}%
                </span>
                <span
                    class="rounded-full border border-gray-200 bg-white px-2.5 py-1 font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ __('STS') }} {{ rtrim(rtrim(number_format($bobotSts, 2, '.', ''), '0'), '.') }}%
                </span>
                <span class="basis-full text-gray-500 dark:text-gray-400">
                    {{ __('Nilai 0-100. Nilai rapor dihitung dengan bobot guru pengampu.') }}</span>
            </div>
        </div>

        {{-- Judul kolom (layar lebar) --}}
        <div
            class="hidden border-b border-gray-200 bg-gray-100 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600 md:grid md:grid-cols-[2rem_minmax(0,1fr)_7rem_7rem_8rem] md:gap-3 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-400">
            <span>#</span>
            <span>{{ __('Siswa') }}</span>
            <span>{{ __('Rerata Sumatif') }}</span>
            <span>{{ __('SAS / STS') }}</span>
            <span>{{ __('Nilai Rapor') }}</span>
        </div>

        @if ($siswas->isEmpty())
            <p class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('Tidak ada siswa pada kelas ini.') }}</p>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-gray-700" @input="hitung()">
                @foreach ($siswas as $index => $siswa)
                    @php
                        $nilai = $nilaiBySiswa->get($siswa->id);
                        $sumatif = $nilai?->nilai_sumatif;
                        $sts = $nilai?->nilai_sts;
                        $hasil = $gradeService->calculateWithDescriptor($sumatif, $sts, null, $bobotSumatif, $bobotSts);
                        $kunciSumatif = 'nilai_sumatif.' . $siswa->id;
                        $kunciSts = 'nilai_sts.' . $siswa->id;
                    @endphp
                    <li data-siswa="{{ $siswa->id }}"
                        class="px-4 py-3 md:grid md:grid-cols-[2rem_minmax(0,1fr)_7rem_7rem_8rem] md:items-center md:gap-3"
                        :class="berubah[{{ $siswa->id }}] ? 'bg-blue-50/60 dark:bg-blue-900/20' : ''">
                        <div class="flex items-start gap-3 md:contents">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600 md:h-auto md:w-auto md:bg-transparent md:font-normal dark:bg-gray-700 dark:text-gray-300 md:dark:bg-transparent">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $siswa->nama }}
                                    <span style="display: none" x-show="berubah[{{ $siswa->id }}]"
                                        class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700 dark:bg-blue-900/60 dark:text-blue-200">{{ __('Diubah') }}</span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('NISN') }} {{ $siswa->nisn ?: '—' }}</p>
                                @if ($nilai?->dikoreksi_pada)
                                    <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-300">
                                        <i class="fas fa-pen-to-square text-[10px]" aria-hidden="true"></i>
                                        {{ __('Dikoreksi :nama, :waktu', [
                                            'nama' => $nilai->pengoreksi?->name ?? __('Admin'),
                                            'waktu' => $nilai->dikoreksi_pada->translatedFormat('d M Y H:i'),
                                        ]) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_5.5rem] items-end gap-2 md:contents">
                            <label class="block">
                                <span
                                    class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Sumatif') }}</span>
                                <input type="number" step="0.01" min="0" max="100" inputmode="decimal"
                                    name="nilai_sumatif[{{ $siswa->id }}]"
                                    value="{{ old($kunciSumatif, $sumatif) }}"
                                    data-nilai="sumatif" data-awal="{{ $formatAwal($sumatif) }}"
                                    aria-label="{{ __('Nilai sumatif :nama', ['nama' => $siswa->nama]) }}"
                                    @error($kunciSumatif) aria-invalid="true" @enderror
                                    @disabled(!$bisaSimpan)
                                    class="h-11 w-full rounded-lg border px-2 text-center text-base font-medium tabular-nums shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60 md:text-sm dark:bg-gray-900 dark:text-gray-100 @error($kunciSumatif) border-red-500 dark:border-red-500 @else border-gray-300 dark:border-gray-600 @enderror">
                            </label>
                            <label class="block">
                                <span
                                    class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('STS') }}</span>
                                <input type="number" step="0.01" min="0" max="100" inputmode="decimal"
                                    name="nilai_sts[{{ $siswa->id }}]"
                                    value="{{ old($kunciSts, $sts) }}"
                                    data-nilai="sts" data-awal="{{ $formatAwal($sts) }}"
                                    aria-label="{{ __('Nilai SAS / STS :nama', ['nama' => $siswa->nama]) }}"
                                    @error($kunciSts) aria-invalid="true" @enderror
                                    @disabled(!$bisaSimpan)
                                    class="h-11 w-full rounded-lg border px-2 text-center text-base font-medium tabular-nums shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60 md:text-sm dark:bg-gray-900 dark:text-gray-100 @error($kunciSts) border-red-500 dark:border-red-500 @else border-gray-300 dark:border-gray-600 @enderror">
                            </label>
                            <div class="min-w-0 text-center md:text-left">
                                <span
                                    class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Akhir') }}</span>
                                <div class="flex h-11 flex-col items-center justify-center leading-tight md:items-start">
                                    <span class="text-base font-bold tabular-nums text-gray-800 dark:text-gray-100"
                                        data-nilai-akhir>{{ $hasil['rapor'] ?? '—' }}</span>
                                    <span class="max-w-full truncate text-[10px] text-gray-500 dark:text-gray-400"
                                        data-predikat>{{ $hasil['descriptor']['predikat'] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Di HP bilah simpan menempel di bawah layar (konten layout tidak punya area gulir sendiri),
             disembunyikan saat menu samping dibuka; mulai md bilah ini kembali di akhir form. --}}
        <div @class([
            'flex items-center justify-between gap-3 rounded-b-2xl border-t border-gray-200 px-4 py-3 dark:border-gray-700',
            'fixed inset-x-0 bottom-0 z-30 rounded-none bg-white/95 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-4px_12px_rgba(0,0,0,0.08)] backdrop-blur md:static md:z-auto md:rounded-b-2xl md:bg-transparent md:pb-3 md:shadow-none md:backdrop-blur-none dark:bg-gray-800/95 md:dark:bg-transparent' => $bisaSimpan,
        ])
            @if ($bisaSimpan) :class="{ 'max-md:hidden': sidebarOpen }" @endif>
            <p class="min-w-0 text-xs text-gray-600 dark:text-gray-300">
                <span class="font-semibold tabular-nums"><span x-text="lengkap">{{ $jumlahLengkapAwal }}</span>/{{ $siswas->count() }}</span>
                {{ __('lengkap') }}
                <span style="display: none" x-show="jumlahBerubah > 0"
                    class="block text-blue-700 sm:inline dark:text-blue-300">
                    <span class="hidden sm:inline">•</span> <span x-text="jumlahBerubah"></span> {{ __('baris diubah') }}
                </span>
            </p>
            @if ($bisaSimpan)
                <button type="submit" :disabled="menyimpan"
                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-900">
                    <i class="fas fa-floppy-disk text-xs" aria-hidden="true"></i>
                    <span x-text="menyimpan ? @js(__('Menyimpan…')) : @js(__('Simpan Koreksi'))">{{ __('Simpan Koreksi') }}</span>
                </button>
            @endif
        </div>
    </form>

    @if ($bisaSimpan)
        {{-- Ruang agar baris terakhir tidak tertutup bilah simpan di HP --}}
        <div class="h-20 md:hidden" aria-hidden="true"></div>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('koreksiNilai', (konfigurasi) => ({
                menyimpan: false,
                lengkap: 0,
                berubah: {},
                jumlahBerubah: 0,
                dirty: konfigurasi.belumTersimpan,

                siap() {
                    this.hitung(false);

                    window.addEventListener('beforeunload', (peristiwa) => {
                        if (!this.dirty || this.menyimpan) {
                            return;
                        }

                        peristiwa.preventDefault();
                        peristiwa.returnValue = '';
                    });
                },

                hitung(tandaiDirty = true) {
                    let lengkap = 0;
                    const berubah = {};

                    this.$root.querySelectorAll('li[data-siswa]').forEach((baris) => {
                        const sumatif = baris.querySelector('input[data-nilai="sumatif"]');
                        const sts = baris.querySelector('input[data-nilai="sts"]');

                        if (sumatif.value.trim() !== '' && sts.value.trim() !== '') {
                            lengkap += 1;
                        }

                        berubah[baris.dataset.siswa] = this.beda(sumatif) || this.beda(sts);

                        const rapor = this.nilaiRapor(sumatif.value, sts.value);
                        baris.querySelector('[data-nilai-akhir]').textContent = rapor === null ? '—' : String(rapor);
                        baris.querySelector('[data-predikat]').textContent = rapor === null ? '' : this.predikat(rapor);
                    });

                    this.lengkap = lengkap;
                    this.berubah = berubah;
                    this.jumlahBerubah = Object.values(berubah).filter(Boolean).length;

                    if (tandaiDirty) {
                        this.dirty = true;
                    }
                },

                angka(nilai) {
                    const teks = String(nilai ?? '').trim();

                    return teks === '' ? null : parseFloat(teks.replace(',', '.'));
                },

                beda(input) {
                    const sekarang = this.angka(input.value);
                    const awal = this.angka(input.dataset.awal);

                    if (sekarang === null || awal === null) {
                        return sekarang !== awal;
                    }

                    return Math.abs(sekarang - awal) > 0.000001;
                },

                nilaiRapor(sumatif, sts) {
                    const angkaSumatif = this.angka(sumatif);
                    const angkaSts = this.angka(sts);

                    const sah = (angka) => angka !== null && !isNaN(angka) && angka >= 0 && angka <= 100;

                    if (!konfigurasi.bobotValid || !sah(angkaSumatif) || !sah(angkaSts)) {
                        return null;
                    }

                    const total = (angkaSumatif * konfigurasi.bobotSumatif + angkaSts * konfigurasi.bobotSts) / 100;

                    return Math.round(total * 100) / 100;
                },

                predikat(rapor) {
                    const batas = konfigurasi.batasPredikat.find((item) => rapor >= item.minimum);

                    return batas ? batas.predikat : konfigurasi.predikatTerendah;
                },
            }));
        });
    </script>
</x-layouts.app>
