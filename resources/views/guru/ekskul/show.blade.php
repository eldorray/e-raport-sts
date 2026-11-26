<x-layouts.app>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">Penilaian Ekskul</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $ekskul->nama }} — Pembina:
                {{ optional($ekskul->guru)->nama ?? '-' }}</p>
        </div>
        <div
            class="bg-accent/10 px-3 py-2 text-xs text-accent-strong dark:bg-blue-900/40 dark:text-blue-100 rounded-2xl">
            Tahun ajaran: {{ $tahunAjaran?->nama ?? '–' }} @if ($semester)
                • Semester: {{ $semester }}
            @endif
        </div>
    </div>

    @if (!$canEdit)
        <div class="mb-4 bg-amber-50 p-4 dark:bg-amber-900/30 rounded-2xl">
            <div class="flex items-center gap-2 text-sm font-semibold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __('Tahun ajaran yang dipilih tidak aktif. Anda hanya dapat melihat data, tidak dapat mengubah data.') }}
            </div>
        </div>
    @endif

    <div class="flex items-center justify-between mb-3 max-md:flex-col max-md:items-stretch max-md:gap-3">
        <div class="text-sm text-zinc-600 dark:text-zinc-300">Tambahkan siswa yang ikut ekskul sebelum memberi nilai.
        </div>
        @if ($canEdit)
            <button type="button" id="openAddSiswa"
                class="tombol tombol-aksen gap-2 px-4 py-2 text-sm font-semibold max-md:h-11">
                <i class="fa-solid fa-user-plus text-xs"></i> Tambah Siswa
            </button>
        @endif
    </div>

    <form method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}"
        class="kartu">
        @csrf
        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        {{-- Satu markup: tabel di layar lebar, satu kartu per peserta di HP (input tetap sama) --}}
        <div class="overflow-x-auto px-6 py-4 max-md:p-0">
            <table
                class="tabel min-w-full text-left text-sm text-zinc-700 max-md:block dark:text-zinc-200">
                <thead
                    class="max-md:hidden">
                    <tr>
                        <th class="px-3 py-3">No</th>
                        <th class="px-3 py-3">Nama Siswa</th>
                        <th class="px-3 py-3">Kelas</th>
                        <th class="px-3 py-3">Nilai</th>
                        <th class="px-3 py-3">Predikat</th>
                        <th class="px-3 py-3">Catatan</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="max-md:block">
                    @forelse ($participants as $index => $siswa)
                        @php $record = $existing[$siswa->id] ?? null; @endphp
                        <tr
                            class="max-md:grid max-md:grid-cols-[minmax(0,1fr)_auto] max-md:gap-x-3 max-md:gap-y-3 max-md:px-4 max-md:py-4">
                            <td class="px-3 py-3 max-md:hidden">{{ $index + 1 }}</td>
                            <td class="px-3 py-3 max-md:col-start-1 max-md:row-start-1 max-md:min-w-0 max-md:p-0">
                                <div class="flex items-baseline gap-2">
                                    <span
                                        class="text-xs font-semibold tabular-nums text-zinc-400 md:hidden dark:text-zinc-600">{{ $index + 1 }}.</span>
                                    <span class="max-md:font-semibold max-md:text-zinc-900 max-md:dark:text-zinc-100">{{ $siswa->nama }}</span>
                                </div>
                                <p class="text-xs text-zinc-600 md:hidden dark:text-zinc-400">
                                    {{ $siswa->kelas->nama ?? '—' }}</p>
                            </td>
                            <td class="px-3 py-3 max-md:hidden">{{ $siswa->kelas->nama ?? '—' }}</td>
                            <td class="px-3 py-3 max-md:p-0">
                                <label for="nilai_{{ $siswa->id }}"
                                    class="mb-1 block text-xs font-medium text-zinc-600 md:hidden dark:text-zinc-400">Nilai</label>
                                <input type="number" id="nilai_{{ $siswa->id }}" name="nilai[{{ $siswa->id }}]" min="0" max="100"
                                    step="0.01" value="{{ old('nilai.' . $siswa->id, $record?->nilai) }}"
                                    @disabled(!$canEdit)
                                    class="isian w-28 px-2 py-1 text-sm max-md:h-11 max-md:w-full max-md:rounded-lg max-md:px-3 max-md:text-base">
                            </td>
                            <td class="px-3 py-3 max-md:p-0">
                                <span
                                    class="mb-1 block text-xs font-medium text-zinc-600 md:hidden dark:text-zinc-400">Predikat</span>
                                @if ($record?->predikat)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $record->predikat === 'A' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : '' }}
                                        {{ $record->predikat === 'B' ? 'bg-accent/10 text-accent-strong dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                        {{ $record->predikat === 'C' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' : '' }}
                                        {{ $record->predikat === 'D' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : '' }}">
                                        {{ $record->predikat }} - {{ $record->predikat_keterangan }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 max-md:flex max-md:h-11 max-md:items-center">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 max-md:col-span-2 max-md:p-0">
                                <label for="catatan_{{ $siswa->id }}"
                                    class="mb-1 block text-xs font-medium text-zinc-600 md:hidden dark:text-zinc-400">Catatan</label>
                                <input type="text" id="catatan_{{ $siswa->id }}" name="catatan[{{ $siswa->id }}]"
                                    value="{{ old('catatan.' . $siswa->id, $record?->catatan) }}" @disabled(!$canEdit)
                                    class="isian w-full px-2 py-1 text-sm max-md:h-11 max-md:rounded-lg max-md:px-3 max-md:text-base">
                            </td>
                            <td
                                class="px-3 py-3 text-center max-md:col-start-2 max-md:row-start-1 max-md:self-start max-md:justify-self-end max-md:p-0 {{ $canEdit ? '' : 'max-md:hidden' }}">
                                @if ($canEdit)
                                    <button type="submit" form="remove-{{ $siswa->id }}"
                                        class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-200 max-md:h-11 max-md:rounded-lg max-md:px-4 max-md:text-sm dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60"
                                        onclick="return confirm('Batalkan siswa ini dari penilaian ekskul?');">
                                        Batal
                                    </button>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="max-md:block">
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-zinc-600 max-md:block">Belum ada siswa
                                peserta ekskul ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Di HP (mode aplikasi) bilah simpan menempel di atas menu bawah --}}
        <div
            class="border-t border-zinc-100 px-6 py-4 text-right max-md:px-4 max-md:py-3 dark:border-zinc-700 {{ $canEdit ? 'max-md:z-20 max-md:rounded-b-2xl max-md:bg-white/95 max-md:backdrop-blur max-md:in-[.mode-aplikasi]:sticky max-md:in-[.mode-aplikasi]:bottom-[calc(4.75rem+env(safe-area-inset-bottom))] max-md:dark:bg-zinc-800/95' : '' }}">
            @if ($canEdit)
                <button type="submit"
                    class="tombol tombol-aksen gap-2 px-5 py-2.5 text-sm font-semibold max-md:h-11 max-md:w-full">
                    Simpan Penilaian
                </button>
            @endif
        </div>
    </form>

    @foreach ($participants as $siswa)
        <form id="remove-{{ $siswa->id }}" method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}"
            class="hidden">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
            <input type="hidden" name="semester" value="{{ $semester }}">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
        </form>
    @endforeach

    <!-- Modal tambah siswa -->
    {{-- Di HP modal tampil sebagai lembar bawah selebar layar --}}
    <div id="modalOverlay"
        class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0">
        <div id="addModal"
            class="permukaan modal-card hidden w-full max-w-3xl overflow-hidden max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="px-6 pb-1 pt-6 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tambah Peserta Ekskul</h3>
            </div>
            <form method="POST" action="{{ route('guru.ekskul.store', $ekskul) }}"
                class="space-y-4 px-6 py-6 max-md:px-4 max-md:pt-4 max-md:pb-[calc(1rem+env(safe-area-inset-bottom))]">
                @csrf
                <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunId }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <input type="hidden" name="action" value="add">
                <div class="flex items-center gap-2 max-md:flex-col max-md:items-stretch">
                    <div class="relative w-full max-w-sm max-md:max-w-none">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
                        <input type="text" id="searchSiswa" @keydown.enter.prevent placeholder="Cari nama atau kelas"
                            class="isian w-full px-9 py-2 text-sm max-md:h-11 max-md:text-base">
                    </div>
                    <span class="text-xs text-zinc-600 dark:text-zinc-400">Ketik untuk memfilter daftar</span>
                </div>
                <div class="max-h-96 overflow-y-auto max-md:max-h-[50dvh]">
                    <table
                        class="tabel min-w-full text-left text-sm text-zinc-700 dark:text-zinc-200">
                        <thead
                           >
                            <tr>
                                <th class="px-3 py-3 text-center">Pilih</th>
                                <th class="px-3 py-3">Nama</th>
                                <th class="px-3 py-3">Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($availableSiswas as $siswa)
                                <tr>
                                    <td class="px-3 py-2 text-center max-md:py-3">
                                        <input type="checkbox" id="calon_{{ $siswa->id }}" name="siswa_ids[]"
                                            value="{{ $siswa->id }}"
                                            class="accent-accent h-4 w-4 max-md:h-5 max-md:w-5">
                                    </td>
                                    <td class="px-3 py-2 max-md:py-3">
                                        <label for="calon_{{ $siswa->id }}" class="block cursor-pointer">{{ $siswa->nama }}</label>
                                    </td>
                                    <td class="px-3 py-2 max-md:py-3">
                                        <label for="calon_{{ $siswa->id }}" class="block cursor-pointer">{{ $siswa->kelas->nama ?? '—' }}</label>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-center text-sm text-zinc-600">Semua siswa
                                        sudah terdaftar sebagai peserta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" data-close-modal
                        class="rounded-lg bg-zinc-100 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-200 focus:outline-none max-md:h-11 max-md:flex-1 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">Batal</button>
                    <button type="submit"
                        class="tombol tombol-aksen px-4 py-2 text-sm font-semibold max-md:h-11 max-md:flex-1">Tambahkan</button>
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
