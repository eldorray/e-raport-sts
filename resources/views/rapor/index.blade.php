<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Master Data</p>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Cetak Rapor Siswa</h1>
        </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Tingkat</label>
            <form id="filterForm" method="GET" class="contents">
                <select name="tingkat" onchange="document.getElementById('filterForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @if ($isGuru ?? false) disabled @endif>
                    @unless ($isGuru ?? false)
                        <option value="">Semua</option>
                    @endunless
                    @foreach ($tingkatOptions as $opt)
                        @if (($isGuru ?? false) && $kelasList->where('tingkat', $opt)->isEmpty())
                            @continue
                        @endif
                        <option value="{{ $opt }}" @selected($tingkat == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @if ($isGuru ?? false)
                    <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                @endif
            </form>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</label>
            <form id="kelasForm" method="GET" class="contents">
                <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                <select name="kelas_id" onchange="document.getElementById('kelasForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    @if ($isGuru ?? false) disabled @endif>
                    @unless ($isGuru ?? false)
                        <option value="">Semua</option>
                    @endunless
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected((string) $kelasId === (string) $kelas->id)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
                @if ($isGuru ?? false)
                    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                @endif
            </form>
        </div>
    </div>

    <div
        class="px-6 pb-6overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table id="rapor-table"
                class="p-6 min-w-full divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">NISN</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">L/P</th>
                        <th class="px-3 py-3">TTL</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($siswas as $index => $siswa)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $siswa->nisn ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $siswa->nama }}</td>
                            <td class="px-3 py-3">{{ strtoupper(substr($siswa->jenis_kelamin ?? '-', 0, 1)) }}</td>
                            <td class="px-3 py-3 text-xs">
                                {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">

                                    <a href="{{ route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">
                                        <i class="fas fa-file-lines text-[11px]"></i> Rapor
                                    </a>
                                    <a href="{{ $siswa->kelas_id ? route('rapor.ledger', ['kelas' => $siswa->kelas_id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) : '#' }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100">
                                        <i class="fas fa-table-list text-[11px]"></i> Ledger
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
