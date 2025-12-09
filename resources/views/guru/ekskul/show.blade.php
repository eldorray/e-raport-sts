<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Penilaian Ekskul</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $ekskul->nama }} — Pembina:
                {{ optional($ekskul->guru)->nama ?? '-' }}</p>
        </div>
        <div
            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-700 dark:border-blue-800 dark:bg-blue-900/40 dark:text-blue-100">
            Tahun ajaran: {{ $tahunId ?? '–' }} @if ($semester)
                • Semester: {{ $semester }}
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between mb-3">
        <div class="text-sm text-gray-600 dark:text-gray-300">Tambahkan siswa yang ikut ekskul sebelum memberi nilai.
        </div>
        <button type="button" id="openAddSiswa"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/30">
            <i class="fa-solid fa-user-plus text-xs"></i> Tambah Siswa
        </button>
    </div>

    <form method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}"
        class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @csrf
        <div class="overflow-x-auto px-6 py-4">
            <table
                class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3">No</th>
                        <th class="px-3 py-3">Nama Siswa</th>
                        <th class="px-3 py-3">Kelas</th>
                        <th class="px-3 py-3">Nilai</th>
                        <th class="px-3 py-3">Catatan</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($participants as $index => $siswa)
                        @php $record = $existing[$siswa->id] ?? null; @endphp
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $siswa->nama }}</td>
                            <td class="px-3 py-3">{{ $siswa->kelas->nama ?? '—' }}</td>
                            <td class="px-3 py-3">
                                <input type="number" name="nilai[{{ $siswa->id }}]" min="0" max="100"
                                    step="0.01" value="{{ $record?->nilai }}"
                                    class="w-28 rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" name="catatan[{{ $siswa->id }}]"
                                    value="{{ $record?->catatan }}"
                                    class="w-full rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button type="submit" form="remove-{{ $siswa->id }}"
                                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-200"
                                    onclick="return confirm('Batalkan siswa ini dari penilaian ekskul?');">
                                    Batal
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">Belum ada siswa
                                peserta ekskul ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-6 py-4 text-right dark:border-gray-700">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                Simpan Penilaian
            </button>
        </div>
    </form>

    @foreach ($participants as $siswa)
        <form id="remove-{{ $siswa->id }}" method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}"
            class="hidden">
            @csrf
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
        </form>
    @endforeach

    <!-- Modal tambah siswa -->
    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="addModal"
            class="modal-card hidden w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tambah Peserta Ekskul</h3>
            </div>
            <form method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}" class="space-y-4 px-6 py-6">
                @csrf
                <input type="hidden" name="action" value="add">
                <div class="flex items-center gap-2">
                    <div class="relative w-full max-w-sm">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="searchSiswa" placeholder="Cari nama atau kelas"
                            class="w-full rounded-lg border border-gray-300 bg-white px-9 py-2 text-sm text-gray-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Ketik untuk memfilter daftar</span>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <table
                        class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                        <thead
                            class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-3 text-center">Pilih</th>
                                <th class="px-3 py-3">Nama</th>
                                <th class="px-3 py-3">Kelas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($availableSiswas as $siswa)
                                <tr>
                                    <td class="px-3 py-2 text-center">
                                        <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-3 py-2">{{ $siswa->nama }}</td>
                                    <td class="px-3 py-2">{{ $siswa->kelas->nama ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-center text-sm text-gray-500">Semua siswa
                                        sudah terdaftar sebagai peserta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" data-close-modal
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 focus:outline-none">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/30">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const overlay = document.getElementById('modalOverlay');
            const modal = document.getElementById('addModal');
            const openBtn = document.getElementById('openAddSiswa');
            const searchInput = document.getElementById('searchSiswa');
            const rows = Array.from(document.querySelectorAll('#addModal tbody tr'));

            function openModal() {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                overlay.classList.remove('flex');
                overlay.classList.add('hidden');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openBtn?.addEventListener('click', openModal);
            overlay?.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal();
            });
            document.querySelectorAll('[data-close-modal]').forEach((btn) => btn.addEventListener('click', closeModal));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });

            searchInput?.addEventListener('input', () => {
                const term = searchInput.value.toLowerCase();
                rows.forEach((row) => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            });
        })();
    </script>
</x-layouts.app>
