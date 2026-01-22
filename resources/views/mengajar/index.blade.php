<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Data Mengajar Guru') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Kelola jadwal mengajar per tahun ajaran dan semester.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="openAddMengajar"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                {{ __('Tambah') }}
            </button>
            <button type="button" id="openCopyMengajar"
                class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-500/30">
                {{ __('Salin Mengajar') }}
            </button>
        </div>
    </div>

    @if (!$tahunId)
        <div
            class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 shadow-sm dark:border-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-100">
            {{ __('Pilih tahun ajaran terlebih dahulu di dashboard.') }}
        </div>
    @else
        <div class="mb-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            <form method="GET" action="{{ route('mengajar.index') }}" class="flex flex-col gap-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Tingkat') }}</label>
                <select name="tingkat" onchange="this.form.submit()"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    @foreach ($tingkats as $tingkat)
                        <option value="{{ $tingkat }}" @selected($tingkat == $selectedTingkat)>{{ $tingkat }}</option>
                    @endforeach
                </select>
            </form>
            <form method="GET" action="{{ route('mengajar.index') }}" class="flex flex-col gap-2">
                <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Kelas') }}</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    @foreach ($kelasList->where('tingkat', $selectedTingkat) as $kelas)
                        <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @if ($mataPelajarans->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Belum ada mata pelajaran yang terdaftar.') }}
                </div>
            @else
                <form action="{{ route('mengajar.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                    <div class="px-6 pb-6 overflow-x-auto">
                        <table id="mengajar-table"
                            class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                            <thead
                                class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                                <tr>
                                    <th class="px-3 py-3">{{ __('Mata Pelajaran') }}</th>
                                    <th class="px-3 py-3">{{ __('Induk') }}</th>
                                    <th class="px-3 py-3">{{ __('JTM') }}</th>
                                    <th class="px-3 py-3">{{ __('Guru Pengajar') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @php $currentKelompok = null; $rowNumber = 0; @endphp
                                @foreach ($mataPelajarans as $mapel)
                                    @php
                                        $assignment = $mengajarByMapel->get($mapel->id);
                                        $selectedGuruId = $assignment?->guru_id;
                                        $jtmValue = old(
                                            "items.{$mapel->id}.jtm",
                                            $assignment?->jtm ?? $mapel->jumlah_jam,
                                        );
                                        $isChildSubject = in_array($mapel->kelompok, ['PAI', 'Mulok']);
                                    @endphp
                                    
                                    {{-- Group header for PAI --}}
                                    @if ($mapel->kelompok === 'PAI' && $currentKelompok !== 'PAI')
                                        @php $currentKelompok = 'PAI'; $rowNumber++; @endphp
                                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                                            <td class="px-3 py-3" colspan="4">
                                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ __('Pendidikan Agama Islam') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    {{-- Group header for Mulok --}}
                                    @if ($mapel->kelompok === 'Mulok' && $currentKelompok !== 'Mulok')
                                        @php $currentKelompok = 'Mulok'; $rowNumber++; @endphp
                                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                                            <td class="px-3 py-3" colspan="4">
                                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ __('Muatan Lokal') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    @php 
                                        if ($currentKelompok !== 'PAI' && $currentKelompok !== 'Mulok' && $mapel->kelompok === 'Umum') {
                                            $currentKelompok = 'Umum';
                                        }
                                        $rowNumber++; 
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-3">
                                            <input type="hidden" name="items[{{ $mapel->id }}][mata_pelajaran_id]"
                                                value="{{ $mapel->id }}">
                                            <div class="{{ $isChildSubject ? 'italic pl-4' : '' }} font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $mapel->nama_mapel }}</div>
                                            @if ($mapel->kode)
                                                <div class="text-xs text-gray-500 {{ $isChildSubject ? 'pl-4' : '' }}">{{ $mapel->kode }}</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">{{ $isChildSubject ? $mapel->kelompok : '—' }}</td>
                                        <td class="px-3 py-3">
                                            <input type="number" name="items[{{ $mapel->id }}][jtm]" min="0"
                                                value="{{ $jtmValue }}"
                                                class="w-20 rounded-md border border-gray-200 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                        </td>
                                        <td class="px-3 py-3">
                                            <select name="items[{{ $mapel->id }}][guru_id]"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                                <option value="">- {{ __('Pilih Guru') }} -</option>
                                                @foreach ($gurus as $guru)
                                                    <option value="{{ $guru->id }}" @selected($guru->id == $selectedGuruId)>
                                                        {{ $guru->nama }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-100 px-6 py-4 text-right dark:border-gray-700">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                            {{ __('Simpan Jadwal Mengajar') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    <div id="mengajarModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="addMengajarModal"
            class="modal-card hidden w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Tambah Jadwal Mengajar') }}
                </h3>
            </div>
            <form action="{{ route('mengajar.store') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kelas') }}</label>
                        <select name="kelas_id" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Mata Pelajaran') }}</label>
                        <select name="mata_pelajaran_id" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            @foreach ($mataPelajarans as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Guru') }}</label>
                        <select name="guru_id"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">- {{ __('Pilih Guru') }} -</option>
                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('JTM') }}</label>
                        <input type="number" name="jtm" min="0"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
                <div
                    class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button" data-close-modal
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                        {{ __('Simpan') }}
                    </button>
                </div>
            </form>
        </div>

        <div id="copyMengajarModal"
            class="modal-card hidden w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Salin Mengajar') }}</h3>
            </div>
            <form action="{{ route('mengajar.copy') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div class="space-y-2">
                    <label
                        class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Sumber Tahun Ajaran') }}</label>
                    <select name="source_tahun_ajaran_id" required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        @foreach ($tahunOptions as $option)
                            @continue($option->id == $tahunId)
                            <option value="{{ $option->id }}">
                                {{ $option->nama }}{{ $option->is_active ? ' — Aktif' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kelas') }}</label>
                    <select name="kelas_id" required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div
                    class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button" data-close-modal
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-500/30">
                        {{ __('Salin') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const overlay = document.getElementById('mengajarModalOverlay');
            const addModal = document.getElementById('addMengajarModal');
            const copyModal = document.getElementById('copyMengajarModal');
            const openAdd = document.getElementById('openAddMengajar');
            const openCopy = document.getElementById('openCopyMengajar');

            function openModal(modal) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                overlay.classList.remove('flex');
                overlay.classList.add('hidden');
                addModal.classList.add('hidden');
                copyModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openAdd?.addEventListener('click', () => openModal(addModal));
            openCopy?.addEventListener('click', () => openModal(copyModal));

            overlay?.addEventListener('click', (event) => {
                if (event.target === overlay) closeModal();
            });
            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => closeModal());
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });
        })();
    </script>
</x-layouts.app>
