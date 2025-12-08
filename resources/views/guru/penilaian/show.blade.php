<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ __('Penilaian Mapel') }}</p>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ $mengajar->mataPelajaran->nama_mapel ?? '—' }} — {{ $mengajar->kelas->nama ?? '—' }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Semester') }} {{ $semester ?? '—' }} • {{ __('Tahun Ajaran') }}
                {{ $mengajar->tahunAjaran->nama ?? '—' }}
            </p>
        </div>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form method="POST" action="{{ route('guru.penilaian.store', $mengajar) }}">
            @csrf
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
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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

            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 text-sm text-gray-800 dark:divide-gray-700 dark:text-gray-100">
                    <thead
                        class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
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
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($siswas as $index => $siswa)
                            @php
                                $nilai = $nilaiBySiswa[$siswa->id] ?? null;
                                $sumatif = $nilai?->nilai_sumatif;
                                $sts = $nilai?->nilai_sts;

                                $rapor = null;
                                if ($sumatif !== null && $sts !== null && abs($bobotTotal - 100) <= 0.01) {
                                    $rapor = round(($sumatif * $bobotSumatif + $sts * $bobotSts) / 100, 2);
                                }

                                $materiText = $currentMateriTp ?: __('Materi/TP belum diisi');
                                $descriptor = null;

                                if ($rapor !== null) {
                                    if ($rapor >= 86) {
                                        $descriptor = [
                                            'predikat' => 'Sangat Baik',
                                            'keterangan' => 'Sangat Menguasai',
                                            'kalimat' => str_replace(
                                                '[Materi/TP]',
                                                $materiText,
                                                'Peserta didik menunjukkan penguasaan yang sangat baik dalam [Materi/TP].',
                                            ),
                                        ];
                                    } elseif ($rapor >= 76) {
                                        $descriptor = [
                                            'predikat' => 'Baik',
                                            'keterangan' => 'Sudah Mampu',
                                            'kalimat' => str_replace(
                                                '[Materi/TP]',
                                                $materiText,
                                                'Peserta didik menunjukkan penguasaan yang baik dalam [Materi/TP].',
                                            ),
                                        ];
                                    } elseif ($rapor >= 61) {
                                        $descriptor = [
                                            'predikat' => 'Cukup',
                                            'keterangan' => 'Mulai Berkembang',
                                            'kalimat' => str_replace(
                                                ['[Materi/TP]', '[Sub-bagian tertentu]'],
                                                [$materiText, 'bagian tertentu'],
                                                'Peserta didik cukup mampu dalam [Materi/TP], namun masih perlu bimbingan pada [Sub-bagian tertentu].',
                                            ),
                                        ];
                                    } else {
                                        $descriptor = [
                                            'predikat' => 'Perlu Bimbingan',
                                            'keterangan' => 'Belum Mencapai',
                                            'kalimat' => str_replace(
                                                '[Materi/TP]',
                                                $materiText,
                                                'Peserta didik memerlukan bimbingan dalam [Materi/TP].',
                                            ),
                                        ];
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">{{ $siswa->nisn ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $siswa->nama }}</td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="nilai_sumatif[{{ $siswa->id }}]" value="{{ $sumatif }}"
                                        class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-center text-sm font-medium shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" max="100"
                                        name="nilai_sts[{{ $siswa->id }}]" value="{{ $sts }}"
                                        class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-center text-sm font-medium shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $rapor ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
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
                            <tr>
                                <td colspan="7"
                                    class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Tidak ada siswa pada kelas ini.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between px-4 py-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Nilai 0-100. Kolom Sumatif dan STS dapat langsung diubah. Nilai Rapor dihitung dengan bobot yang Anda tentukan di atas.') }}
                </p>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-900">
                    {{ __('Simpan Nilai') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
