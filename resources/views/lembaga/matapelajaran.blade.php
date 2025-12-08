<x-layouts.app>

    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Mata Pelajaran') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Kelola daftar mata pelajaran dengan mudah, lengkap dengan pencarian cepat dan pengurutan pintar.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" id="openCreateModal"
                class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah Mata Pelajaran') }}
            </button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[360px,1fr]">
        <div class="space-y-6">
            <div
                class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                    <div
                        class="absolute -top-14 -right-16 h-36 w-36 rounded-full bg-violet-100/70 dark:bg-violet-900/30">
                    </div>
                    <div class="absolute -bottom-16 -left-10 h-40 w-40 rounded-full bg-blue-100/70 dark:bg-blue-900/30">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900/40">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Daftar Mata Pelajaran') }}</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Gunakan pencarian live untuk menemukan mata pelajaran secara cepat.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 pb-6">
                    <div class="overflow-x-auto">
                        <table id="subjects-table"
                            class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                            <thead
                                class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
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
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($mataPelajaran as $mapel)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $mapel->kode }}</td>
                                        <td class="px-4 py-3">{{ $mapel->nama_mapel }}</td>
                                        <td class="px-4 py-3">{{ $mapel->kelompok ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $mapel->jurusan ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">{{ $mapel->jumlah_jam ?? '0' }}</td>
                                        <td class="px-4 py-3 text-center">{{ $mapel->urutan ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button"
                                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-200 dark:hover:bg-indigo-900/60"
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
                                                <form action="{{ route('mata-pelajaran.destroy', $mapel) }}"
                                                    method="POST" class="inline"
                                                    onsubmit="return confirm('{{ __('Hapus mata pelajaran ini?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-3h4l1 3" />
                                                        </svg>
                                                        {{ __('Hapus') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
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

    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="createModal"
            class="modal-card hidden w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Tambah Mata Pelajaran') }}
                </h3>
            </div>
            <form action="{{ route('mata-pelajaran.store') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="create_kode"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kode') }}</label>
                        <input id="create_kode" name="kode" type="text" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="create_nama_mapel"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Mata Pelajaran') }}</label>
                        <input id="create_nama_mapel" name="nama_mapel" type="text" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="create_jumlah_jam"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Jam / Minggu') }}</label>
                        <input id="create_jumlah_jam" name="jumlah_jam" type="number" min="0"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="create_kelompok"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kelompok') }}</label>
                        <input id="create_kelompok" name="kelompok" type="text"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="create_jurusan"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Jurusan') }}</label>
                        <input id="create_jurusan" name="jurusan" type="text"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
                <div>
                    <label for="create_urutan"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Urutan Tampil') }}</label>
                    <input id="create_urutan" name="urutan" type="text"
                        class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300"
                        data-close-modal>{{ __('Batal') }}</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
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
            class="modal-card hidden w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit Mata Pelajaran') }}
                </h3>
            </div>
            <form id="editMapelForm" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_kode"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kode') }}</label>
                        <input id="edit_kode" name="kode" type="text" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="edit_nama_mapel"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Mata Pelajaran') }}</label>
                        <input id="edit_nama_mapel" name="nama_mapel" type="text" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="edit_jumlah_jam"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Jam / Minggu') }}</label>
                        <input id="edit_jumlah_jam" name="jumlah_jam" type="number" min="0"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="edit_kelompok"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kelompok') }}</label>
                        <input id="edit_kelompok" name="kelompok" type="text"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="edit_jurusan"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Jurusan') }}</label>
                        <input id="edit_jurusan" name="jurusan" type="text"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
                <div>
                    <label for="edit_urutan"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Urutan Tampil') }}</label>
                    <input id="edit_urutan" name="urutan" type="text"
                        class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                </div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700">
                    <button type="button"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300"
                        data-close-modal>{{ __('Batal') }}</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-500/30">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QMc7qk5o5mQ=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        (function() {
            const modalOverlay = document.getElementById('modalOverlay');
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            const openCreateModalButton = document.getElementById('openCreateModal');
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
