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
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Kembali ke Siswa Kelas') }}
            </a>
        </div>
    </div>

    @if (session('status'))
        <div
            class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if (session('warning'))
        <div
            class="mb-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
            {{ session('warning') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    @error('siswa_ids')
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Siswa Belum Memiliki Kelas') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Ditemukan :count siswa yang dapat di-claim.', ['count' => $unassignedSiswas->count()]) }}
                    </p>
                </div>
                <div id="selectedCount"
                    class="hidden rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                    <span id="selectedCountNumber">0</span> {{ __('dipilih') }}
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

                <div class="overflow-x-auto">
                    <table id="unassigned-table"
                        class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                        <thead
                            class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">
                                    <input type="checkbox" id="selectAll"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">NIS</th>
                                <th class="px-4 py-3">NISN</th>
                                <th class="px-4 py-3">{{ __('Nama') }}</th>
                                <th class="px-4 py-3">{{ __('Jenis Kelamin') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($unassignedSiswas as $index => $siswa)
                                <tr class="siswa-row cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                    data-siswa-id="{{ $siswa->id }}">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" class="siswa-checkbox pointer-events-none"
                                            data-siswa-id="{{ $siswa->id }}"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-mono">{{ $siswa->nis }}</td>
                                    <td class="px-4 py-3 font-mono">{{ $siswa->nisn ?? '—' }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $siswa->nama }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $siswa->jenis_kelamin === 'L' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-200' }}">
                                            {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Klik baris untuk memilih siswa yang akan di-claim ke kelas :kelas.', ['kelas' => $kelas->nama]) }}
                        </p>
                        <button type="submit" id="claimButton" disabled
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-blue-300 dark:disabled:bg-blue-900/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            {{ __('Claim Siswa Terpilih') }}
                        </button>
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

                // Update select all checkbox state
                const allCheckboxes = document.querySelectorAll('.siswa-checkbox');
                const allChecked = allCheckboxes.length > 0 && checkedSiswaIds.size === allCheckboxes.length;
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = count > 0 && !allChecked;
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

            // Select all functionality
            selectAllCheckbox?.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.siswa-checkbox');
                allCheckboxes.forEach(function(cb) {
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
