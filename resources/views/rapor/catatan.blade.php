<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rapor</p>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Catatan Wali Kelas</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tahun Pelajaran: {{ $tahun->nama ?? $tahunId }}
                • Semester: {{ ucfirst($semester) }}</p>
        </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        <form method="GET" class="contents">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @foreach ($kelasList as $k)
                        <option value="{{ $k->id }}" @selected((string) $kelasId === (string) $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if (!$kelas)
        <div
            class="rounded-xl border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Pilih kelas untuk mengisi catatan wali kelas.
        </div>
    @else
        @if (!$canEdit)
            <div
                class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/30">
                <div class="flex items-center gap-2 text-sm font-semibold text-amber-700 dark:text-amber-300">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ __('Tahun ajaran yang dipilih tidak aktif. Anda hanya dapat melihat data, tidak dapat mengubah data.') }}
                </div>
            </div>
        @endif

        {{-- Alerts are handled in the base layout --}}

        <form method="POST" action="{{ route('rapor.catatan.store') }}"
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                    <thead
                        class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3 text-left">#</th>
                            <th class="px-3 py-3 text-left">NISN</th>
                            <th class="px-3 py-3 text-left">Nama</th>
                            <th class="px-3 py-3 text-left">Catatan Wali</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($siswas as $index => $siswa)
                            @php($val = $catatan[$siswa->id] ?? '')
                            <tr>
                                <td class="px-3 py-3">{{ $index + 1 }}</td>
                                <td class="px-3 py-3">{{ $siswa->nisn ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $siswa->nama }}</td>
                                <td class="px-3 py-3">
                                    <textarea name="catatan[{{ $siswa->id }}]" rows="2" placeholder="Catatan singkat dari wali kelas"
                                        @disabled(!$canEdit)
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 disabled:opacity-60 disabled:cursor-not-allowed">{{ old('catatan.' . $siswa->id, $val) }}</textarea>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada
                                    siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div
                class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/30">
                @if ($canEdit)
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <i class="fas fa-save text-xs"></i> Simpan Catatan
                    </button>
                @endif
            </div>
        </form>
    @endif
</x-layouts.app>
