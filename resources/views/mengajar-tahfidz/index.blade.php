<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Pengaturan</p>
            <h1 class="text-2xl font-bold text-gray-800 max-md:text-xl dark:text-gray-100">Mengajar Tahfidz</h1>
        </div>
    </div>

    @if (!$tahunId || !$semester)
        <div class="rounded-lg bg-amber-50 p-4 text-sm text-amber-700 dark:bg-amber-900/50 dark:text-amber-200">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Pilih tahun ajaran dan semester terlebih dahulu.
        </div>
    @else
        <form action="{{ route('mengajar-tahfidz.store') }}" method="POST">
            @csrf
            
            {{-- Di HP setiap kelas tampil sebagai kartu: nama + status, lalu pilihan guru; mulai md kembali menjadi tabel --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto max-md:overflow-visible">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700 max-md:block dark:divide-gray-700 dark:text-gray-200">
                        <thead class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 max-md:hidden dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Kelas</th>
                                <th class="px-4 py-3 text-left">Guru Tahfidz</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 max-md:block dark:divide-gray-700">
                            @forelse ($kelasList as $index => $kelas)
                                @php
                                    $assignment = $assignments->get($kelas->id);
                                    $guruId = $assignment?->guru_id ?? null;
                                @endphp
                                <tr
                                    class="max-md:grid max-md:grid-cols-[minmax(0,1fr)_auto] max-md:items-center max-md:gap-x-3 max-md:gap-y-2 max-md:px-4 max-md:py-3">
                                    <td class="px-4 py-3 font-medium max-md:p-0 max-md:text-base max-md:font-semibold max-md:text-gray-900 max-md:dark:text-gray-100">
                                        <span class="text-xs font-normal text-gray-500 md:hidden dark:text-gray-400">{{ __('Kelas') }}</span>
                                        {{ $kelas->nama }}
                                        <input type="hidden" name="items[{{ $index }}][kelas_id]" value="{{ $kelas->id }}">
                                    </td>
                                    <td class="px-4 py-3 max-md:order-last max-md:col-span-2 max-md:p-0">
                                        <label for="tahfidz-guru-{{ $kelas->id }}" class="sr-only">{{ __('Guru Tahfidz kelas :kelas', ['kelas' => $kelas->nama]) }}</label>
                                        <select id="tahfidz-guru-{{ $kelas->id }}" name="items[{{ $index }}][guru_id]"
                                            class="w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-sm max-md:h-11 max-md:max-w-none max-md:bg-white max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                            <option value="">-- Belum Ditugaskan --</option>
                                            @foreach ($gurus as $guru)
                                                <option value="{{ $guru->id }}" @selected($guruId == $guru->id)>
                                                    {{ $guru->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-center max-md:p-0">
                                        @if ($guruId)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <i class="fas fa-check mr-1 text-[10px]"></i> Assigned
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                <i class="fas fa-minus mr-1 text-[10px]"></i> -
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="max-md:block">
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500 max-md:block dark:text-gray-400">
                                        Belum ada kelas untuk tahun ajaran ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($kelasList->isNotEmpty())
                {{-- Di HP bilah simpan menempel di bawah layar (di atas menu bawah aplikasi) selama form terlihat --}}
                <div
                    class="mt-4 flex justify-end max-md:-mx-4 max-md:border-t max-md:border-gray-200 max-md:px-4 max-md:py-3 max-md:dark:border-gray-700 max-md:sticky max-md:bottom-0 max-md:z-20 max-md:bg-white/95 max-md:shadow-[0_-4px_12px_rgba(0,0,0,0.08)] max-md:backdrop-blur max-md:dark:bg-gray-800/95 in-[.mode-aplikasi]:max-md:bottom-[calc(4.75rem+env(safe-area-inset-bottom))]">
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 max-md:min-h-11 max-md:w-full">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            @endif
        </form>
    @endif
</x-layouts.app>
