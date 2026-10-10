<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rapor</p>
            <h1 class="text-2xl font-bold text-gray-800 max-md:text-xl dark:text-gray-100">Data Absen</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tahun Pelajaran: {{ $tahun->nama ?? $tahunId }}
                • Semester: {{ ucfirst($semester) }}</p>
        </div>
    </div>

    <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        <form method="GET" class="contents">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Kelas</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm max-md:h-11 max-md:bg-white max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
            Pilih kelas untuk mengisi absen siswa.
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

        <form method="POST" action="{{ route('rapor.absen.store') }}"
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm max-md:overflow-clip dark:border-gray-700 dark:bg-gray-800">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
            <input type="hidden" name="semester" value="{{ $semester }}">
            {{-- Di HP setiap siswa tampil sebagai kartu dengan isian berlabel; mulai md kembali menjadi tabel --}}
            <div class="overflow-x-auto max-md:overflow-visible">
                <table
                    class="min-w-full divide-y divide-gray-200 text-sm text-gray-700 max-md:block dark:divide-gray-700 dark:text-gray-200">
                    <thead
                        class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 max-md:hidden dark:bg-gray-900/40 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3 text-left">#</th>
                            <th class="px-3 py-3 text-left">NISN</th>
                            <th class="px-3 py-3 text-left">Nama</th>
                            <th class="px-3 py-3 text-center">Sakit</th>
                            <th class="px-3 py-3 text-center">Izin</th>
                            <th class="px-3 py-3 text-center">Alpa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 max-md:block dark:divide-gray-700">
                        @forelse ($siswas as $index => $siswa)
                            @php($row = $absen[$siswa->id] ?? ['sakit' => 0, 'izin' => 0, 'alpa' => 0])
                            <tr class="max-md:grid max-md:grid-cols-3 max-md:gap-x-2 max-md:gap-y-2 max-md:px-4 max-md:py-3">
                                <td class="px-3 py-3 max-md:hidden">{{ $index + 1 }}</td>
                                <td class="px-3 py-3 max-md:hidden">{{ $siswa->nisn ?? '—' }}</td>
                                <td class="px-3 py-3 max-md:col-span-full max-md:p-0">
                                    <div class="flex items-start gap-3 md:block">
                                        <span
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600 md:hidden dark:bg-gray-700 dark:text-gray-300">{{ $index + 1 }}</span>
                                        <div class="min-w-0">
                                            <span class="max-md:block max-md:font-semibold max-md:text-gray-900 max-md:dark:text-gray-100">{{ $siswa->nama }}</span>
                                            <span class="block text-xs text-gray-500 md:hidden dark:text-gray-400">{{ __('NISN') }} {{ $siswa->nisn ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center max-md:p-0">
                                    <label for="absen-{{ $siswa->id }}-sakit"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Sakit') }}</label>
                                    <input type="number" id="absen-{{ $siswa->id }}-sakit" name="absen[{{ $siswa->id }}][sakit]" min="0"
                                        max="255" inputmode="numeric" @disabled(!$canEdit)
                                        class="w-20 rounded-lg border border-gray-300 px-2 py-1 text-center text-sm max-md:h-11 max-md:w-full max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 disabled:opacity-60 disabled:cursor-not-allowed"
                                        value="{{ old('absen.' . $siswa->id . '.sakit', $row['sakit']) }}">
                                </td>
                                <td class="px-3 py-3 text-center max-md:p-0">
                                    <label for="absen-{{ $siswa->id }}-izin"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Izin') }}</label>
                                    <input type="number" id="absen-{{ $siswa->id }}-izin" name="absen[{{ $siswa->id }}][izin]" min="0"
                                        max="255" inputmode="numeric" @disabled(!$canEdit)
                                        class="w-20 rounded-lg border border-gray-300 px-2 py-1 text-center text-sm max-md:h-11 max-md:w-full max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 disabled:opacity-60 disabled:cursor-not-allowed"
                                        value="{{ old('absen.' . $siswa->id . '.izin', $row['izin']) }}">
                                </td>
                                <td class="px-3 py-3 text-center max-md:p-0">
                                    <label for="absen-{{ $siswa->id }}-alpa"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Alpa') }}</label>
                                    <input type="number" id="absen-{{ $siswa->id }}-alpa" name="absen[{{ $siswa->id }}][alpa]" min="0"
                                        max="255" inputmode="numeric" @disabled(!$canEdit)
                                        class="w-20 rounded-lg border border-gray-300 px-2 py-1 text-center text-sm max-md:h-11 max-md:w-full max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 disabled:opacity-60 disabled:cursor-not-allowed"
                                        value="{{ old('absen.' . $siswa->id . '.alpa', $row['alpa']) }}">
                                </td>
                            </tr>
                        @empty
                            <tr class="max-md:block">
                                <td colspan="6"
                                    class="px-3 py-6 text-center text-sm text-gray-500 max-md:block dark:text-gray-400">Belum ada
                                    siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Di HP bilah simpan menempel di bawah layar (di atas menu bawah aplikasi) selama form terlihat --}}
            <div @class([
                'flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/30',
                'max-md:sticky max-md:bottom-0 max-md:z-20 max-md:bg-white/95 max-md:shadow-[0_-4px_12px_rgba(0,0,0,0.08)] max-md:backdrop-blur max-md:dark:bg-gray-800/95 in-[.mode-aplikasi]:max-md:bottom-[calc(4.75rem+env(safe-area-inset-bottom))]' => $canEdit,
            ])>
                @if ($canEdit)
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow max-md:min-h-11 max-md:w-full max-md:justify-center hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <i class="fas fa-save text-xs"></i> Simpan Absen
                    </button>
                @endif
            </div>
        </form>
    @endif
</x-layouts.app>
