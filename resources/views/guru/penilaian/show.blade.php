<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">
                {{ $mengajar->mataPelajaran->nama_mapel ?? '—' }} — {{ $mengajar->kelas->nama ?? '—' }}
            </h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Semester') }} {{ $semester ?? '—' }} • {{ __('Tahun Ajaran') }}
                {{ $mengajar->tahunAjaran->nama ?? '—' }}
            </p>
        </div>
    </div>

    @php
        $barisDikoreksi = $nilaiBySiswa->filter(fn ($nilai) => $nilai->dikoreksi_pada !== null);
        $koreksiTerakhir = $barisDikoreksi->sortByDesc(fn ($nilai) => $nilai->dikoreksi_pada->getTimestamp())->first();
    @endphp

    @if ($koreksiTerakhir)
        <div class="mb-4 rounded-2xl bg-blue-100 p-4 dark:bg-blue-900/30">
            <div class="flex items-start gap-2 text-sm text-blue-800 dark:text-blue-200">
                <i class="fas fa-pen-to-square mt-0.5" aria-hidden="true"></i>
                <span>
                    {{ __(':jumlah nilai siswa dikoreksi admin, terakhir oleh :nama pada :waktu. Baris yang dikoreksi diberi catatan di bawah nama siswa.', [
                        'jumlah' => $barisDikoreksi->count(),
                        'nama' => $koreksiTerakhir->pengoreksi?->name ?? __('Admin'),
                        'waktu' => $koreksiTerakhir->dikoreksi_pada->translatedFormat('d M Y H:i'),
                    ]) }}
                </span>
            </div>
        </div>
    @endif

    @if (!$canEdit)
        <div class="mb-4 rounded-2xl bg-amber-100 p-4 dark:bg-amber-900/30">
            <div class="flex items-center gap-2 text-sm font-semibold text-amber-900 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __('Tahun ajaran yang dipilih tidak aktif. Anda hanya dapat melihat data, tidak dapat mengubah data.') }}
            </div>
        </div>
    @endif

    {{-- Di HP: overflow-clip (bukan hidden) agar bilah simpan bisa menempel di bawah layar --}}
    <div
        class="overflow-hidden rounded-[20px] bg-white shadow-[0_1px_2px_rgba(0,0,0,.05)] max-md:overflow-clip dark:border-zinc-700 dark:bg-zinc-800">
        <form method="POST" action="{{ route('guru.penilaian.store', $mengajar) }}"
            x-data="{
                menyimpan: false,
                ubah: 0,
                bobotSumatif: {{ (float) $bobotSumatif }},
                bobotSts: {{ (float) $bobotSts }},
                bobotValid: {{ $gradeService->calculateRapor(100, 100, (float) $bobotSumatif, (float) $bobotSts) !== null ? 'true' : 'false' }},
                // Pratinjau saja: nilai rapor yang tersimpan tetap dihitung GradeService di server.
                catat(isian) {
                    if (!isian.dataset.ubah) {
                        isian.dataset.ubah = '1';
                        this.ubah++;
                    }
                    const baris = isian.closest('[data-baris]');
                    if (!baris) return;
                    baris.dataset.ubah = '1';
                    const sumatif = baris.querySelector('[data-nilai=sumatif]').value;
                    const sts = baris.querySelector('[data-nilai=sts]').value;
                    const lengkap = sumatif !== '' && sts !== '' && this.bobotValid;
                    baris.querySelector('[data-rapor]').textContent = lengkap ?
                        Math.round(sumatif * this.bobotSumatif + sts * this.bobotSts) / 100 : '—';
                },
            }" @input="catat($event.target)" @submit="menyimpan = true"
            @pageshow.window="menyimpan = false">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
            <input type="hidden" name="semester" value="{{ $semester }}">
            {{-- Both Sumatif & STS saved together --}}

            @php
                $currentMateriTp = old('materi_tp', optional($nilaiBySiswa->first())->materi_tp);
                $bobotSumatif = (float) $bobotSumatif;
                $bobotSts = (float) $bobotSts;
                $bobotTotal = $bobotSumatif + $bobotSts;
            @endphp

            <div class="px-5 pb-1 pt-5 max-md:px-4">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col gap-1">
                        <label for="materi_tp" class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Materi /
                            TP</label>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">
                            {{ __('Tuliskan materi atau tujuan pembelajaran yang menjadi acuan penyusunan capaian kompetensi.') }}
                        </p>
                    </div>
                    <div class="flex flex-1 flex-col gap-1 md:max-w-xl">
                        <input id="materi_tp" name="materi_tp" type="text" value="{{ $currentMateriTp }}"
                            maxlength="255" @disabled(!$canEdit)
                            class="h-10 w-full rounded-xl border-0 bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent px-3 text-sm font-medium max-md:h-11 max-md:text-base dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus:bg-zinc-900 dark:text-zinc-100 disabled:cursor-not-allowed disabled:opacity-60"
                            placeholder="{{ __('Contoh: Persamaan linear satu variabel') }}">
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">
                            {{ __('Materi/TP ini digunakan dalam pola deskripsi capaian.') }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Bobot Penilaian
                            (%)</label>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400">
                            {{ __('Bobot Sumatif dan STS berlaku global untuk semua mapel.') }}
                        </p>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-3 md:max-w-xl text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-zinc-600 dark:text-zinc-300">Sumatif</span>
                            <span
                                class="inline-flex h-7 items-center rounded-full bg-zinc-100 px-3 tabular-nums dark:bg-zinc-700">{{ number_format($bobotSumatif, 2) }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-zinc-600 dark:text-zinc-300">STS</span>
                            <span
                                class="inline-flex h-7 items-center rounded-full bg-zinc-100 px-3 tabular-nums dark:bg-zinc-700">{{ number_format($bobotSts, 2) }}%</span>
                        </div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-300">
                            {{ __('Total') }}: {{ number_format($bobotTotal, 2) }}%
                        </div>
                        <a href="{{ route('penilaian.bobot.edit') }}"
                            class="text-[13px] font-medium text-accent-strong hover:underline dark:text-blue-300">{{ __('Ubah Bobot') }}</a>
                    </div>
                </div>
            </div>

            {{-- Satu markup: tabel di layar lebar, satu kartu per siswa di HP (input tetap sama) --}}
            <div class="overflow-x-auto md:px-5 md:pt-4">
                <table
                    class="min-w-full border-separate border-spacing-0 text-sm text-zinc-800 max-md:block dark:text-zinc-100">
                    <thead
                        class="text-xs font-semibold text-zinc-600 max-md:hidden dark:text-zinc-400 [&_th]:bg-zinc-100 [&_th:first-child]:rounded-l-xl [&_th:last-child]:rounded-r-xl dark:[&_th]:bg-zinc-700/60">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">NISN</th>
                            <th class="px-4 py-3 text-left">{{ __('Nama') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Rerata Sumatif') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('SAS / STS') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Nilai Rapor') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Capaian Kompetensi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="max-md:block max-md:divide-y max-md:divide-zinc-100 md:[&_td]:border-b md:[&_td]:border-zinc-100 dark:max-md:divide-zinc-700 dark:md:[&_td]:border-zinc-700">
                        @forelse ($siswas as $index => $siswa)
                            @php
                                $nilai = $nilaiBySiswa[$siswa->id] ?? null;
                                $sumatif = $nilai?->nilai_sumatif;
                                $sts = $nilai?->nilai_sts;

                                $hasil = $gradeService->calculateWithDescriptor(
                                    $sumatif,
                                    $sts,
                                    $currentMateriTp,
                                    $bobotSumatif,
                                    $bobotSts,
                                );
                                $rapor = $hasil['rapor'];
                                $descriptor = $hasil['descriptor'];
                            @endphp
                            <tr data-baris class="group max-md:grid max-md:grid-cols-3 max-md:gap-x-3 max-md:gap-y-3 max-md:px-4 max-md:py-4">
                                <td class="px-4 py-3 max-md:hidden">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 max-md:hidden">{{ $siswa->nisn ?? '—' }}</td>
                                <td class="px-4 py-3 max-md:col-span-3 max-md:p-0">
                                    <div class="flex items-baseline gap-2">
                                        <span
                                            class="text-xs font-semibold tabular-nums text-zinc-400 md:hidden dark:text-zinc-600">{{ $index + 1 }}.</span>
                                        <span class="max-md:font-semibold">{{ $siswa->nama }}</span>
                                    </div>
                                    <p class="text-xs text-zinc-600 md:hidden dark:text-zinc-400">NISN
                                        {{ $siswa->nisn ?? '—' }}</p>
                                    @if ($nilai?->dikoreksi_pada)
                                        <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-300">
                                            {{ __('Dikoreksi :nama, :waktu', [
                                                'nama' => $nilai->pengoreksi?->name ?? __('Admin'),
                                                'waktu' => $nilai->dikoreksi_pada->translatedFormat('d M Y H:i'),
                                            ]) }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 max-md:p-0">
                                    <label for="nilai_sumatif_{{ $siswa->id }}"
                                        class="mb-1 block text-xs font-medium text-zinc-600 md:hidden dark:text-zinc-400">{{ __('Rerata Sumatif') }}</label>
                                    <input type="number" step="0.01" min="0" max="100"
                                        id="nilai_sumatif_{{ $siswa->id }}" data-nilai="sumatif" name="nilai_sumatif[{{ $siswa->id }}]"
                                        value="{{ old('nilai_sumatif.' . $siswa->id, $sumatif) }}"
                                        aria-label="{{ __('Nilai sumatif :nama', ['nama' => $siswa->nama]) }}"
                                        @disabled(!$canEdit)
                                        class="h-9 w-24 rounded-[10px] border-0 bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent px-2 text-center text-sm font-medium tabular-nums max-md:h-11 max-md:w-full max-md:text-base dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus:bg-zinc-900 dark:text-zinc-100 disabled:cursor-not-allowed disabled:opacity-60">
                                </td>
                                <td class="px-4 py-3 max-md:p-0">
                                    <label for="nilai_sts_{{ $siswa->id }}"
                                        class="mb-1 block text-xs font-medium text-zinc-600 md:hidden dark:text-zinc-400">{{ __('SAS / STS') }}</label>
                                    <input type="number" step="0.01" min="0" max="100"
                                        id="nilai_sts_{{ $siswa->id }}" data-nilai="sts" name="nilai_sts[{{ $siswa->id }}]"
                                        value="{{ old('nilai_sts.' . $siswa->id, $sts) }}"
                                        aria-label="{{ __('Nilai SAS / STS :nama', ['nama' => $siswa->nama]) }}"
                                        @disabled(!$canEdit)
                                        class="h-9 w-24 rounded-[10px] border-0 bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent px-2 text-center text-sm font-medium tabular-nums max-md:h-11 max-md:w-full max-md:text-base dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus:bg-zinc-900 dark:text-zinc-100 disabled:cursor-not-allowed disabled:opacity-60">
                                </td>
                                <td class="px-4 py-3 text-zinc-600 max-md:p-0 dark:text-zinc-300">
                                    <span
                                        class="mb-1 block text-xs font-medium text-zinc-600 md:hidden dark:text-zinc-400">{{ __('Nilai Rapor') }}</span>
                                    <span
                                        data-rapor class="tabular-nums md:text-base md:font-semibold md:text-zinc-900 md:dark:text-zinc-50 max-md:flex max-md:h-11 max-md:items-center max-md:justify-center max-md:rounded-[10px] max-md:bg-zinc-100 max-md:text-base max-md:font-semibold max-md:text-zinc-900 max-md:dark:bg-zinc-700/60 max-md:dark:text-zinc-100">{{ $rapor ?? '—' }}</span>
                                </td>
                                <td
                                    class="px-4 py-3 text-zinc-600 transition-opacity duration-200 group-data-[ubah]:opacity-40 max-md:col-span-3 max-md:p-0 dark:text-zinc-400 {{ $descriptor ? '' : 'max-md:hidden' }}"
                                    title="{{ __('Capaian diperbarui setelah nilai disimpan.') }}">
                                    @if ($descriptor)
                                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-100">
                                            {{ $descriptor['predikat'] }} • {{ $descriptor['keterangan'] }}
                                        </div>
                                        <p class="mt-1 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">
                                            {{ $descriptor['kalimat'] }}</p>
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="max-md:block">
                                <td colspan="7"
                                    class="px-4 py-6 text-center text-sm text-zinc-600 max-md:block dark:text-zinc-400">
                                    {{ __('Tidak ada siswa pada kelas ini.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Di HP pembungkus ini "contents": keterangan tetap di bawah daftar, tombol menjadi
                 bilah yang menempel di atas menu bawah aplikasi --}}
            <div class="max-md:contents md:flex md:items-center md:justify-between md:gap-4 md:px-5 md:py-4">
                <p class="text-xs text-zinc-600 max-md:border-t max-md:border-zinc-200 max-md:px-4 max-md:py-3 dark:text-zinc-400 max-md:dark:border-zinc-700">
                    {{ __('Nilai 0-100. Kolom Sumatif dan STS dapat langsung diubah. Nilai Rapor dihitung dengan bobot yang Anda tentukan di atas.') }}
                </p>
                @if ($canEdit)
                    <div
                        class="flex shrink-0 items-center gap-3 max-md:z-20 max-md:border-t max-md:border-zinc-200 max-md:bg-white/95 max-md:px-4 max-md:py-3 max-md:backdrop-blur max-md:in-[.mode-aplikasi]:sticky max-md:in-[.mode-aplikasi]:bottom-[calc(4.75rem+env(safe-area-inset-bottom))] max-md:dark:border-zinc-700 max-md:dark:bg-zinc-800/95">
                        <span x-show="ubah > 0" style="display: none" role="status"
                            x-transition:enter="transition duration-300 ease-[cubic-bezier(.32,.72,0,1)]"
                            x-transition:enter-start="opacity-0 translate-y-1.5 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="inline-flex h-7 items-center whitespace-nowrap rounded-full bg-amber-100 px-3 text-xs font-semibold text-amber-900 max-md:hidden dark:bg-amber-900/40 dark:text-amber-300"><span
                                x-text="ubah"></span>&nbsp;{{ __('perubahan belum disimpan') }}</span>
                        {{-- Reset button placeholder - actual form is outside --}}
                        <button type="button" id="reset-trigger"
                            class="inline-flex h-10 items-center gap-2 whitespace-nowrap rounded-xl bg-rose-100 px-4 text-sm font-semibold text-rose-800 transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97] focus:outline-none focus-visible:ring-4 focus-visible:ring-rose-500/30 max-md:h-11 max-md:shrink-0 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ __('Reset Nilai') }}
                        </button>
                        <button type="submit" :disabled="menyimpan"
                            class="inline-flex h-10 items-center gap-2 whitespace-nowrap rounded-xl bg-accent px-4 text-sm font-semibold text-white transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97] focus:outline-none focus-visible:ring-4 focus-visible:ring-accent/30 disabled:cursor-not-allowed disabled:opacity-60 max-md:h-11 max-md:flex-1 max-md:justify-center">
                            <svg x-show="menyimpan" style="display: none" aria-hidden="true" class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                                <path d="M12 3a9 9 0 1 0 9 9" />
                            </svg>
                            {{ __('Simpan Nilai') }}
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- Hidden reset form outside main form --}}
    @if ($canEdit)
        <form id="reset-form" method="POST" action="{{ route('guru.penilaian.reset', $mengajar) }}" class="hidden">
            @csrf
            @method('DELETE')
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
            <input type="hidden" name="semester" value="{{ $semester }}">
        </form>

        <script>
            document.getElementById('reset-trigger').addEventListener('click', function() {
                if (confirm('{{ __('Yakin ingin mereset semua nilai? Tindakan ini tidak dapat dibatalkan.') }}')) {
                    document.getElementById('reset-form').submit();
                }
            });
        </script>
    @endif
</x-layouts.app>
