<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rapor Tahfidz</p>
            <h1 class="text-2xl font-bold text-gray-800 max-md:text-xl dark:text-gray-100">Input Penilaian Tahfidz — Juz
                {{ $juz }}</h1>
        </div>
        <a href="{{ route('tahfidz.index', ['kelas_id' => $siswa->kelas_id]) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 max-md:min-h-11 max-md:self-start dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Info Siswa -->
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid gap-4 max-md:grid-cols-2 max-md:gap-3 md:grid-cols-2 lg:grid-cols-4">
            <div class="max-md:col-span-2">
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

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 dark:border-red-700 dark:bg-red-900/30">
            <div class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                <i class="fas fa-exclamation-circle"></i>
                {{ __('Terjadi kesalahan saat menyimpan:') }}
            </div>
            <ul class="mt-2 list-inside list-disc text-sm text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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

    <form action="{{ route('tahfidz.store', $siswa->id) }}" method="POST">
        @csrf
        <input type="hidden" name="juz" value="{{ $juz }}">

        <div class="space-y-6">
            <!-- Pembimbing Tahfidz -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm max-md:p-4 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-800 dark:text-gray-100">Pembimbing Tahfidz</h3>
                <label for="tahfidz-pembimbing" class="sr-only">{{ __('Pembimbing Tahfidz') }}</label>
                <select id="tahfidz-pembimbing" name="pembimbing_id" @disabled(!$canEdit)
                    class="w-full max-w-md rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm max-md:h-11 max-md:bg-white max-md:max-w-none max-md:text-base focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed">
                    <option value="">-- Pilih Pembimbing --</option>
                    @foreach ($pembimbingList as $guru)
                        <option value="{{ $guru->id }}" @selected(old('pembimbing_id', $penilaian->pembimbing_id ?? null) == $guru->id)>{{ $guru->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Penilaian Pengetahuan -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm max-md:p-4 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-800 dark:text-gray-100">Penilaian Pengetahuan</h3>
                {{-- Di HP setiap aspek tampil bertumpuk dengan isian berlabel; mulai md kembali menjadi tabel --}}
                <div class="overflow-x-auto max-md:overflow-visible">
                    <table class="min-w-full divide-y divide-gray-200 text-sm max-md:block dark:divide-gray-700">
                        <thead class="bg-gray-50 max-md:hidden dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Mata
                                    Pelajaran</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300 w-32">
                                    Predikat</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Deskripsi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 max-md:block dark:divide-gray-700">
                            <!-- Adab -->
                            <tr
                                class="max-md:grid max-md:grid-cols-[5.5rem_minmax(0,1fr)] max-md:gap-x-3 max-md:gap-y-2 max-md:py-3 max-md:first:pt-0 max-md:last:pb-0">
                                <td class="px-4 py-3 font-medium max-md:col-span-2 max-md:p-0 max-md:font-semibold max-md:text-gray-900 max-md:dark:text-gray-100">Adab</td>
                                <td class="px-4 py-3 max-md:p-0">
                                    <label for="tahfidz-predikat-adab"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Predikat') }}</label>
                                    <select id="tahfidz-predikat-adab" name="predikat_adab" @disabled(!$canEdit)
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm max-md:h-11 max-md:bg-white max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="">-</option>
                                        @foreach ($predikatList as $key => $label)
                                            <option value="{{ $key }}" @selected(old('predikat_adab', $penilaian->predikat_adab ?? '') == $key)>
                                                {{ $key }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 max-md:min-w-0 max-md:p-0">
                                    <label for="tahfidz-deskripsi-adab"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Deskripsi') }}</label>
                                    <input type="text" id="tahfidz-deskripsi-adab" name="deskripsi_adab" maxlength="100" @disabled(!$canEdit)
                                        value="{{ old('deskripsi_adab', $penilaian->deskripsi_adab ?? 'Baik') }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm max-md:h-11 max-md:bg-white max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                        placeholder="Deskripsi...">
                                </td>
                            </tr>
                            <!-- Tajwid -->
                            <tr
                                class="max-md:grid max-md:grid-cols-[5.5rem_minmax(0,1fr)] max-md:gap-x-3 max-md:gap-y-2 max-md:py-3 max-md:first:pt-0 max-md:last:pb-0">
                                <td class="px-4 py-3 font-medium max-md:col-span-2 max-md:p-0 max-md:font-semibold max-md:text-gray-900 max-md:dark:text-gray-100">Tajwid</td>
                                <td class="px-4 py-3 max-md:p-0">
                                    <label for="tahfidz-predikat-tajwid"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Predikat') }}</label>
                                    <select id="tahfidz-predikat-tajwid" name="predikat_tajwid" @disabled(!$canEdit)
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm max-md:h-11 max-md:bg-white max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="">-</option>
                                        @foreach ($predikatList as $key => $label)
                                            <option value="{{ $key }}" @selected(old('predikat_tajwid', $penilaian->predikat_tajwid ?? '') == $key)>
                                                {{ $key }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 max-md:min-w-0 max-md:p-0">
                                    <label for="tahfidz-deskripsi-tajwid"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Deskripsi') }}</label>
                                    <input type="text" id="tahfidz-deskripsi-tajwid" name="deskripsi_tajwid" maxlength="100" @disabled(!$canEdit)
                                        value="{{ old('deskripsi_tajwid', $penilaian->deskripsi_tajwid ?? 'Baik') }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm max-md:h-11 max-md:bg-white max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                        placeholder="Deskripsi...">
                                </td>
                            </tr>
                            <!-- Makhorijul Huruf -->
                            <tr
                                class="max-md:grid max-md:grid-cols-[5.5rem_minmax(0,1fr)] max-md:gap-x-3 max-md:gap-y-2 max-md:py-3 max-md:first:pt-0 max-md:last:pb-0">
                                <td class="px-4 py-3 font-medium max-md:col-span-2 max-md:p-0 max-md:font-semibold max-md:text-gray-900 max-md:dark:text-gray-100">Makhorijul Huruf</td>
                                <td class="px-4 py-3 max-md:p-0">
                                    <label for="tahfidz-predikat-makhorijul"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Predikat') }}</label>
                                    <select id="tahfidz-predikat-makhorijul" name="predikat_makhorijul" @disabled(!$canEdit)
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm max-md:h-11 max-md:bg-white max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="">-</option>
                                        @foreach ($predikatList as $key => $label)
                                            <option value="{{ $key }}" @selected(old('predikat_makhorijul', $penilaian->predikat_makhorijul ?? '') == $key)>
                                                {{ $key }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 max-md:min-w-0 max-md:p-0">
                                    <label for="tahfidz-deskripsi-makhorijul"
                                        class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 md:sr-only dark:text-gray-400">{{ __('Deskripsi') }}</label>
                                    <input type="text" id="tahfidz-deskripsi-makhorijul" name="deskripsi_makhorijul" maxlength="100" @disabled(!$canEdit)
                                        value="{{ old('deskripsi_makhorijul', $penilaian->deskripsi_makhorijul ?? 'Cukup') }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm max-md:h-11 max-md:bg-white max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                        placeholder="Deskripsi...">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Hafalan Surah -->
            @php
                // Pakai pilihan yang baru dikirim bila kembali karena gagal simpan
                $pakaiInputLama = (int) old('juz', 0) === $juz;
            @endphp
            @if ($juz === 30)
                @php
                    $surahHafalan = $pakaiInputLama
                        ? (array) old('surah_hafalan', [])
                        : $penilaian->surah_hafalan ?? [];
                @endphp
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm max-md:p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between max-md:items-start max-md:gap-3">
                        <h3 class="text-lg font-bold text-gray-800 max-md:text-base dark:text-gray-100">Pencapaian Target Hafalan — Juz
                            30
                            (Juz 'Amma)
                        </h3>
                        <span id="surah-count"
                            class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold max-md:shrink-0 max-md:tabular-nums text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                            {{ count($surahHafalan) }} / 38
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach ($surahList as $key => $nama)
                            <label
                                class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors max-md:min-h-11 max-md:gap-2.5 hover:bg-gray-100 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:bg-gray-800 dark:has-[:checked]:border-green-600 dark:has-[:checked]:bg-green-900/30 {{ !$canEdit ? 'opacity-60 cursor-not-allowed' : '' }}">
                                <input type="checkbox" name="surah_hafalan[]" value="{{ $key }}"
                                    @disabled(!$canEdit)
                                    class="surah-checkbox h-4 w-4 rounded max-md:shrink-0 border-gray-300 text-green-600 max-md:h-5 max-md:w-5 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-800 disabled:cursor-not-allowed"
                                    @checked(in_array($key, $surahHafalan))>
                                <span
                                    class="text-xs font-medium text-gray-700 max-md:min-w-0 max-md:text-sm max-md:leading-tight dark:text-gray-300">{{ $nama }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $surahHafalan29 = $pakaiInputLama
                        ? (array) old('surah_hafalan_29', [])
                        : $penilaian->surah_hafalan_29 ?? [];
                @endphp
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm max-md:p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between max-md:items-start max-md:gap-3">
                        <h3 class="text-lg font-bold text-gray-800 max-md:text-base dark:text-gray-100">Pencapaian Target Hafalan — Juz
                            29
                            (Juz Tabarak)
                        </h3>
                        <span id="surah-count"
                            class="rounded-full bg-purple-100 px-3 py-1 text-sm font-semibold max-md:shrink-0 max-md:tabular-nums text-purple-700 dark:bg-purple-900 dark:text-purple-200">
                            {{ count($surahHafalan29) }} / 11
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach ($surahListJuz29 as $key => $nama)
                            <label
                                class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition-colors max-md:min-h-11 max-md:gap-2.5 hover:bg-gray-100 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:bg-gray-900/50 dark:hover:bg-gray-800 dark:has-[:checked]:border-purple-600 dark:has-[:checked]:bg-purple-900/30 {{ !$canEdit ? 'opacity-60 cursor-not-allowed' : '' }}">
                                <input type="checkbox" name="surah_hafalan_29[]" value="{{ $key }}"
                                    @disabled(!$canEdit)
                                    class="surah-checkbox h-4 w-4 rounded max-md:shrink-0 border-gray-300 text-purple-600 max-md:h-5 max-md:w-5 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-800 disabled:cursor-not-allowed"
                                    @checked(in_array($key, $surahHafalan29))>
                                <span
                                    class="text-xs font-medium text-gray-700 max-md:min-w-0 max-md:text-sm max-md:leading-tight dark:text-gray-300">{{ $nama }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Tombol Submit (di HP menempel di bawah layar, di atas menu bawah aplikasi) -->
            <div @class([
                'flex justify-end gap-3',
                'max-md:sticky max-md:bottom-0 max-md:z-20 max-md:-mx-4 max-md:gap-2 max-md:border-t max-md:border-gray-200 max-md:bg-white/95 max-md:px-4 max-md:py-3 max-md:shadow-[0_-4px_12px_rgba(0,0,0,0.08)] max-md:backdrop-blur max-md:dark:border-gray-700 max-md:dark:bg-gray-800/95 in-[.mode-aplikasi]:max-md:bottom-[calc(4.75rem+env(safe-area-inset-bottom))]' => $canEdit,
            ])>
                <a href="{{ route('tahfidz.index', ['kelas_id' => $siswa->kelas_id]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 max-md:inline-flex max-md:min-h-11 max-md:items-center max-md:justify-center max-md:px-4 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    {{ $canEdit ? 'Batal' : 'Kembali' }}
                </a>
                @if ($canEdit)
                    @if ($penilaian->exists)
                        <button type="button" id="reset-trigger"
                            class="rounded-lg border border-red-300 bg-white px-6 py-2.5 text-sm font-semibold text-red-600 shadow-sm hover:bg-red-50 max-md:min-h-11 max-md:px-4 dark:border-red-600 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    @endif
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 max-md:min-h-11 max-md:flex-1">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                @endif
            </div>
        </div>
    </form>

    {{-- Hidden reset form --}}
    @if ($canEdit && $penilaian->exists)
        <form id="reset-form" method="POST" action="{{ route('tahfidz.reset', $siswa->id) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.surah-checkbox');
            const countDisplay = document.getElementById('surah-count');
            const totalSurah = {{ $juz === 30 ? 38 : 11 }};

            function updateCount() {
                const checked = document.querySelectorAll('.surah-checkbox:checked').length;
                countDisplay.textContent = checked + ' / ' + totalSurah;
            }

            checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

            // Reset button handler
            const resetTrigger = document.getElementById('reset-trigger');
            if (resetTrigger) {
                resetTrigger.addEventListener('click', function() {
                    if (confirm(
                            '{{ __('Yakin ingin mereset semua penilaian tahfidz? Tindakan ini tidak dapat dibatalkan.') }}'
                        )) {
                        document.getElementById('reset-form').submit();
                    }
                });
            }
        });
    </script>
</x-layouts.app>
