<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rapor Tahfidz</p>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Input Penilaian Tahfidz</h1>
        </div>
        <a href="{{ route('tahfidz.index', ['kelas_id' => $siswa->kelas_id]) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Info Siswa -->
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Nama Siswa</span>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->nama }}</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">NISN</span>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->nisn ?? '-' }}</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Kelas</span>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->kelas->nama ?? '-' }}</p>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Semester</span>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($semester) }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('tahfidz.store', $siswa->id) }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            <!-- Pembimbing Tahfidz -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-800 dark:text-gray-100">Pembimbing Tahfidz</h3>
                <select name="pembimbing_id"
                    class="w-full max-w-md rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">-- Pilih Pembimbing --</option>
                    @foreach ($pembimbingList as $guru)
                        <option value="{{ $guru->id }}" @selected(($penilaian->pembimbing_id ?? null) == $guru->id)>{{ $guru->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Penilaian Pengetahuan -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-800 dark:text-gray-100">Penilaian Pengetahuan</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Mata Pelajaran</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300 w-32">Predikat</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Adab -->
                            <tr>
                                <td class="px-4 py-3 font-medium">Adab</td>
                                <td class="px-4 py-3">
                                    <select name="predikat_adab" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="">-</option>
                                        @foreach ($predikatList as $key => $label)
                                            <option value="{{ $key }}" @selected(($penilaian->predikat_adab ?? '') == $key)>{{ $key }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="deskripsi_adab" value="{{ $penilaian->deskripsi_adab ?? 'Baik' }}" 
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Deskripsi...">
                                </td>
                            </tr>
                            <!-- Tajwid -->
                            <tr>
                                <td class="px-4 py-3 font-medium">Tajwid</td>
                                <td class="px-4 py-3">
                                    <select name="predikat_tajwid" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="">-</option>
                                        @foreach ($predikatList as $key => $label)
                                            <option value="{{ $key }}" @selected(($penilaian->predikat_tajwid ?? '') == $key)>{{ $key }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="deskripsi_tajwid" value="{{ $penilaian->deskripsi_tajwid ?? 'Baik' }}" 
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Deskripsi...">
                                </td>
                            </tr>
                            <!-- Makhorijul Huruf -->
                            <tr>
                                <td class="px-4 py-3 font-medium">Makhorijul Huruf</td>
                                <td class="px-4 py-3">
                                    <select name="predikat_makhorijul" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="">-</option>
                                        @foreach ($predikatList as $key => $label)
                                            <option value="{{ $key }}" @selected(($penilaian->predikat_makhorijul ?? '') == $key)>{{ $key }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="deskripsi_makhorijul" value="{{ $penilaian->deskripsi_makhorijul ?? 'Cukup' }}" 
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Deskripsi...">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Hafalan Surah -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Pencapaian Target Hafalan (Juz 'Amma)</h3>
                    <span id="surah-count" class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                        {{ count($penilaian->surah_hafalan ?? []) }} / 38
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @php $surahHafalan = $penilaian->surah_hafalan ?? []; @endphp
                    @foreach ($surahList as $key => $nama)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors hover:bg-gray-100 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:bg-gray-800 dark:has-[:checked]:border-green-600 dark:has-[:checked]:bg-green-900/30">
                            <input type="checkbox" name="surah_hafalan[]" value="{{ $key }}"
                                class="surah-checkbox h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-800"
                                @checked(in_array($key, $surahHafalan))>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $nama }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('tahfidz.index', ['kelas_id' => $siswa->kelas_id]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.surah-checkbox');
            const countDisplay = document.getElementById('surah-count');
            
            function updateCount() {
                const checked = document.querySelectorAll('.surah-checkbox:checked').length;
                countDisplay.textContent = checked + ' / 38';
            }
            
            checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
        });
    </script>
</x-layouts.app>
