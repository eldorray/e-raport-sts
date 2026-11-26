<x-layouts.app>

    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Mata Pelajaran') }}</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                Kelola daftar mata pelajaran dengan mudah, lengkap dengan pencarian cepat dan pengurutan pintar.
            </p>
        </div>
        <div class="grid gap-2 md:flex md:items-center md:gap-3">
            <button type="button" id="openCreateModal"
                class="tombol tombol-aksen gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah Mata Pelajaran') }}
            </button>
            @if (config('services.data_induk.enabled'))
            <button type="button" id="openSyncModal"
                class="tombol tombol-netral gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ __('Sync API') }}
            </button>
            @endif
        </div>
    </div>

    <div class="grid gap-6">
        <div class="min-w-0">
            {{-- HP: daftar kartu + pencarian cepat; tabel (DataTables) hanya untuk layar md ke atas --}}
            <div class="space-y-3 md:hidden"
                x-data="{
                    q: '',
                    kunci() { return this.q.trim().toLowerCase(); },
                    cocok(teks) { return this.kunci() === '' || teks.includes(this.kunci()); },
                    get kosong() {
                        const k = this.kunci();
                        return k !== '' && ! [...this.$root.querySelectorAll('[data-cari]')].some((el) => el.dataset.cari.includes(k));
                    },
                }">
                @if ($mataPelajaran->isNotEmpty())
                    <div class="relative">
                        <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-zinc-400"></i>
                        <input type="search" x-model="q" placeholder="{{ __('Cari nama atau kode mapel') }}"
                            aria-label="{{ __('Cari mata pelajaran') }}"
                            class="isian min-h-11 w-full py-2 pl-10 pr-3 text-base">
                    </div>
                @endif

                @forelse ($mataPelajaran as $mapel)
                    <article data-cari="{{ mb_strtolower($mapel->nama_mapel.' '.$mapel->kode.' '.$mapel->kelompok.' '.$mapel->jurusan) }}"
                        x-show="cocok($el.dataset.cari)"
                        class="kartu p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="break-words font-semibold text-zinc-900 dark:text-zinc-100">{{ $mapel->nama_mapel }}</p>
                                <p class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ __('Kelompok') }} {{ $mapel->kelompok ?? '—' }}@if ($mapel->jurusan) · {{ $mapel->jurusan }}@endif
                                    · {{ $mapel->jumlah_jam ?? 0 }} {{ __('jam/minggu') }} · {{ __('Urutan') }} {{ $mapel->urutan ?? '—' }}
                                </p>
                            </div>
                            <span
                                class="pil pil-info max-w-[40%] shrink-0 truncate px-2.5 py-1 font-mono">
                                {{ $mapel->kode }}
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-700">
                            <button type="button"
                                class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-indigo-50 px-2 text-sm font-semibold text-indigo-700 transition active:scale-[0.98] dark:bg-indigo-900/40 dark:text-indigo-200"
                                data-action="edit"
                                data-update-url="{{ route('mata-pelajaran.update', $mapel) }}"
                                data-kode="{{ $mapel->kode }}"
                                data-nama="{{ $mapel->nama_mapel }}"
                                data-jam="{{ $mapel->jumlah_jam }}"
                                data-kelompok="{{ $mapel->kelompok }}"
                                data-jurusan="{{ $mapel->jurusan }}"
                                data-urutan="{{ $mapel->urutan }}">
                                <i class="fas fa-pen text-xs"></i> {{ __('Edit') }}
                            </button>
                            @if ($mapel->penilaians_count > 0)
                                <x-hapus-nilai-terkunci :jumlah="$mapel->penilaians_count"
                                    class="inline-flex min-h-11 w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-xl bg-zinc-100 px-2 text-sm font-semibold text-zinc-400 dark:bg-zinc-700 dark:text-zinc-400" />
                            @else
                                <form action="{{ route('mata-pelajaran.destroy', $mapel) }}" method="POST"
                                    onsubmit="return confirm('{{ __('Hapus mata pelajaran ini?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex min-h-11 w-full items-center justify-center gap-1.5 rounded-xl bg-red-50 px-2 text-sm font-semibold text-red-600 transition active:scale-[0.98] dark:bg-red-900/40 dark:text-red-300">
                                        <i class="fas fa-trash text-xs"></i> {{ __('Hapus') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div
                        class="kartu px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Belum ada data mata pelajaran. Tambahkan data baru untuk mulai menyusun kurikulum.') }}
                    </div>
                @endforelse

                <p x-show="kosong" style="display: none"
                    class="rounded-2xl border border-dashed border-zinc-300 px-4 py-6 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                    {{ __('Tidak ada mata pelajaran yang cocok dengan pencarian.') }}
                </p>
            </div>

            <div
                class="kartu hidden overflow-hidden md:block">
                <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-5 dark:border-zinc-700 dark:bg-zinc-900/40">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ __('Daftar Mata Pelajaran') }}</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Gunakan pencarian live untuk menemukan mata pelajaran secara cepat.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 pb-6">
                    <div class="overflow-x-auto">
                        <table id="subjects-table"
                            class="tabel min-w-full text-left text-sm text-zinc-700 dark:text-zinc-200">
                            <thead
                               >
                                <tr>
                                    <th scope="col" class="px-4 py-3">{{ __('Kode') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Nama Mata Pelajaran') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Kelompok') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Jurusan') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center">{{ __('Jam / Minggu') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center">{{ __('Urutan') }}</th>
                                    <th scope="col" class="px-4 py-3 text-center">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mataPelajaran as $mapel)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $mapel->kode }}</td>
                                        <td class="px-4 py-3">{{ $mapel->nama_mapel }}</td>
                                        <td class="px-4 py-3">{{ $mapel->kelompok ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $mapel->jurusan ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">{{ $mapel->jumlah_jam ?? '0' }}</td>
                                        <td class="px-4 py-3 text-center">{{ $mapel->urutan ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button"
                                                    class="pil-aksi"
                                                    data-action="edit"
                                                    data-update-url="{{ route('mata-pelajaran.update', $mapel) }}"
                                                    data-kode="{{ $mapel->kode }}"
                                                    data-nama="{{ $mapel->nama_mapel }}"
                                                    data-jam="{{ $mapel->jumlah_jam }}"
                                                    data-kelompok="{{ $mapel->kelompok }}"
                                                    data-jurusan="{{ $mapel->jurusan }}"
                                                    data-urutan="{{ $mapel->urutan }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L7.5 20.5 3 21l.5-4.5 13.232-13.232z" />
                                                    </svg>
                                                    {{ __('Edit') }}
                                                </button>
                                                @if ($mapel->penilaians_count > 0)
                                                    <x-hapus-nilai-terkunci :jumlah="$mapel->penilaians_count" />
                                                @else
                                                    <form action="{{ route('mata-pelajaran.destroy', $mapel) }}"
                                                        method="POST" class="inline"
                                                        onsubmit="return confirm('{{ __('Hapus mata pelajaran ini?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="pil-aksi pil-bahaya">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="1.8"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-3h4l1 3" />
                                                            </svg>
                                                            {{ __('Hapus') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-4 py-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ __('Belum ada data mata pelajaran. Tambahkan data baru untuk mulai menyusun kurikulum.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0">
        <div id="createModal"
            class="permukaan modal-card hidden w-full max-w-xl overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:top-0 max-md:z-10 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Tambah Mata Pelajaran') }}
                </h3>
            </div>
            <form action="{{ route('mata-pelajaran.store') }}" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:py-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="create_kode"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Kode') }}</label>
                        <input id="create_kode" name="kode" type="text" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                    <div>
                        <label for="create_nama_mapel"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Nama Mata Pelajaran') }}</label>
                        <input id="create_nama_mapel" name="nama_mapel" type="text" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="create_jumlah_jam"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Jam / Minggu') }}</label>
                        <input id="create_jumlah_jam" name="jumlah_jam" type="number" min="0"
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                    <div>
                        <label for="create_kelompok"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Kelompok') }}</label>
                        <input id="create_kelompok" name="kelompok" type="text"
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                    <div>
                        <label for="create_jurusan"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Jurusan') }}</label>
                        <input id="create_jurusan" name="jurusan" type="text"
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                </div>
                <div>
                    <label for="create_urutan"
                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Urutan Tampil') }}</label>
                    <input id="create_urutan" name="urutan" type="text"
                        class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 bg-white pt-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:bottom-0 max-md:z-10 max-md:-mx-4 max-md:-mb-4 max-md:grid max-md:grid-cols-2 max-md:px-4 max-md:pb-[max(1rem,env(safe-area-inset-bottom))]">
                    <button type="button"
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11"
                        data-close-modal>{{ __('Batal') }}</button>
                    <button type="submit"
                        class="tombol tombol-aksen gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Simpan Data') }}
                    </button>
                </div>
            </form>
        </div>

        <div id="editModal"
            class="permukaan modal-card hidden w-full max-w-xl overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:top-0 max-md:z-10 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Edit Mata Pelajaran') }}
                </h3>
            </div>
            <form id="editMapelForm" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:py-4">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_kode"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Kode') }}</label>
                        <input id="edit_kode" name="kode" type="text" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                    <div>
                        <label for="edit_nama_mapel"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Nama Mata Pelajaran') }}</label>
                        <input id="edit_nama_mapel" name="nama_mapel" type="text" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="edit_jumlah_jam"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Jam / Minggu') }}</label>
                        <input id="edit_jumlah_jam" name="jumlah_jam" type="number" min="0"
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                    <div>
                        <label for="edit_kelompok"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Kelompok') }}</label>
                        <input id="edit_kelompok" name="kelompok" type="text"
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                    <div>
                        <label for="edit_jurusan"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Jurusan') }}</label>
                        <input id="edit_jurusan" name="jurusan" type="text"
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                    </div>
                </div>
                <div>
                    <label for="edit_urutan"
                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Urutan Tampil') }}</label>
                    <input id="edit_urutan" name="urutan" type="text"
                        class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                </div>
                <div class="flex items-center justify-between border-t border-zinc-100 bg-white pt-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:bottom-0 max-md:z-10 max-md:-mx-4 max-md:-mb-4 max-md:grid max-md:grid-cols-2 max-md:gap-3 max-md:px-4 max-md:pb-[max(1rem,env(safe-area-inset-bottom))]">
                    <button type="button"
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11"
                        data-close-modal>{{ __('Batal') }}</button>
                    <button type="submit"
                        class="tombol tombol-netral gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Perbarui Data') }}
                    </button>
                </div>
            </form>
        </div>

        @if (config('services.data_induk.enabled'))
        <div id="syncModal"
            class="permukaan modal-card hidden w-full max-w-lg overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:top-0 max-md:z-10 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Sync Data Mata Pelajaran dari API') }}</h3>
            </div>
            <form action="{{ route('mata-pelajaran.sync') }}" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:py-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">{{ __('Pilih Sumber Data') }}</label>
                    <select name="source" required class="isian w-full px-4 py-2.5 max-md:min-h-11">
                        <option value="">-- Pilih Sumber --</option>
                        <option value="mapel-mi">Mapel MI</option>
                        <option value="mapel-smp">Mapel SMP</option>
                    </select>
                </div>
                <div class="bg-accent/10 dark:bg-blue-900/20 rounded-lg p-4 text-sm text-accent-strong dark:text-blue-300">
                    <p class="font-medium mb-2">{{ __('Informasi:') }}</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Data akan diambil dari: <code class="bg-accent/10 dark:bg-blue-800 px-1 rounded max-md:break-all">{{ config('services.data_induk.base_url') }}/api/[source]/all</code></li>
                        <li>Mapel yang sudah ada (berdasarkan kode) akan diperbarui</li>
                        <li>Mapel baru akan ditambahkan</li>
                    </ul>
                </div>
                <div class="flex justify-end gap-3 max-md:grid max-md:grid-cols-2">
                    <button type="button" data-close-modal
                        class="rounded-lg bg-zinc-100 px-4 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-200 max-md:min-h-11">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="tombol tombol-netral px-4 py-2.5 text-sm font-semibold max-md:min-h-11">{{ __('Sync Sekarang') }}</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- jQuery + DataTables dimuat dari layout (components.layouts.app.datatables) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script>
        (function() {
            const modalOverlay = document.getElementById('modalOverlay');
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            const syncModal = document.getElementById('syncModal');
            const openCreateModalButton = document.getElementById('openCreateModal');
            const openSyncModalButton = document.getElementById('openSyncModal');
            const editForm = document.getElementById('editMapelForm');

            function openModal(modal) {
                modalOverlay.classList.remove('hidden');
                modalOverlay.classList.add('flex');
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                modalOverlay.classList.remove('flex');
                modalOverlay.classList.add('hidden');
                createModal.classList.add('hidden');
                editModal.classList.add('hidden');
                syncModal?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            if (openCreateModalButton) {
                openCreateModalButton.addEventListener('click', () => {
                    openModal(createModal);
                });
            }

            if (openSyncModalButton) {
                openSyncModalButton.addEventListener('click', () => {
                    openModal(syncModal);
                });
            }

            modalOverlay?.addEventListener('click', (event) => {
                if (event.target === modalOverlay) {
                    closeModal();
                }
            });

            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => closeModal());
            });

            document.querySelectorAll('[data-action="edit"]').forEach((button) => {
                button.addEventListener('click', () => {
                    const updateUrl = button.getAttribute('data-update-url');
                    editForm.setAttribute('action', updateUrl);

                    document.getElementById('edit_kode').value = button.getAttribute('data-kode') || '';
                    document.getElementById('edit_nama_mapel').value = button.getAttribute(
                        'data-nama') || '';
                    document.getElementById('edit_jumlah_jam').value = button.getAttribute(
                        'data-jam') || '';
                    document.getElementById('edit_kelompok').value = button.getAttribute(
                        'data-kelompok') || '';
                    document.getElementById('edit_jurusan').value = button.getAttribute(
                        'data-jurusan') || '';
                    document.getElementById('edit_urutan').value = button.getAttribute('data-urutan') ||
                        '';

                    openModal(editModal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        })();
    </script>
</x-layouts.app>
