<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">Catatan Wali Kelas</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Tahun Pelajaran: {{ $tahun->nama ?? $tahunId }}
                • Semester: {{ ucfirst($semester) }}</p>
        </div>
    </div>

    <x-wali-tabs aktif="catatan" />

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        <form method="GET" class="contents">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="w-full h-10 rounded-xl border-0 bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent px-3 text-sm max-md:h-11 max-md:text-base dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus:bg-zinc-900 dark:text-zinc-100">
                    @foreach ($kelasList as $k)
                        <option value="{{ $k->id }}" @selected((string) $kelasId === (string) $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if (!$kelas)
        <div
            class="rounded-[20px] border border-dashed border-zinc-300 bg-white p-6 text-center text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            Pilih kelas untuk mengisi catatan wali kelas.
        </div>
    @else
        @if (!$canEdit)
            <div
                class="mb-4 rounded-2xl bg-amber-100 p-4 dark:bg-amber-900/30">
                <div class="flex items-center gap-2 text-sm font-semibold text-amber-900 dark:text-amber-300">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ __('Tahun ajaran yang dipilih tidak aktif. Anda hanya dapat melihat data, tidak dapat mengubah data.') }}
                </div>
            </div>
        @endif

        {{-- Alerts are handled in the base layout --}}

        <form method="POST" action="{{ route('rapor.catatan.store') }}"
            class="overflow-hidden rounded-[20px] bg-white shadow-[0_1px_2px_rgba(0,0,0,.05)] max-md:overflow-clip dark:border-zinc-700 dark:bg-zinc-800">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
            <input type="hidden" name="semester" value="{{ $semester }}">
            {{-- Di HP setiap siswa tampil sebagai kartu dengan isian berlabel; mulai md kembali menjadi tabel --}}
            <div class="overflow-x-auto max-md:overflow-visible md:px-5 md:pt-5">
                <table
                    class="min-w-full border-separate border-spacing-0 text-sm text-zinc-700 max-md:block dark:text-zinc-200">
                    <thead
                        class="text-xs font-semibold text-zinc-600 max-md:hidden dark:text-zinc-400 [&_th]:bg-zinc-100 [&_th:first-child]:rounded-l-xl [&_th:last-child]:rounded-r-xl dark:[&_th]:bg-zinc-700/60">
                        <tr>
                            <th class="px-3 py-3 text-left">#</th>
                            <th class="px-3 py-3 text-left">NISN</th>
                            <th class="px-3 py-3 text-left">Nama</th>
                            <th class="px-3 py-3 text-left">Catatan Wali</th>
                        </tr>
                    </thead>
                    <tbody class="max-md:block max-md:divide-y max-md:divide-zinc-100 md:[&_td]:border-b md:[&_td]:border-zinc-100 dark:max-md:divide-zinc-700 dark:md:[&_td]:border-zinc-700">
                        @forelse ($siswas as $index => $siswa)
                            @php($val = $catatan[$siswa->id] ?? '')
                            <tr class="max-md:block max-md:space-y-2 max-md:px-4 max-md:py-3">
                                <td class="px-3 py-3 max-md:hidden">{{ $index + 1 }}</td>
                                <td class="px-3 py-3 max-md:hidden">{{ $siswa->nisn ?? '—' }}</td>
                                <td class="px-3 py-3 max-md:block max-md:p-0">
                                    <div class="flex items-start gap-3 md:block">
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-xs font-bold text-zinc-600 md:hidden dark:bg-zinc-700 dark:text-zinc-300">{{ $index + 1 }}</span>
                                        <div class="min-w-0">
                                            <span class="max-md:block max-md:font-semibold max-md:text-zinc-900 max-md:dark:text-zinc-100">{{ $siswa->nama }}</span>
                                            <span class="block text-xs text-zinc-600 md:hidden dark:text-zinc-400">{{ __('NISN') }} {{ $siswa->nisn ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 max-md:block max-md:p-0">
                                    <label for="catatan-{{ $siswa->id }}"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-zinc-600 md:sr-only dark:text-zinc-400">{{ __('Catatan Wali') }}</label>
                                    <textarea id="catatan-{{ $siswa->id }}" name="catatan[{{ $siswa->id }}]" rows="2" placeholder="Catatan singkat dari wali kelas"
                                        @disabled(!$canEdit)
                                        class="w-full rounded-[10px] border-0 bg-zinc-100 transition-colors duration-200 hover:bg-zinc-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-accent px-3 py-2 text-sm max-md:min-h-24 max-md:text-base dark:bg-zinc-700/60 dark:hover:bg-zinc-700 dark:focus:bg-zinc-900 disabled:opacity-60 disabled:cursor-not-allowed">{{ old('catatan.' . $siswa->id, $val) }}</textarea>
                                </td>
                            </tr>
                        @empty
                            <tr class="max-md:block">
                                <td colspan="4"
                                    class="px-3 py-6 text-center text-sm text-zinc-600 max-md:block dark:text-zinc-400">Belum ada
                                    siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Di HP bilah simpan menempel di bawah layar (di atas menu bawah aplikasi) selama form terlihat --}}
            <div @class([
                'flex items-center justify-end gap-3 px-5 py-4 max-md:px-4 max-md:py-3',
                'max-md:sticky max-md:bottom-0 max-md:z-20 max-md:bg-white/95 max-md:shadow-[0_-4px_12px_rgba(0,0,0,0.08)] max-md:backdrop-blur max-md:dark:bg-zinc-800/95 in-[.mode-aplikasi]:max-md:bottom-[calc(4.75rem+env(safe-area-inset-bottom))]' => $canEdit,
            ])>
                @if ($canEdit)
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-10 rounded-xl bg-accent px-4 text-sm font-semibold text-white transition duration-200 ease-[cubic-bezier(.32,.72,0,1)] hover:opacity-90 active:scale-[0.97] max-md:min-h-11 max-md:w-full max-md:justify-center focus:outline-none focus-visible:ring-4 focus-visible:ring-accent/30">
                        <i class="fas fa-save text-xs"></i> Simpan Catatan
                    </button>
                @endif
            </div>
        </form>
    @endif
</x-layouts.app>
