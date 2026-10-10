<x-layouts.app>
    @php
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();
        $currentTahunId = session('selected_tahun_ajaran_id');
    @endphp

    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 max-md:text-xl dark:text-gray-100">{{ __('Rombel Kelas') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 max-md:text-sm">{{ __('Atur anggota rombel untuk setiap kelas.') }}</p>
        </div>
        <button type="button" onclick="document.getElementById('copyModal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm max-md:min-h-11 max-md:justify-center hover:bg-emerald-700">
            <i class="fas fa-copy"></i> {{ __('Salin dari Tahun Ajaran Lain') }}
        </button>
    </div>

    @if ($kelasList->isNotEmpty())
        {{-- Di HP ringkasan kelas menjadi strip yang digeser ke samping agar tidak memanjang ke bawah --}}
        <div
            class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 max-md:-mx-4 max-md:flex max-md:snap-x max-md:overflow-x-auto max-md:px-4 max-md:pb-1">
            @foreach ($kelasList as $kelas)
                <div
                    class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm max-md:w-40 max-md:shrink-0 max-md:snap-start dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p
                                class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Kelas</p>
                            <p class="text-base font-bold text-gray-900 dark:text-gray-100 leading-tight">
                                {{ $kelas->nama }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-snug">Wali:
                                {{ optional($kelas->guru)->nama ?? 'Belum diatur' }}</p>
                        </div>
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-md bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 11c0-1.657-1.343-3-3-3S6 9.343 6 11s1.343 3 3 3 3-1.343 3-3zM6 18v-1a3 3 0 016 0v1m0 0h6m-6 0h-6m12 0v-1a3 3 0 00-3-3h-1m-2-9a4 4 0 110 8 4 4 0 010-8z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-700 dark:text-gray-200">
                        <span>Jumlah siswa</span>
                        <span
                            class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $kelas->siswas_count }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <form method="GET" action="{{ route('rombel.index') }}" class="flex items-center gap-2 max-md:w-full">
            <label for="rombel-pilih-kelas" class="sr-only">{{ __('Kelas') }}</label>
            <select id="rombel-pilih-kelas" name="kelas_id"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm max-md:h-11 max-md:min-w-0 max-md:flex-1 max-md:text-base focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                @foreach ($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" @selected(optional($selectedKelas)->id === $kelas->id)>
                        {{ $kelas->nama }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 max-md:min-h-11 max-md:px-5">{{ __('Pilih') }}</button>
        </form>
    </div>
    @if ($kelasList->isEmpty())
        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            {{ __('Buat kelas terlebih dahulu sebelum mengatur rombel.') }}
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-3">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm max-md:p-4 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Info Kelas') }}</h3>
                @if ($selectedKelas)
                    <dl class="mt-4 space-y-2 text-sm text-gray-800 dark:text-gray-200">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">{{ __('Nama Kelas') }}</dt>
                            <dd class="font-semibold">{{ $selectedKelas->nama }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">{{ __('Wali Kelas') }}</dt>
                            <dd>{{ optional($selectedKelas->guru)->nama ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">{{ __('Tingkat') }}</dt>
                            <dd>{{ $selectedKelas->tingkat }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">{{ __('Jurusan') }}</dt>
                            <dd>{{ $selectedKelas->jurusan ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">{{ __('Jenis') }}</dt>
                            <dd>{{ $selectedKelas->jenis ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">{{ __('Jumlah Siswa') }}</dt>
                            <dd>{{ $selectedKelas->siswas->count() }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-3 text-sm text-gray-500">{{ __('Pilih kelas untuk melihat detail.') }}</p>
                @endif
            </div>

            <div
                class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-6 py-4 max-md:px-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Anggota Rombel') }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ __('Centang siswa untuk menetapkan ke kelas ini. Siswa tanpa kelas juga ditampilkan.') }}
                    </p>
                </div>

                <form id="rombelForm" method="POST"
                    action="{{ $selectedKelas ? route('rombel.update', $selectedKelas) : '#' }}">
                    @csrf
                    @method('PUT')

                    {{-- Hidden container for checked siswa IDs --}}
                    <div id="hiddenSiswaIds"></div>

                    @if ($siswas->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Belum ada siswa di kelas ini.') }}
                        </div>
                    @else
                        {{-- Daftar centang versi HP: kartu per siswa + pencarian. Kotak centang tidak punya name;
                             pilihan dikirim lewat input tersembunyi saat submit (lihat skrip di bawah). --}}
                        <div class="md:hidden" x-data="{
                            cari: '',
                            cocok(el) {
                                const kata = this.cari.trim().toLowerCase();
                                return kata === '' || el.dataset.cari.includes(kata);
                            },
                            get kosong() {
                                const kata = this.cari.trim().toLowerCase();
                                return kata !== '' && ![...this.$root.querySelectorAll('[data-cari]')].some((el) => el.dataset.cari.includes(kata));
                            },
                        }">
                            <div class="px-4 pt-4">
                                <label for="rombel-cari-siswa" class="sr-only">{{ __('Cari siswa') }}</label>
                                <div class="relative">
                                    <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"
                                        aria-hidden="true"></i>
                                    <input id="rombel-cari-siswa" type="search" x-model="cari" autocomplete="off"
                                        placeholder="{{ __('Cari nama atau NIS…') }}"
                                        class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 text-base text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                </div>
                            </div>
                            <ul class="mt-3 divide-y divide-gray-200 border-t border-gray-100 dark:divide-gray-700 dark:border-gray-700">
                                @foreach ($siswas as $siswa)
                                    <li x-show="cocok($el)" data-cari="{{ \Illuminate\Support\Str::lower($siswa->nama . ' ' . $siswa->nis) }}">
                                        <label
                                            class="flex min-h-14 cursor-pointer items-center gap-3 px-4 py-3 transition-colors active:bg-gray-100 has-[:checked]:bg-blue-50 dark:active:bg-gray-700/40 dark:has-[:checked]:bg-blue-900/30">
                                            <input type="checkbox" class="siswa-checkbox h-5 w-5 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900"
                                                data-siswa-id="{{ $siswa->id }}" @checked($selectedKelas && $siswa->kelas_id === $selectedKelas->id)>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->nama }}</span>
                                                <span class="block truncate text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('NIS') }} {{ $siswa->nis ?: '—' }} •
                                                    @if ($siswa->kelas)
                                                        {{ __('Kelas :kelas', ['kelas' => $siswa->kelas->nama]) }}
                                                    @else
                                                        <span class="font-semibold text-amber-600 dark:text-amber-400">{{ __('Belum punya kelas') }}</span>
                                                    @endif
                                                </span>
                                            </span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                            <p style="display: none" x-show="kosong" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Tidak ada siswa yang cocok.') }}</p>
                        </div>

                        <div class="overflow-x-auto px-6 py-4 max-md:hidden">
                            <table id="rombel-table"
                                class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                                <thead
                                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-3 text-center">{{ __('Pilih') }}</th>
                                        <th class="px-3 py-3">NIS</th>
                                        <th class="px-3 py-3">Nama</th>
                                        <th class="px-3 py-3">{{ __('Kelas Saat Ini') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($siswas as $siswa)
                                        <tr class="siswa-row cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                            data-siswa-id="{{ $siswa->id }}">
                                            <td class="px-3 py-3 text-center">
                                                <input type="checkbox" class="siswa-checkbox pointer-events-none"
                                                    data-siswa-id="{{ $siswa->id }}" @checked($selectedKelas && $siswa->kelas_id === $selectedKelas->id)
                                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            </td>
                                            <td class="px-3 py-3">{{ $siswa->nis }}</td>
                                            <td class="px-3 py-3">{{ $siswa->nama }}</td>
                                            <td class="px-3 py-3">{{ optional($siswa->kelas)->nama ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Di HP bilah simpan menempel di bawah layar (di atas menu bawah aplikasi) selama form terlihat --}}
                    <div
                        class="border-t border-gray-100 px-6 py-4 text-right dark:border-gray-700 max-md:sticky max-md:bottom-0 max-md:z-20 max-md:flex max-md:items-center max-md:gap-3 max-md:rounded-b-2xl max-md:bg-white/95 max-md:px-4 max-md:py-3 max-md:text-left max-md:shadow-[0_-4px_12px_rgba(0,0,0,0.08)] max-md:backdrop-blur max-md:dark:bg-gray-800/95 in-[.mode-aplikasi]:max-md:bottom-[calc(4.75rem+env(safe-area-inset-bottom))]">
                        <p class="min-w-0 flex-1 text-xs text-gray-600 md:hidden dark:text-gray-300">
                            <span id="rombel-jumlah-dipilih" class="font-semibold tabular-nums">{{ $selectedKelas ? $siswas->where('kelas_id', $selectedKelas->id)->count() : 0 }}</span>
                            {{ __('siswa dipilih') }}
                        </p>
                        <button type="submit" @disabled(!$selectedKelas)
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-blue-300 max-md:min-h-11 max-md:shrink-0 max-md:justify-center">
                            {{ __('Simpan Rombel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('rombelForm');
            const hiddenContainer = document.getElementById('hiddenSiswaIds');

            // Track checked state across all pages
            const checkedSiswaIds = new Set();

            // Semua kotak centang (tabel + kartu HP) dikumpulkan sebelum DataTables memindahkan baris
            // ke halaman lain, supaya keduanya tetap seragam meski barisnya sedang tidak tampil.
            const semuaKotak = Array.from(document.querySelectorAll('.siswa-checkbox'));
            const jumlahDipilih = document.getElementById('rombel-jumlah-dipilih');

            // Initialize with already checked checkboxes
            semuaKotak.filter((cb) => cb.checked).forEach(function(cb) {
                checkedSiswaIds.add(cb.dataset.siswaId);
            });

            // Function to update row styling based on checkbox state
            function updateRowStyle(row, isChecked) {
                if (isChecked) {
                    row.classList.add('bg-blue-50', 'dark:bg-blue-900/30');
                } else {
                    row.classList.remove('bg-blue-50', 'dark:bg-blue-900/30');
                }
            }

            // Initialize row styles
            document.querySelectorAll('.siswa-row').forEach(function(row) {
                const checkbox = row.querySelector('.siswa-checkbox');
                if (checkbox) {
                    updateRowStyle(row, checkbox.checked);
                }
            });

            // Samakan status centang satu siswa di tabel dan kartu HP, lalu perbarui penghitung
            function samakanPilihan(siswaId, isChecked) {
                if (isChecked) {
                    checkedSiswaIds.add(siswaId);
                } else {
                    checkedSiswaIds.delete(siswaId);
                }
                semuaKotak.forEach(function(cb) {
                    if (cb.dataset.siswaId !== siswaId) {
                        return;
                    }
                    cb.checked = isChecked;
                    const row = cb.closest('.siswa-row');
                    if (row) {
                        updateRowStyle(row, isChecked);
                    }
                });
                if (jumlahDipilih) {
                    jumlahDipilih.textContent = checkedSiswaIds.size;
                }
            }

            // Listen for row clicks to toggle checkbox
            document.addEventListener('click', function(e) {
                const row = e.target.closest('.siswa-row');
                if (row) {
                    const checkbox = row.querySelector('.siswa-checkbox');
                    if (checkbox) {
                        samakanPilihan(checkbox.dataset.siswaId, !checkbox.checked);
                    }
                }
            });

            // Listen for checkbox changes (for DataTables pagination)
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('siswa-checkbox')) {
                    samakanPilihan(e.target.dataset.siswaId, e.target.checked);
                }
            });

            // Before form submit, add all checked IDs as hidden inputs
            form.addEventListener('submit', function(e) {
                // Clear old hidden inputs
                hiddenContainer.innerHTML = '';

                // Add hidden input for each checked siswa
                checkedSiswaIds.forEach(function(siswaId) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'siswa_ids[]';
                    input.value = siswaId;
                    hiddenContainer.appendChild(input);
                });
            });
        });
    </script>

    {{-- Copy Modal (di HP tampil sebagai lembar bawah selebar layar) --}}
    <div id="copyModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4 max-md:min-h-dvh max-md:items-end max-md:p-0">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
                onclick="document.getElementById('copyModal').classList.add('hidden')"></div>

            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl max-md:max-w-none max-md:rounded-b-none max-md:p-5 max-md:pb-[calc(1.25rem+env(safe-area-inset-bottom))] dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <i class="fas fa-copy mr-2 text-emerald-600"></i>
                        {{ __('Salin Rombel') }}
                    </h3>
                    <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')"
                        aria-label="{{ __('Tutup') }}"
                        class="text-gray-400 hover:text-gray-600 max-md:-mr-3 max-md:flex max-md:h-11 max-md:w-11 max-md:items-center max-md:justify-center dark:hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('rombel.copy') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Salin dari Tahun Ajaran:') }}
                        </label>
                        <select name="source_tahun_ajaran_id" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">-- {{ __('Pilih Tahun Ajaran Sumber') }} --</option>
                            @foreach ($tahunAjarans as $tahun)
                                @if ($tahun->id != $currentTahunId)
                                    <option value="{{ $tahun->id }}">
                                        {{ $tahun->nama }} - {{ $tahun->semester }}
                                        @if ($tahun->is_active)
                                            (Aktif)
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div
                        class="mb-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-700 dark:bg-amber-900/50 dark:text-amber-200">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('Siswa akan disalin ke tahun ajaran saat ini beserta kelasnya. Jika siswa sudah ada, hanya kelas yang akan di-update.') }}
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 max-md:min-h-11 max-md:flex-1 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 max-md:min-h-11 max-md:flex-1">
                            <i class="fas fa-copy mr-1"></i> {{ __('Salin Rombel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
