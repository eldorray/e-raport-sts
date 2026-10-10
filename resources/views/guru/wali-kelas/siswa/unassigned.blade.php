<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Claim Siswa') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Pilih siswa yang belum memiliki kelas untuk di-claim ke kelas Anda.') }}
            </p>
            <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-1">
                {{ __('Kelas:') }} {{ $kelas->nama }} &bull; {{ __('Wali Kelas:') }} {{ $guru->nama }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('wali-kelas.siswa.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-500/10 max-md:h-11 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Kembali ke Siswa Kelas') }}
            </a>
        </div>
    </div>




    @error('siswa_ids')
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    {{-- Di HP: overflow-clip (bukan hidden) agar bilah claim bisa menempel di bawah layar --}}
    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm max-md:overflow-clip dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-6 py-4 max-md:px-4 dark:border-gray-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Siswa Belum Memiliki Kelas') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Ditemukan :count siswa yang dapat di-claim.', ['count' => $unassignedSiswas->count()]) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Live Search Input --}}
                    <div class="relative max-md:min-w-0 max-md:flex-1">
                        <input type="text" id="searchInput" placeholder="{{ __('Cari nama, NIS, NISN...') }}"
                            class="w-64 rounded-lg border border-gray-300 bg-white px-4 py-2 pl-10 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:w-full max-md:text-base dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div id="selectedCount"
                        class="hidden shrink-0 rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                        <span id="selectedCountNumber">0</span> {{ __('dipilih') }}
                    </div>
                </div>
            </div>
        </div>

        @if ($unassignedSiswas->isEmpty())
            <div class="px-6 py-10 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-3 text-gray-500 dark:text-gray-400">
                    {{ __('Tidak ada siswa yang tersedia untuk di-claim.') }}
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500">
                    {{ __('Semua siswa sudah ter-assign ke kelas masing-masing.') }}
                </p>
            </div>
        @else
            <form id="claimForm" method="POST" action="{{ route('wali-kelas.siswa.claim') }}">
                @csrf
                <div id="hiddenSiswaIds"></div>

                {{-- No results message --}}
                <div id="noResults" class="hidden px-6 py-10 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="mt-3 text-gray-500 dark:text-gray-400">
                        {{ __('Tidak ada hasil yang cocok dengan pencarian Anda.') }}
                    </p>
                </div>

                {{-- Satu markup: tabel di layar lebar, baris kartu yang bisa diketuk di HP --}}
                <div class="overflow-x-auto" id="tableContainer">
                    <table id="unassigned-table"
                        class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 max-md:block dark:divide-gray-700 dark:text-gray-200">
                        <thead
                            class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 max-md:block dark:bg-gray-900/40 dark:text-gray-400">
                            <tr class="max-md:flex max-md:min-h-11 max-md:items-center max-md:px-4">
                                <th class="px-4 py-3 text-center w-12 max-md:w-auto max-md:p-0 max-md:text-left">
                                    <div class="max-md:flex max-md:items-center max-md:gap-3">
                                        <input type="checkbox" id="selectAll"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 max-md:h-5 max-md:w-5">
                                        <label for="selectAll" class="md:hidden">{{ __('Pilih semua') }}</label>
                                    </div>
                                </th>
                                <th class="px-4 py-3 max-md:hidden">#</th>
                                <th class="px-4 py-3 max-md:hidden">NIS</th>
                                <th class="px-4 py-3 max-md:hidden">NISN</th>
                                <th class="px-4 py-3 max-md:hidden">{{ __('Nama') }}</th>
                                <th class="px-4 py-3 max-md:hidden">{{ __('Jenis Kelamin') }}</th>
                                <th class="px-4 py-3 max-md:hidden">{{ __('Kelas Saat Ini') }}</th>
                                <th class="px-4 py-3 max-md:hidden">{{ __('Kelas Tujuan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 max-md:block dark:divide-gray-700" id="siswaTableBody">
                            @foreach ($unassignedSiswas as $index => $siswa)
                                <tr class="siswa-row cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors max-md:flex max-md:min-h-16 max-md:items-center max-md:gap-3 max-md:px-4 max-md:py-3"
                                    data-siswa-id="{{ $siswa->id }}"
                                    data-search="{{ strtolower($siswa->nama . ' ' . $siswa->nis . ' ' . ($siswa->nisn ?? '')) }}">
                                    <td class="px-4 py-3 text-center max-md:shrink-0 max-md:p-0">
                                        <input type="checkbox"
                                            class="siswa-checkbox pointer-events-none h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 max-md:h-5 max-md:w-5"
                                            data-siswa-id="{{ $siswa->id }}"
                                            aria-label="{{ __('Pilih :nama', ['nama' => $siswa->nama]) }}">
                                    </td>
                                    <td class="px-4 py-3 row-number max-md:hidden">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono max-md:hidden">{{ $siswa->nis }}</td>
                                    <td class="px-4 py-3 font-mono max-md:hidden">{{ $siswa->nisn ?? '—' }}</td>
                                    <td class="px-4 py-3 font-medium max-md:min-w-0 max-md:flex-1 max-md:p-0">
                                        {{ $siswa->nama }}
                                        <p class="text-xs font-normal text-gray-500 md:hidden dark:text-gray-400">
                                            NIS {{ $siswa->nis }} &bull; NISN {{ $siswa->nisn ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-3 max-md:shrink-0 max-md:p-0">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $siswa->jenis_kelamin === 'L' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-200' }}">
                                            {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 max-md:hidden">
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                            {{ __('Belum ada kelas') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 max-md:hidden">
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                            {{ $kelas->nama }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Di HP pembungkus menjadi "contents": keterangan tetap di bawah daftar, tombol claim
                     menjadi bilah yang menempel di atas menu bawah aplikasi --}}
                <div class="border-t border-gray-100 px-6 py-4 max-md:contents dark:border-gray-700">
                    <div class="flex items-center justify-between max-md:contents">
                        <p class="text-sm text-gray-500 max-md:border-t max-md:border-gray-100 max-md:px-4 max-md:py-3 dark:text-gray-400 max-md:dark:border-gray-700">
                            {{ __('Klik baris untuk memilih siswa yang akan di-claim ke kelas :kelas.', ['kelas' => $kelas->nama]) }}
                        </p>
                        <div
                            class="md:contents max-md:z-20 max-md:border-t max-md:border-gray-100 max-md:bg-white/95 max-md:px-4 max-md:py-3 max-md:backdrop-blur max-md:in-[.mode-aplikasi]:sticky max-md:in-[.mode-aplikasi]:bottom-[calc(4.75rem+env(safe-area-inset-bottom))] max-md:dark:border-gray-700 max-md:dark:bg-gray-800/95">
                        <button type="submit" id="claimButton" disabled
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-blue-300 max-md:h-11 max-md:w-full max-md:justify-center dark:disabled:bg-blue-900/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            {{ __('Claim Siswa Terpilih') }}
                        </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <script>
        (function() {
            const form = document.getElementById('claimForm');
            const hiddenContainer = document.getElementById('hiddenSiswaIds');
            const selectAllCheckbox = document.getElementById('selectAll');
            const claimButton = document.getElementById('claimButton');
            const selectedCountDiv = document.getElementById('selectedCount');
            const selectedCountNumber = document.getElementById('selectedCountNumber');
            const searchInput = document.getElementById('searchInput');
            const tableContainer = document.getElementById('tableContainer');
            const noResults = document.getElementById('noResults');
            const siswaTableBody = document.getElementById('siswaTableBody');

            if (!form) return;

            // Track checked state
            const checkedSiswaIds = new Set();

            // Update UI based on selection
            function updateUI() {
                const count = checkedSiswaIds.size;
                if (count > 0) {
                    selectedCountDiv.classList.remove('hidden');
                    selectedCountNumber.textContent = count;
                    claimButton.disabled = false;
                } else {
                    selectedCountDiv.classList.add('hidden');
                    claimButton.disabled = true;
                }

                // Update select all checkbox state (only consider visible rows)
                const visibleCheckboxes = document.querySelectorAll(
                    '.siswa-row:not([style*="display: none"]) .siswa-checkbox');
                const visibleCheckedCount = Array.from(visibleCheckboxes).filter(cb => cb.checked).length;
                const allVisibleChecked = visibleCheckboxes.length > 0 && visibleCheckedCount === visibleCheckboxes
                    .length;

                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allVisibleChecked;
                    selectAllCheckbox.indeterminate = visibleCheckedCount > 0 && !allVisibleChecked;
                }
            }

            // Function to update row styling
            function updateRowStyle(row, isChecked) {
                if (isChecked) {
                    row.classList.add('bg-blue-50', 'dark:bg-blue-900/30');
                } else {
                    row.classList.remove('bg-blue-50', 'dark:bg-blue-900/30');
                }
            }

            // Live Search functionality
            function performSearch() {
                const query = searchInput.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.siswa-row');
                let visibleCount = 0;
                let rowNumber = 1;

                rows.forEach(function(row) {
                    const searchData = row.dataset.search || '';
                    const matches = query === '' || searchData.includes(query);

                    if (matches) {
                        row.style.display = '';
                        row.querySelector('.row-number').textContent = rowNumber++;
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0 && query !== '') {
                    noResults.classList.remove('hidden');
                    tableContainer.classList.add('hidden');
                } else {
                    noResults.classList.add('hidden');
                    tableContainer.classList.remove('hidden');
                }

                updateUI();
            }

            // Debounce function for search
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // Search input event listener
            searchInput?.addEventListener('input', debounce(performSearch, 200));

            // Select all functionality (only selects visible rows)
            selectAllCheckbox?.addEventListener('change', function() {
                const visibleCheckboxes = document.querySelectorAll(
                    '.siswa-row:not([style*="display: none"]) .siswa-checkbox');
                visibleCheckboxes.forEach(function(cb) {
                    cb.checked = selectAllCheckbox.checked;
                    const row = cb.closest('.siswa-row');
                    const siswaId = cb.dataset.siswaId;
                    if (selectAllCheckbox.checked) {
                        checkedSiswaIds.add(siswaId);
                    } else {
                        checkedSiswaIds.delete(siswaId);
                    }
                    if (row) {
                        updateRowStyle(row, cb.checked);
                    }
                });
                updateUI();
            });

            // Row click to toggle checkbox
            document.addEventListener('click', function(e) {
                const row = e.target.closest('.siswa-row');
                if (row && !e.target.matches('input[type="checkbox"]')) {
                    const checkbox = row.querySelector('.siswa-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        const siswaId = checkbox.dataset.siswaId;
                        if (checkbox.checked) {
                            checkedSiswaIds.add(siswaId);
                        } else {
                            checkedSiswaIds.delete(siswaId);
                        }
                        updateRowStyle(row, checkbox.checked);
                        updateUI();
                    }
                }
            });

            // Before form submit, add all checked IDs as hidden inputs
            form.addEventListener('submit', function(e) {
                if (checkedSiswaIds.size === 0) {
                    e.preventDefault();
                    return;
                }

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

            // Initialize
            updateUI();
        })();
    </script>
</x-layouts.app>
