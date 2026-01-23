<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Pengaturan</p>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Mengajar Tahfidz</h1>
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
            
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                        <thead class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Kelas</th>
                                <th class="px-4 py-3 text-left">Guru Tahfidz</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($kelasList as $index => $kelas)
                                @php
                                    $assignment = $assignments->get($kelas->id);
                                    $guruId = $assignment?->guru_id ?? null;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-medium">
                                        {{ $kelas->nama }}
                                        <input type="hidden" name="items[{{ $index }}][kelas_id]" value="{{ $kelas->id }}">
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="items[{{ $index }}][guru_id]"
                                            class="w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                            <option value="">-- Belum Ditugaskan --</option>
                                            @foreach ($gurus as $guru)
                                                <option value="{{ $guru->id }}" @selected($guruId == $guru->id)>
                                                    {{ $guru->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-center">
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
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada kelas untuk tahun ajaran ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($kelasList->isNotEmpty())
                <div class="mt-4 flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            @endif
        </form>
    @endif
</x-layouts.app>
