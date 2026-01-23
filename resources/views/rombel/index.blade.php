<x-layouts.app>
    @php
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();
        $currentTahunId = session('selected_tahun_ajaran_id');
    @endphp

    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Rombel Kelas') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Atur anggota rombel untuk setiap kelas.') }}</p>
        </div>
        <button type="button" onclick="document.getElementById('copyModal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            <i class="fas fa-copy"></i> {{ __('Salin dari Tahun Ajaran Lain') }}
        </button>
    </div>

    @if ($kelasList->isNotEmpty())
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
            @foreach ($kelasList as $kelas)
                <div
                    class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
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
        <form method="GET" action="{{ route('rombel.index') }}" class="flex items-center gap-2">
            <select name="kelas_id"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                @foreach ($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" @selected(optional($selectedKelas)->id === $kelas->id)>
                        {{ $kelas->nama }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">{{ __('Pilih') }}</button>
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
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
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
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Anggota Rombel') }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ __('Centang siswa untuk menetapkan ke kelas ini. Siswa tanpa kelas juga ditampilkan.') }}
                    </p>
                </div>

                <form id="rombelForm" method="POST" action="{{ $selectedKelas ? route('rombel.update', $selectedKelas) : '#' }}">
                    @csrf
                    @method('PUT')
                    
                    {{-- Hidden container for checked siswa IDs --}}
                    <div id="hiddenSiswaIds"></div>

                    @if ($siswas->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Belum ada siswa di kelas ini.') }}
                        </div>
                    @else
                        <div class="overflow-x-auto px-6 py-4">
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
                                        <tr>
                                            <td class="px-3 py-3 text-center">
                                                <input type="checkbox" class="siswa-checkbox" data-siswa-id="{{ $siswa->id }}"
                                                    @checked($selectedKelas && $siswa->kelas_id === $selectedKelas->id)
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

                    <div class="border-t border-gray-100 px-6 py-4 text-right dark:border-gray-700">
                        <button type="submit" @disabled(!$selectedKelas)
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-blue-300">
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
            
            // Initialize with already checked checkboxes
            document.querySelectorAll('.siswa-checkbox:checked').forEach(function(cb) {
                checkedSiswaIds.add(cb.dataset.siswaId);
            });
            
            // Listen for checkbox changes (works even with DataTables pagination)
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('siswa-checkbox')) {
                    const siswaId = e.target.dataset.siswaId;
                    if (e.target.checked) {
                        checkedSiswaIds.add(siswaId);
                    } else {
                        checkedSiswaIds.delete(siswaId);
                    }
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

    {{-- Copy Modal --}}
    <div id="copyModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('copyModal').classList.add('hidden')"></div>
            
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <i class="fas fa-copy mr-2 text-emerald-600"></i>
                        {{ __('Salin Rombel') }}
                    </h3>
                    <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
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
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">-- {{ __('Pilih Tahun Ajaran Sumber') }} --</option>
                            @foreach ($tahunAjarans as $tahun)
                                @if ($tahun->id != $currentTahunId)
                                    <option value="{{ $tahun->id }}">
                                        {{ $tahun->nama }} - {{ $tahun->semester }}
                                        @if ($tahun->is_active) (Aktif) @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-700 dark:bg-amber-900/50 dark:text-amber-200">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('Siswa akan disalin ke kelas dengan nama yang sama di tahun ajaran saat ini.') }}
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            <i class="fas fa-copy mr-1"></i> {{ __('Salin Rombel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
