<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Data Mengajar Guru') }}</h1>
            <p class="text-zinc-600 dark:text-zinc-400 mt-1 max-md:text-sm">
                {{ __('Kelola jadwal mengajar per tahun ajaran dan semester.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="openAddMengajar"
                class="tombol tombol-aksen gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:flex-auto">
                {{ __('Tambah') }}
            </button>
            <button type="button" id="openCopyMengajar"
                class="tombol tombol-netral gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:flex-auto">
                {{ __('Salin dari TA Lain') }}
            </button>
            <button type="button" id="openCopyKelasMengajar"
                class="tombol tombol-netral gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:flex-auto">
                {{ __('Salin dari Kelas Lain') }}
            </button>
        </div>
    </div>

    @if (!$tahunId)
        <div
            class="bg-yellow-50 p-4 text-sm text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-100 rounded-2xl">
            {{ __('Pilih tahun ajaran terlebih dahulu di dashboard.') }}
        </div>
    @else
        <div class="mb-4 grid gap-3 max-md:grid-cols-2 md:grid-cols-2 lg:grid-cols-3">
            <form method="GET" action="{{ route('mengajar.index') }}" class="flex min-w-0 flex-col gap-2">
                <label class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Tingkat') }}</label>
                <select name="tingkat" onchange="this.form.submit()"
                    class="isian px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                    @foreach ($tingkats as $tingkat)
                        <option value="{{ $tingkat }}" @selected($tingkat == $selectedTingkat)>{{ $tingkat }}</option>
                    @endforeach
                </select>
            </form>
            <form method="GET" action="{{ route('mengajar.index') }}" class="flex min-w-0 flex-col gap-2">
                <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
                <label class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Kelas') }}</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="isian px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                    @foreach ($kelasList->where('tingkat', $selectedTingkat) as $kelas)
                        <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div
            class="kartu overflow-hidden max-md:overflow-clip">
            @if ($mataPelajarans->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Belum ada mata pelajaran yang terdaftar.') }}
                </div>
            @else
                <form action="{{ route('mengajar.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                    {{-- Di HP setiap baris tampil sebagai kartu bertumpuk; mulai md kembali menjadi tabel --}}
                    <div class="px-6 pb-6 overflow-x-auto max-md:overflow-visible max-md:p-0">
                        <table id="mengajar-table"
                            class="tabel min-w-full text-left text-sm text-zinc-700 max-md:block dark:text-zinc-200">
                            <thead
                                class="max-md:hidden">
                                <tr>
                                    <th class="px-3 py-3">{{ __('Mata Pelajaran') }}</th>
                                    <th class="px-3 py-3">{{ __('Induk') }}</th>
                                    <th class="px-3 py-3">{{ __('JTM') }}</th>
                                    <th class="px-3 py-3">{{ __('Guru Pengajar') }}</th>
                                </tr>
                            </thead>
                            <tbody class="max-md:block">
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
                                        <tr class="bg-zinc-50 max-md:block dark:bg-zinc-800/50">
                                            <td class="px-3 py-3 max-md:block max-md:px-4 max-md:py-2.5" colspan="4">
                                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ __('Pendidikan Agama Islam') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    {{-- Group header for Mulok --}}
                                    @if ($mapel->kelompok === 'Mulok' && $currentKelompok !== 'Mulok')
                                        @php $currentKelompok = 'Mulok'; $rowNumber++; @endphp
                                        <tr class="bg-zinc-50 max-md:block dark:bg-zinc-800/50">
                                            <td class="px-3 py-3 max-md:block max-md:px-4 max-md:py-2.5" colspan="4">
                                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ __('Muatan Lokal') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    @php 
                                        if ($currentKelompok !== 'PAI' && $currentKelompok !== 'Mulok' && $mapel->kelompok === 'Umum') {
                                            $currentKelompok = 'Umum';
                                        }
                                        $rowNumber++; 
                                    @endphp
                                    <tr
                                        class="max-md:grid max-md:grid-cols-[5.5rem_minmax(0,1fr)] max-md:items-end max-md:gap-x-3 max-md:gap-y-2 max-md:px-4 max-md:py-3">
                                        <td class="px-3 py-3 max-md:col-span-2 max-md:p-0">
                                            <input type="hidden" name="items[{{ $mapel->id }}][mata_pelajaran_id]"
                                                value="{{ $mapel->id }}">
                                            <div class="{{ $isChildSubject ? 'italic pl-4 max-md:pl-0' : '' }} font-semibold text-zinc-900 dark:text-zinc-100">
                                                {{ $mapel->nama_mapel }}</div>
                                            @if ($mapel->kode)
                                                <div class="text-xs text-zinc-600 {{ $isChildSubject ? 'pl-4 max-md:pl-0' : '' }}">{{ $mapel->kode }}</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 max-md:hidden">{{ $isChildSubject ? $mapel->kelompok : '—' }}</td>
                                        <td class="px-3 py-3 max-md:p-0">
                                            <label for="mengajar-jtm-{{ $mapel->id }}"
                                                class="mb-1 block text-xs font-semibold text-zinc-600 md:sr-only dark:text-zinc-400">{{ __('JTM') }}</label>
                                            <input type="number" id="mengajar-jtm-{{ $mapel->id }}" name="items[{{ $mapel->id }}][jtm]" min="0"
                                                inputmode="numeric" value="{{ $jtmValue }}"
                                                class="isian w-20 px-2 py-1 text-sm max-md:h-11 max-md:w-full max-md:rounded-lg max-md:text-center max-md:text-base">
                                        </td>
                                        <td class="px-3 py-3 max-md:min-w-0 max-md:p-0">
                                            <label for="mengajar-guru-{{ $mapel->id }}"
                                                class="mb-1 block text-xs font-semibold text-zinc-600 md:sr-only dark:text-zinc-400">{{ __('Guru Pengajar') }}</label>
                                            <select id="mengajar-guru-{{ $mapel->id }}" name="items[{{ $mapel->id }}][guru_id]"
                                                class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
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
                    {{-- Di HP bilah simpan menempel di bawah layar (di atas menu bawah aplikasi) selama form terlihat --}}
                    <div class="border-t border-zinc-100 px-6 py-4 text-right max-md:px-4 max-md:py-3 max-md:sticky max-md:bottom-0 max-md:z-20 max-md:bg-white/95 max-md:shadow-[0_-4px_12px_rgba(0,0,0,0.08)] max-md:backdrop-blur max-md:dark:bg-zinc-800/95 in-[.mode-aplikasi]:max-md:bottom-[calc(4.75rem+env(safe-area-inset-bottom))] dark:border-zinc-700">
                        <button type="submit"
                            class="tombol tombol-aksen gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:w-full">
                            {{ __('Simpan Jadwal Mengajar') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    {{-- Di HP modal tampil sebagai lembar bawah selebar layar dan bisa digulir --}}
    <div id="mengajarModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0">
        <div id="addMengajarModal"
            class="permukaan modal-card hidden w-full max-w-3xl overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:rounded-b-none max-md:pb-[env(safe-area-inset-bottom)] shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900/40">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Tambah Jadwal Mengajar') }}
                </h3>
            </div>
            <form action="{{ route('mengajar.store') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Kelas') }}</label>
                        <select name="kelas_id" required
                            class="isian mt-1 w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Mata Pelajaran') }}</label>
                        <select name="items[0][mata_pelajaran_id]" required
                            class="isian mt-1 w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                            @foreach ($mataPelajarans as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Guru') }}</label>
                        <select name="items[0][guru_id]"
                            class="isian mt-1 w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                            <option value="">- {{ __('Pilih Guru') }} -</option>
                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('JTM') }}</label>
                        <input type="number" name="items[0][jtm]" min="0"
                            class="isian mt-1 w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                    </div>
                </div>
                <div
                    class="mt-4 flex items-center justify-end gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-700">
                    <button type="button" data-close-modal
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:flex-1">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="tombol tombol-aksen gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:flex-1">
                        {{ __('Simpan') }}
                    </button>
                </div>
            </form>
        </div>

        <div id="copyMengajarModal"
            class="permukaan modal-card hidden w-full max-w-md overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:rounded-b-none max-md:pb-[env(safe-area-inset-bottom)] shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900/40">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Salin Mengajar') }}</h3>
            </div>
            <form action="{{ route('mengajar.copy') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <p class="rounded-lg bg-zinc-50 px-3 py-2 text-xs text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300">
                    {{ __('Jadwal mengajar kelas yang sama pada tahun ajaran sumber akan disalin ke kelas yang dipilih, beserta guru dan JTM-nya. Kelas dicocokkan berdasarkan nama (contoh: 1A dengan 1A), dan semester sumber boleh berbeda dari semester yang sedang aktif — misalnya menyalin jadwal Ganjil ke Genap.') }}
                </p>
                <div class="space-y-2">
                    <label
                        class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Sumber Tahun Ajaran') }}</label>
                    <select name="source_tahun_ajaran_id" required
                        class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                        @forelse ($tahunOptions as $option)
                            @php($jumlahGanjil = (int) $jadwalPerTahun->get($option->id.'-Ganjil', 0))
                            @php($jumlahGenap = (int) $jadwalPerTahun->get($option->id.'-Genap', 0))
                            <option value="{{ $option->id }}">
                                {{ $option->nama }}{{ $option->is_active ? ' — Aktif' : '' }}{{ $option->id == $tahunId ? ' ('.__('tahun ajaran ini').')' : '' }} —
                                {{ __('Ganjil: :ganjil jadwal · Genap: :genap jadwal', ['ganjil' => $jumlahGanjil, 'genap' => $jumlahGenap]) }}
                            </option>
                        @empty
                            <option value="">{{ __('Belum ada tahun ajaran.') }}</option>
                        @endforelse
                    </select>
                </div>
                <div class="space-y-2">
                    <label
                        class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Semester Sumber') }}</label>
                    <select name="source_semester" required
                        class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                        <option value="Ganjil" @selected($semester === 'Ganjil')>{{ __('Ganjil') }}</option>
                        <option value="Genap" @selected($semester !== 'Ganjil')>{{ __('Genap') }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Kelas') }}</label>
                    @if ($kelasList->isEmpty())
                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">
                            {{ __('Tahun ajaran aktif belum punya kelas. Tambahkan kelas atau jalankan sync siswa terlebih dahulu.') }}
                        </p>
                    @else
                        <select name="kelas_id" required
                            class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div
                    class="mt-4 flex items-center justify-end gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-700">
                    <button type="button" data-close-modal
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:flex-1">{{ __('Batal') }}</button>
                    <button type="submit" @disabled($kelasList->isEmpty())
                        class="tombol tombol-netral gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:flex-1">
                        {{ __('Salin') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Salin dari Kelas Lain --}}
    <div id="copyKelasModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0">
        <div id="copyKelasModal"
            class="permukaan w-full max-w-md overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:rounded-b-none max-md:pb-[env(safe-area-inset-bottom)] rounded-3xl bg-white shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900/40">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Salin dari Kelas Lain') }}</h3>
            </div>
            <form action="{{ route('mengajar.copy-kelas') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <input type="hidden" name="target_kelas_id" value="{{ $selectedKelasId }}">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Kelas Tujuan') }}</label>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $selectedKelas?->nama ?? '-' }}
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Salin dari Kelas') }}</label>
                    @if ($kelasList->count() < 2)
                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">
                            {{ __('Tahun ajaran aktif baru punya satu kelas, jadi belum ada kelas lain yang bisa dijadikan sumber.') }}
                        </p>
                    @else
                        <select name="source_kelas_id" required
                            class="isian w-full px-3 py-2 text-sm max-md:h-11 max-md:text-base">
                            <option value="">- {{ __('Pilih Kelas Sumber') }} -</option>
                            @foreach ($kelasList as $kelas)
                                @continue($kelas->id == $selectedKelasId)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                    {{ __('Data mengajar (guru & JTM) dari kelas sumber akan disalin ke kelas tujuan pada semester yang sedang aktif. Data yang sudah ada akan di-update.') }}
                </p>
                <div class="mt-4 flex items-center justify-end gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-700">
                    <button type="button" data-close-copy-kelas
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:flex-1">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="tombol tombol-netral gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:flex-1">
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

            // Copy Kelas modal
            const copyKelasOverlay = document.getElementById('copyKelasModalOverlay');
            const openCopyKelas = document.getElementById('openCopyKelasMengajar');

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

            function openCopyKelasModal() {
                copyKelasOverlay.classList.remove('hidden');
                copyKelasOverlay.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function closeCopyKelasModal() {
                copyKelasOverlay.classList.remove('flex');
                copyKelasOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openAdd?.addEventListener('click', () => openModal(addModal));
            openCopy?.addEventListener('click', () => openModal(copyModal));
            openCopyKelas?.addEventListener('click', () => openCopyKelasModal());

            overlay?.addEventListener('click', (event) => {
                if (event.target === overlay) closeModal();
            });
            copyKelasOverlay?.addEventListener('click', (event) => {
                if (event.target === copyKelasOverlay) closeCopyKelasModal();
            });
            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => closeModal());
            });
            document.querySelectorAll('[data-close-copy-kelas]').forEach((button) => {
                button.addEventListener('click', () => closeCopyKelasModal());
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                    closeCopyKelasModal();
                }
            });
        })();
    </script>
</x-layouts.app>
