<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ __('Penilaian Mapel') }}</p>
            <h1 class="text-xl font-bold text-gray-800 md:text-2xl dark:text-gray-100">
                {{ $mengajar->mataPelajaran->nama_mapel ?? '—' }} — {{ $mengajar->kelas->nama ?? '—' }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
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
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/30">
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
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/30">
            <div class="flex items-center gap-2 text-sm font-semibold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __('Tahun ajaran yang dipilih tidak aktif. Anda hanya dapat melihat data, tidak dapat mengubah data.') }}
            </div>
        </div>
    @endif

    {{-- Di HP: overflow-clip (bukan hidden) agar bilah simpan bisa menempel di bawah layar --}}
    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm max-md:overflow-clip dark:border-gray-700 dark:bg-gray-800">
        <form method="POST" action="{{ route('guru.penilaian.store', $mengajar) }}"
            x-data="{ menyimpan: false }" @submit="menyimpan = true" @pageshow.window="menyimpan = false">
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

            <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col gap-1">
                        <label for="materi_tp" class="text-sm font-semibold text-gray-800 dark:text-gray-100">Materi /
                            TP</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Tuliskan materi atau tujuan pembelajaran yang menjadi acuan penyusunan capaian kompetensi.') }}
                        </p>
                    </div>
                    <div class="flex flex-1 flex-col gap-1 md:max-w-xl">
                        <input id="materi_tp" name="materi_tp" type="text" value="{{ $currentMateriTp }}"
                            maxlength="255" @disabled(!$canEdit)
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium shadow-sm focus:border-blue-500 focus:ring-blue-500 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed"
                            placeholder="{{ __('Contoh: Persamaan linear satu variabel') }}">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Materi/TP ini digunakan dalam pola deskripsi capaian.') }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Bobot Penilaian
                            (%)</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Bobot Sumatif dan STS berlaku global untuk semua mapel.') }}
                        </p>
                    </div>
                    <div
                        class="flex flex-wrap items-center gap-3 md:max-w-xl text-sm font-semibold text-gray-700 dark:text-gray-200">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-600 dark:text-gray-300">Sumatif</span>
                            <span
                                class="px-3 py-1 rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">{{ number_format($bobotSumatif, 2) }}%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-600 dark:text-gray-300">STS</span>
                            <span
                                class="px-3 py-1 rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">{{ number_format($bobotSts, 2) }}%</span>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            {{ __('Total') }}: {{ number_format($bobotTotal, 2) }}%
                        </div>
                    </div>
                </div>
            </div>

            {{-- Satu markup: tabel di layar lebar, satu kartu per siswa di HP (input tetap sama) --}}
            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 text-sm text-gray-800 max-md:block dark:divide-gray-700 dark:text-gray-100">
                    <thead
                        class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 max-md:hidden dark:bg-gray-900/40 dark:text-gray-400">
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
                    <tbody class="divide-y divide-gray-200 max-md:block dark:divide-gray-700">
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
                            <tr class="max-md:grid max-md:grid-cols-3 max-md:gap-x-3 max-md:gap-y-3 max-md:px-4 max-md:py-4">
                                <td class="px-4 py-3 max-md:hidden">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 max-md:hidden">{{ $siswa->nisn ?? '—' }}</td>
                                <td class="px-4 py-3 max-md:col-span-3 max-md:p-0">
                                    <div class="flex items-baseline gap-2">
                                        <span
                                            class="text-xs font-semibold tabular-nums text-gray-400 md:hidden dark:text-gray-500">{{ $index + 1 }}.</span>
                                        <span class="max-md:font-semibold">{{ $siswa->nama }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 md:hidden dark:text-gray-400">NISN
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
                                        class="mb-1 block text-xs font-medium text-gray-500 md:hidden dark:text-gray-400">{{ __('Rerata Sumatif') }}</label>
                                    <input type="number" step="0.01" min="0" max="100"
                                        id="nilai_sumatif_{{ $siswa->id }}" name="nilai_sumatif[{{ $siswa->id }}]"
                                        value="{{ old('nilai_sumatif.' . $siswa->id, $sumatif) }}"
                                        aria-label="{{ __('Nilai sumatif :nama', ['nama' => $siswa->nama]) }}"
                                        @disabled(!$canEdit)
                                        class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-center text-sm font-medium shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500 max-md:h-11 max-md:w-full max-md:text-base dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400 disabled:opacity-60 disabled:cursor-not-allowed">
                                </td>
                                <td class="px-4 py-3 max-md:p-0">
                                    <label for="nilai_sts_{{ $siswa->id }}"
                                        class="mb-1 block text-xs font-medium text-gray-500 md:hidden dark:text-gray-400">{{ __('SAS / STS') }}</label>
                                    <input type="number" step="0.01" min="0" max="100"
                                        id="nilai_sts_{{ $siswa->id }}" name="nilai_sts[{{ $siswa->id }}]"
                                        value="{{ old('nilai_sts.' . $siswa->id, $sts) }}"
                                        aria-label="{{ __('Nilai SAS / STS :nama', ['nama' => $siswa->nama]) }}"
                                        @disabled(!$canEdit)
                                        class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-center text-sm font-medium shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500 max-md:h-11 max-md:w-full max-md:text-base dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400 disabled:opacity-60 disabled:cursor-not-allowed">
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-md:p-0 dark:text-gray-300">
                                    <span
                                        class="mb-1 block text-xs font-medium text-gray-500 md:hidden dark:text-gray-400">{{ __('Nilai Rapor') }}</span>
                                    <span
                                        class="max-md:flex max-md:h-11 max-md:items-center max-md:justify-center max-md:rounded-lg max-md:bg-gray-50 max-md:text-base max-md:font-semibold max-md:text-gray-800 max-md:dark:bg-gray-900/60 max-md:dark:text-gray-100">{{ $rapor ?? '—' }}</span>
                                </td>
                                <td
                                    class="px-4 py-3 text-gray-500 max-md:col-span-3 max-md:p-0 dark:text-gray-400 {{ $descriptor ? '' : 'max-md:hidden' }}">
                                    @if ($descriptor)
                                        <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">
                                            {{ $descriptor['predikat'] }} • {{ $descriptor['keterangan'] }}
                                        </div>
                                        <p class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-300">
                                            {{ $descriptor['kalimat'] }}</p>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="max-md:block">
                                <td colspan="7"
                                    class="px-4 py-6 text-center text-sm text-gray-500 max-md:block dark:text-gray-400">
                                    {{ __('Tidak ada siswa pada kelas ini.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Di HP pembungkus ini "contents": keterangan tetap di bawah daftar, tombol menjadi
                 bilah yang menempel di atas menu bawah aplikasi --}}
            <div class="max-md:contents md:flex md:items-center md:justify-between md:px-4 md:py-4">
                <p class="text-xs text-gray-500 max-md:border-t max-md:border-gray-200 max-md:px-4 max-md:py-3 dark:text-gray-400 max-md:dark:border-gray-700">
                    {{ __('Nilai 0-100. Kolom Sumatif dan STS dapat langsung diubah. Nilai Rapor dihitung dengan bobot yang Anda tentukan di atas.') }}
                </p>
                @if ($canEdit)
                    <div
                        class="flex items-center gap-3 max-md:z-20 max-md:border-t max-md:border-gray-200 max-md:bg-white/95 max-md:px-4 max-md:py-3 max-md:backdrop-blur max-md:in-[.mode-aplikasi]:sticky max-md:in-[.mode-aplikasi]:bottom-[calc(4.75rem+env(safe-area-inset-bottom))] max-md:dark:border-gray-700 max-md:dark:bg-gray-800/95">
                        {{-- Reset button placeholder - actual form is outside --}}
                        <button type="button" id="reset-trigger"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 max-md:h-11 max-md:shrink-0 dark:border-red-600 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ __('Reset Nilai') }}
                        </button>
                        <button type="submit" :disabled="menyimpan"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 max-md:h-11 max-md:flex-1 max-md:justify-center dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-900">
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
