<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rapor</p>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Raport Tahfidz Al-Qur'an</h1>
        </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</label>
            <form id="kelasForm" method="GET" class="contents">
                <select name="kelas_id" onchange="document.getElementById('kelasForm').submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected((string) $kelasId === (string) $kelas->id)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700 dark:bg-green-900 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table
                class="p-6 min-w-full divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">NISN</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">L/P</th>
                        <th class="px-3 py-3 text-center">Jml Surah</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($siswas as $index => $siswa)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $siswa->nisn ?? '—' }}</td>
                            <td class="px-3 py-3 font-medium">{{ $siswa->nama }}</td>
                            <td class="px-3 py-3">{{ strtoupper(substr($siswa->jenis_kelamin ?? '-', 0, 1)) }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $siswa->jumlah_surah > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $siswa->jumlah_surah }} / 38
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">
                                    <a href="{{ route('tahfidz.show', $siswa->id) }}"
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 dark:bg-amber-900/40 dark:text-amber-100">
                                        <i class="fas fa-edit text-[11px]"></i> Input
                                    </a>
                                    @if ($siswa->tahfidz)
                                        <a href="{{ route('tahfidz.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahunId, 'semester' => $semester]) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-100">
                                            <i class="fas fa-print text-[11px]"></i> Cetak
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                @if ($kelasId)
                                    Belum ada siswa di kelas ini.
                                @else
                                    Pilih kelas terlebih dahulu.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
