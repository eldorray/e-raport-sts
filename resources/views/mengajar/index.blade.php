<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 max-md:text-xl dark:text-gray-100">{{ __('Data Mengajar Guru') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 max-md:text-sm">
                {{ __('Kelola jadwal mengajar per tahun ajaran dan semester.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="openAddMengajar"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition max-md:min-h-11 max-md:flex-auto max-md:justify-center hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                {{ __('Tambah') }}
            </button>
            <button type="button" id="openCopyMengajar"
                class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition max-md:min-h-11 max-md:flex-auto max-md:justify-center hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-500/30">
                {{ __('Salin dari TA Lain') }}
            </button>
            <button type="button" id="openCopyKelasMengajar"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition max-md:min-h-11 max-md:flex-auto max-md:justify-center hover:bg-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-500/30">
                {{ __('Salin dari Kelas Lain') }}
            </button>
        </div>
    </div>

    @if (!$tahunId)
        <div
            class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 shadow-sm dark:border-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-100">
            {{ __('Pilih tahun ajaran terlebih dahulu di dashboard.') }}
        </div>
    @else
        <div class="mb-4 grid gap-3 max-md:grid-cols-2 md:grid-cols-2 lg:grid-cols-3">
            <form method="GET" action="{{ route('mengajar.index') }}" class="flex min-w-0 flex-col gap-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Tingkat') }}</label>
                <select name="tingkat" onchange="this.form.submit()"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm max-md:h-11 max-md:text-base focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    @foreach ($tingkats as $tingkat)
                        <option value="{{ $tingkat }}" @selected($tingkat == $selectedTingkat)>{{ $tingkat }}</option>
                    @endforeach
                </select>
            </form>
            <form method="GET" action="{{ route('mengajar.index') }}" class="flex min-w-0 flex-col gap-2">
                <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Kelas') }}</label>
                <select name="kelas_id" onchange="this.form.submit()"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm max-md:h-11 max-md:text-base focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    @foreach ($kelasList->where('tingkat', $selectedTingkat) as $kelas)
                        <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm max-md:overflow-clip dark:border-gray-700 dark:bg-gray-800">
            @if ($mataPelajarans->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Belum ada mata pelajaran yang terdaftar.') }}
                </div>
            @else
                <form action="{{ route('mengajar.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                    {{-- Di HP setiap baris tampil sebagai kartu bertumpuk; mulai md kembali menjadi tabel --}}
                    <div class="px-6 pb-6 overflow-x-auto max-md:overflow-visible max-md:p-0">
                        <table id="mengajar-table"
                            class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 max-md:block dark:divide-gray-700 dark:text-gray-200">
                            <thead
                                class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 max-md:hidden dark:bg-gray-900/40 dark:text-gray-400">
                                <tr>
                                    <th class="px-3 py-3">{{ __('Mata Pelajaran') }}</th>
                                    <th class="px-3 py-3">{{ __('Induk') }}</th>
                                    <th class="px-3 py-3">{{ __('JTM') }}</th>
                                    <th class="px-3 py-3">{{ __('Guru Pengajar') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 max-md:block dark:divide-gray-700">
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
                                        <tr class="bg-gray-50 max-md:block dark:bg-gray-800/50">
                                            <td class="px-3 py-3 max-md:block max-md:px-4 max-md:py-2.5" colspan="4">
                                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ __('Pendidikan Agama Islam') }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    
                                    {{-- Group header for Mulok --}}
                                    @if ($mapel->kelompok === 'Mulok' && $currentKelompok !== 'Mulok')
                                        @php $currentKelompok = 'Mulok'; $rowNumber++; @endphp
                                        <tr class="bg-gray-50 max-md:block dark:bg-gray-800/50">
                                            <td class="px-3 py-3 max-md:block max-md:px-4 max-md:py-2.5" colspan="4">
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
                                    <tr
                                        class="max-md:grid max-md:grid-cols-[5.5rem_minmax(0,1fr)] max-md:items-end max-md:gap-x-3 max-md:gap-y-2 max-md:px-4 max-md:py-3">
                                        <td class="px-3 py-3 max-md:col-span-2 max-md:p-0">
                                            <input type="hidden" name="items[{{ $mapel->id }}][mata_pelajaran_id]"
                                                value="{{ $mapel->id }}">
                                            <div class="{{ $isChildSubject ? 'italic pl-4 max-md:pl-0' : '' }} font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $mapel->nama_mapel }}</div>
                                            @if ($mapel->kode)
                                                <div class="text-xs text-gray-500 {{ $isChildSubject ? 'pl-4 max-md:pl-0' : '' }}">{{ $mapel->kode }}</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 max-md:hidden">{{ $isChildSubject ? $mapel->kelompok : '—' }}</td>
                                        <td class="px-3 py-3 max-md:p-0">
                                            <label for="mengajar-jtm-{{ $mapel->id }}"
                                                class="mb-1 block text-xs font-semibold text-gray-500 md:sr-only dark:text-gray-400">{{ __('JTM') }}</label>
                                            <input type="number" id="mengajar-jtm-{{ $mapel->id }}" name="items[{{ $mapel->id }}][jtm]" min="0"
                                                inputmode="numeric" value="{{ $jtmValue }}"
                                                class="w-20 rounded-md border border-gray-200 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 max-md:h-11 max-md:w-full max-md:rounded-lg max-md:text-center max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                        </td>
                                        <td class="px-3 py-3 max-md:min-w-0 max-md:p-0">
                                            <label for="mengajar-guru-{{ $mapel->id }}"
                                                class="mb-1 block text-xs font-semibold text-gray-500 md:sr-only dark:text-gray-400">{{ __('Guru Pengajar') }}</label>
                                            <select id="mengajar-guru-{{ $mapel->id }}" name="items[{{ $mapel->id }}][guru_id]"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
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
                    <div class="border-t border-gray-100 px-6 py-4 text-right max-md:px-4 max-md:py-3 max-md:sticky max-md:bottom-0 max-md:z-20 max-md:bg-white/95 max-md:shadow-[0_-4px_12px_rgba(0,0,0,0.08)] max-md:backdrop-blur max-md:dark:bg-gray-800/95 in-[.mode-aplikasi]:max-md:bottom-[calc(4.75rem+env(safe-area-inset-bottom))] dark:border-gray-700">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 max-md:min-h-11 max-md:w-full max-md:justify-center">
                            {{ __('Simpan Jadwal Mengajar') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    {{-- Di HP modal tampil sebagai lembar bawah selebar layar dan bisa digulir --}}
    <div id="mengajarModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4 max-md:items-end max-md:px-0">
        <div id="addMengajarModal"
            class="modal-card hidden w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:rounded-b-none max-md:pb-[env(safe-area-inset-bottom)] dark:border-gray-700 dark:bg-gray-900">
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
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Mata Pelajaran') }}</label>
                        <select name="items[0][mata_pelajaran_id]" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            @foreach ($mataPelajarans as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Guru') }}</label>
                        <select name="items[0][guru_id]"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">- {{ __('Pilih Guru') }} -</option>
                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('JTM') }}</label>
                        <input type="number" name="items[0][jtm]" min="0"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
                <div
                    class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button" data-close-modal
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 max-md:min-h-11 max-md:flex-1 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 max-md:min-h-11 max-md:flex-1 max-md:justify-center focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                        {{ __('Simpan') }}
                    </button>
                </div>
            </form>
        </div>

        <div id="copyMengajarModal"
            class="modal-card hidden w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:rounded-b-none max-md:pb-[env(safe-area-inset-bottom)] dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Salin Mengajar') }}</h3>
            </div>
            <form action="{{ route('mengajar.copy') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <p class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                    {{ __('Jadwal mengajar kelas yang sama pada tahun ajaran sumber akan disalin ke kelas yang dipilih, beserta guru dan JTM-nya. Kelas dicocokkan berdasarkan nama (contoh: 1A dengan 1A), dan semester sumber boleh berbeda dari semester yang sedang aktif — misalnya menyalin jadwal Ganjil ke Genap.') }}
                </p>
                <div class="space-y-2">
                    <label
                        class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Sumber Tahun Ajaran') }}</label>
                    <select name="source_tahun_ajaran_id" required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
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
                        class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Semester Sumber') }}</label>
                    <select name="source_semester" required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="Ganjil" @selected($semester === 'Ganjil')>{{ __('Ganjil') }}</option>
                        <option value="Genap" @selected($semester !== 'Ganjil')>{{ __('Genap') }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kelas') }}</label>
                    @if ($kelasList->isEmpty())
                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">
                            {{ __('Tahun ajaran aktif belum punya kelas. Tambahkan kelas atau jalankan sync siswa terlebih dahulu.') }}
                        </p>
                    @else
                        <select name="kelas_id" required
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($kelas->id == $selectedKelasId)>{{ $kelas->nama }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div
                    class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button" data-close-modal
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 max-md:min-h-11 max-md:flex-1 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button>
                    <button type="submit" @disabled($kelasList->isEmpty())
                        class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600 max-md:min-h-11 max-md:flex-1 max-md:justify-center focus:outline-none focus:ring-4 focus:ring-orange-500/30 disabled:cursor-not-allowed disabled:bg-gray-300 dark:disabled:bg-gray-700">
                        {{ __('Salin') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Salin dari Kelas Lain --}}
    <div id="copyKelasModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4 max-md:items-end max-md:px-0">
        <div id="copyKelasModal"
            class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:rounded-b-none max-md:pb-[env(safe-area-inset-bottom)] dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Salin dari Kelas Lain') }}</h3>
            </div>
            <form action="{{ route('mengajar.copy-kelas') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <input type="hidden" name="target_kelas_id" value="{{ $selectedKelasId }}">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kelas Tujuan') }}</label>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $selectedKelas?->nama ?? '-' }}
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Salin dari Kelas') }}</label>
                    @if ($kelasList->count() < 2)
                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">
                            {{ __('Tahun ajaran aktif baru punya satu kelas, jadi belum ada kelas lain yang bisa dijadikan sumber.') }}
                        </p>
                    @else
                        <select name="source_kelas_id" required
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 max-md:h-11 max-md:text-base dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">- {{ __('Pilih Kelas Sumber') }} -</option>
                            @foreach ($kelasList as $kelas)
                                @continue($kelas->id == $selectedKelasId)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Data mengajar (guru & JTM) dari kelas sumber akan disalin ke kelas tujuan pada semester yang sedang aktif. Data yang sudah ada akan di-update.') }}
                </p>
                <div class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button" data-close-copy-kelas
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 max-md:min-h-11 max-md:flex-1 dark:border-gray-700 dark:text-gray-300">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 max-md:min-h-11 max-md:flex-1 max-md:justify-center focus:outline-none focus:ring-4 focus:ring-emerald-500/30">
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
