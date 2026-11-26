<x-layouts.app>
    <div class="mb-8 flex flex-col gap-3 max-md:mb-5 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Tahun Ajaran') }}</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Kelola tahun ajaran aktif, termasuk pengaturan semester dan status aktif yang digunakan sistem.') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 max-md:grid max-md:grid-cols-1 max-md:gap-2">
            <a href="{{ route('tahun-ajaran-baru.create') }}"
                title="{{ __('Pindahkan kelas, siswa, dan jadwal mengajar ke semester/tahun ajaran berikutnya sekaligus') }}"
                class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 max-md:min-h-11 max-md:justify-center dark:border-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200 dark:hover:bg-emerald-900/60">
                <i class="fas fa-forward"></i>
                {{ __('Wizard Tahun Ajaran Baru') }}
            </a>
            <button type="button" id="openCreateModal"
                class="tombol tombol-aksen gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah Tahun Ajaran') }}
            </button>
        </div>
    </div>

    <div class="grid gap-6">
        <div class="max-md:min-w-0">
            <div
                class="kartu overflow-hidden">
                <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-5 max-md:px-4 max-md:py-4 dark:border-zinc-700 dark:bg-zinc-900/40">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ __('Daftar Tahun Ajaran') }}</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Sunting atau aktifkan tahun ajaran dari tabel berikut.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- HP: kartu per tahun ajaran (aksi sama dengan tabel, area sentuh minimal 44px) --}}
                <ul class="divide-y divide-zinc-200 md:hidden dark:divide-zinc-700">
                    @forelse ($tahunAjaran as $tahun)
                        @php
                            $dataTahunAjaran = $tahun->siswas_count
                                + $tahun->kelas_count
                                + $tahun->penilaians_count
                                + $tahun->rapor_metadatas_count
                                + $tahun->tahfidz_penilaians_count
                                + $tahun->mengajars_count;
                        @endphp
                        <li class="space-y-3 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $tahun->nama }}
                                        <span class="font-medium text-zinc-600 dark:text-zinc-400">• {{ $tahun->semester ?? '—' }}</span>
                                    </p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ __('Periode') }} {{ $tahun->tahun_mulai }} / {{ $tahun->tahun_selesai }}
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $tahun->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                    {{ $tahun->is_active ? __('Aktif') : __('Nonaktif') }}
                                </span>
                            </div>

                            <form action="{{ route('tahun-ajaran.toggle-active', $tahun) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="tombol tombol-netral flex min-h-11 w-full justify-between gap-3 px-3 text-left text-sm font-medium">
                                    <span>{{ $tahun->is_active ? __('Nonaktifkan tahun ajaran') : __('Aktifkan tahun ajaran') }}</span>
                                    <span aria-hidden="true"
                                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full {{ $tahun->is_active ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-zinc-600' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow {{ $tahun->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </span>
                                </button>
                            </form>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-indigo-50 px-3 text-sm font-semibold text-indigo-600 transition active:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-200 dark:active:bg-indigo-900/60"
                                    data-action="edit"
                                    data-update-url="{{ route('tahun-ajaran.update', $tahun) }}"
                                    data-nama="{{ $tahun->nama }}"
                                    data-mulai="{{ $tahun->tahun_mulai }}"
                                    data-selesai="{{ $tahun->tahun_selesai }}"
                                    data-semester="{{ $tahun->semester }}"
                                    data-keterangan="{{ $tahun->keterangan }}"
                                    data-active="{{ $tahun->is_active ? '1' : '0' }}">
                                    <i class="fas fa-pen text-xs" aria-hidden="true"></i>
                                    {{ __('Edit') }}
                                </button>
                                @if ($dataTahunAjaran > 0)
                                    <x-hapus-nilai-terkunci :jumlah="$dataTahunAjaran"
                                        class="inline-flex min-h-11 cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-zinc-100 px-3 text-sm font-semibold text-zinc-400 dark:bg-zinc-800 dark:text-zinc-600" />
                                @else
                                    <form action="{{ route('tahun-ajaran.destroy', $tahun) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Hapus tahun ajaran ini?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-red-50 px-3 text-sm font-semibold text-red-600 transition active:bg-red-100 dark:bg-red-900/40 dark:text-red-300 dark:active:bg-red-900/60">
                                            <i class="fas fa-trash text-xs" aria-hidden="true"></i>
                                            {{ __('Hapus') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                            @if ($dataTahunAjaran > 0)
                                {{-- Pengganti tooltip tombol Hapus terkunci (tidak ada hover di HP) --}}
                                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                    <i class="fas fa-lock mr-1 text-[10px]" aria-hidden="true"></i>
                                    {{ __('Tidak bisa dihapus: masih terhubung ke :jumlah nilai. Hapus nilai tersebut lebih dulu.', ['jumlah' => number_format($dataTahunAjaran, 0, ',', '.')]) }}
                                </p>
                            @endif
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('Belum ada data tahun ajaran. Tambahkan data baru untuk memulai.') }}
                        </li>
                    @endforelse
                </ul>

                <div class="px-6 pb-6 max-md:hidden">
                    <div class="overflow-x-auto">
                        <table
                            class="tabel min-w-full text-left text-sm text-zinc-700 dark:text-zinc-200">
                            <thead
                               >
                                <tr>
                                    <th class="px-4 py-3">{{ __('Nama') }}</th>
                                    <th class="px-4 py-3">{{ __('Periode') }}</th>
                                    <th class="px-4 py-3">{{ __('Semester') }}</th>
                                    <th class="px-4 py-3">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-center">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tahunAjaran as $tahun)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $tahun->nama }}
                                        </td>
                                        <td class="px-4 py-3">{{ $tahun->tahun_mulai }} / {{ $tahun->tahun_selesai }}
                                        </td>
                                        <td class="px-4 py-3">{{ $tahun->semester ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <form action="{{ route('tahun-ajaran.toggle-active', $tahun) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $tahun->is_active ? 'bg-emerald-500 focus:ring-emerald-500' : 'bg-zinc-300 dark:bg-zinc-600 focus:ring-zinc-400' }}"
                                                    title="{{ $tahun->is_active ? __('Klik untuk menonaktifkan') : __('Klik untuk mengaktifkan') }}">
                                                    <span class="sr-only">{{ __('Toggle status') }}</span>
                                                    <span
                                                        class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $tahun->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                                </button>
                                            </form>
                                            <span class="ml-2 text-xs font-medium {{ $tahun->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                                                {{ $tahun->is_active ? __('Aktif') : __('Nonaktif') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button"
                                                    class="pil-aksi"
                                                    data-action="edit"
                                                    data-update-url="{{ route('tahun-ajaran.update', $tahun) }}"
                                                    data-nama="{{ $tahun->nama }}"
                                                    data-mulai="{{ $tahun->tahun_mulai }}"
                                                    data-selesai="{{ $tahun->tahun_selesai }}"
                                                    data-semester="{{ $tahun->semester }}"
                                                    data-keterangan="{{ $tahun->keterangan }}"
                                                    data-active="{{ $tahun->is_active ? '1' : '0' }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L7.5 20.5 3 21l.5-4.5 13.232-13.232z" />
                                                    </svg>
                                                    {{ __('Edit') }}
                                                </button>
                                                @php
                                                    $dataTahunAjaran = $tahun->siswas_count
                                                        + $tahun->kelas_count
                                                        + $tahun->penilaians_count
                                                        + $tahun->rapor_metadatas_count
                                                        + $tahun->tahfidz_penilaians_count
                                                        + $tahun->mengajars_count;
                                                @endphp
                                                @if ($dataTahunAjaran > 0)
                                                    <x-hapus-nilai-terkunci :jumlah="$dataTahunAjaran" />
                                                @else
                                                    <form action="{{ route('tahun-ajaran.destroy', $tahun) }}"
                                                        method="POST" class="inline"
                                                        onsubmit="return confirm('{{ __('Hapus tahun ajaran ini?') }}');">
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
                                        <td colspan="5"
                                            class="px-4 py-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ __('Belum ada data tahun ajaran. Tambahkan data baru untuk memulai.') }}
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

    {{-- Di HP modal tampil sebagai lembar bawah selebar layar (di atas menu bawah aplikasi). --}}
    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0">
        <div id="createModal"
            class="permukaan modal-card hidden w-full max-w-2xl overflow-hidden max-md:max-h-[92dvh] max-md:max-w-none max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="px-6 pb-1 pt-6 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Tambah Tahun Ajaran') }}
                </h3>
            </div>
            <form action="{{ route('tahun-ajaran.store') }}" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:pb-[calc(1rem+env(safe-area-inset-bottom))] max-md:pt-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="create_nama"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Nama (contoh: 2024/2025)') }}</label>
                        <input id="create_nama" name="nama" type="text" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="create_mulai"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Tahun Mulai') }}</label>
                            <input id="create_mulai" name="tahun_mulai" type="number" required
                                class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                        </div>
                        <div>
                            <label for="create_selesai"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Tahun Selesai') }}</label>
                            <input id="create_selesai" name="tahun_selesai" type="number" required
                                class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="create_semester"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Semester') }} <span class="text-red-500">*</span></label>
                        <select id="create_semester" name="semester" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                            <option value="">{{ __('-- Pilih Semester --') }}</option>
                            <option value="Ganjil">{{ __('Ganjil') }}</option>
                            <option value="Genap">{{ __('Genap') }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3 pt-6 max-md:min-h-11 max-md:pt-0">
                        <input type="hidden" name="is_active" value="0">
                        <input id="create_active" name="is_active" type="checkbox" value="1"
                            class="accent-accent h-4 w-4 max-md:h-6 max-md:w-6">
                        <label for="create_active"
                            class="text-sm font-semibold text-zinc-800 max-md:flex max-md:min-h-11 max-md:flex-1 max-md:items-center dark:text-zinc-200">{{ __('Tandai sebagai aktif') }}</label>
                    </div>
                </div>
                <div>
                    <label for="create_keterangan"
                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Keterangan') }}</label>
                    <textarea id="create_keterangan" name="keterangan" rows="3"
                        class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:text-base"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-zinc-100 pt-4 max-md:grid max-md:grid-cols-2 dark:border-zinc-700">
                    <button type="button"
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11"
                        data-close-modal>{{ __('Batal') }}</button>
                    <button type="submit"
                        class="tombol tombol-aksen gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:px-3">
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
            class="permukaan modal-card hidden w-full max-w-2xl overflow-hidden max-md:max-h-[92dvh] max-md:max-w-none max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="px-6 pb-1 pt-6 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Edit Tahun Ajaran') }}</h3>
            </div>
            <form id="editForm" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:pb-[calc(1rem+env(safe-area-inset-bottom))] max-md:pt-4">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_nama"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Nama') }}</label>
                        <input id="edit_nama" name="nama" type="text" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="edit_mulai"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Tahun Mulai') }}</label>
                            <input id="edit_mulai" name="tahun_mulai" type="number" required
                                class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                        </div>
                        <div>
                            <label for="edit_selesai"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Tahun Selesai') }}</label>
                            <input id="edit_selesai" name="tahun_selesai" type="number" required
                                class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_semester"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Semester') }} <span class="text-red-500">*</span></label>
                        <select id="edit_semester" name="semester" required
                            class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:h-11 max-md:py-0 max-md:text-base">
                            <option value="">{{ __('-- Pilih Semester --') }}</option>
                            <option value="Ganjil">{{ __('Ganjil') }}</option>
                            <option value="Genap">{{ __('Genap') }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3 pt-6 max-md:min-h-11 max-md:pt-0">
                        <input type="hidden" name="is_active" value="0">
                        <input id="edit_active" name="is_active" type="checkbox" value="1"
                            class="accent-accent h-4 w-4 max-md:h-6 max-md:w-6">
                        <label for="edit_active"
                            class="text-sm font-semibold text-zinc-800 max-md:flex max-md:min-h-11 max-md:flex-1 max-md:items-center dark:text-zinc-200">{{ __('Tandai sebagai aktif') }}</label>
                    </div>
                </div>
                <div>
                    <label for="edit_keterangan"
                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Keterangan') }}</label>
                    <textarea id="edit_keterangan" name="keterangan" rows="3"
                        class="isian mt-1 w-full px-4 py-2.5 text-sm max-md:text-base"></textarea>
                </div>
                <div class="flex items-center justify-between border-t border-zinc-100 pt-4 max-md:grid max-md:grid-cols-2 max-md:gap-3 dark:border-zinc-700">
                    <button type="button"
                        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11"
                        data-close-modal>{{ __('Batal') }}</button>
                    <button type="submit"
                        class="tombol tombol-netral gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:px-3">
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
    </div>

    <script>
        (function() {
            const modalOverlay = document.getElementById('modalOverlay');
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            const openCreateModalButton = document.getElementById('openCreateModal');
            const editForm = document.getElementById('editForm');

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
                document.body.classList.remove('overflow-hidden');
            }

            if (openCreateModalButton) {
                openCreateModalButton.addEventListener('click', () => {
                    openModal(createModal);
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

                    document.getElementById('edit_nama').value = button.getAttribute('data-nama') || '';
                    document.getElementById('edit_mulai').value = button.getAttribute('data-mulai') ||
                        '';
                    document.getElementById('edit_selesai').value = button.getAttribute(
                        'data-selesai') || '';
                    document.getElementById('edit_semester').value = button.getAttribute(
                        'data-semester') || '';
                    document.getElementById('edit_keterangan').value = button.getAttribute(
                        'data-keterangan') || '';

                    const isActive = button.getAttribute('data-active') === '1';
                    const activeCheckbox = document.getElementById('edit_active');
                    activeCheckbox.checked = isActive;

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
