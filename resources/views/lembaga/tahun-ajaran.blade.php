<x-layouts.app>
    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Tahun Ajaran') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Kelola tahun ajaran aktif, termasuk pengaturan semester dan status aktif yang digunakan sistem.') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" id="openCreateModal"
                class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah Tahun Ajaran') }}
            </button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[360px,1fr]">
        <div class="space-y-6">



        </div>

        <div>
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900/40">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('Daftar Tahun Ajaran') }}</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Sunting atau aktifkan tahun ajaran dari tabel berikut.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 pb-6">
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                            <thead
                                class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Nama') }}</th>
                                    <th class="px-4 py-3">{{ __('Periode') }}</th>
                                    <th class="px-4 py-3">{{ __('Semester') }}</th>
                                    <th class="px-4 py-3">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-center">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($tahunAjaran as $tahun)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $tahun->nama }}
                                        </td>
                                        <td class="px-4 py-3">{{ $tahun->tahun_mulai }} / {{ $tahun->tahun_selesai }}
                                        </td>
                                        <td class="px-4 py-3">{{ $tahun->semester ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($tahun->is_active)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    {{ __('Aktif') }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                                                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                                    {{ __('Nonaktif') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button"
                                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-200 dark:hover:bg-indigo-900/60"
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
                                                <form action="{{ route('tahun-ajaran.destroy', $tahun) }}"
                                                    method="POST" class="inline"
                                                    onsubmit="return confirm('{{ __('Hapus tahun ajaran ini?') }}');">
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
                                        <td colspan="5"
                                            class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
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

    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="createModal"
            class="modal-card hidden w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Tambah Tahun Ajaran') }}
                </h3>
            </div>
            <form action="{{ route('tahun-ajaran.store') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="create_nama"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama (contoh: 2024/2025)') }}</label>
                        <input id="create_nama" name="nama" type="text" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="create_mulai"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Tahun Mulai') }}</label>
                            <input id="create_mulai" name="tahun_mulai" type="number" required
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        </div>
                        <div>
                            <label for="create_selesai"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Tahun Selesai') }}</label>
                            <input id="create_selesai" name="tahun_selesai" type="number" required
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="create_semester"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Semester (opsional)') }}</label>
                        <input id="create_semester" name="semester" type="text"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input id="create_active" name="is_active" type="checkbox" value="1"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="create_active"
                            class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Tandai sebagai aktif') }}</label>
                    </div>
                </div>
                <div>
                    <label for="create_keterangan"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Keterangan') }}</label>
                    <textarea id="create_keterangan" name="keterangan" rows="3"
                        class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
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
            class="modal-card hidden w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit Tahun Ajaran') }}</h3>
            </div>
            <form id="editForm" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_nama"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama') }}</label>
                        <input id="edit_nama" name="nama" type="text" required
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="edit_mulai"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Tahun Mulai') }}</label>
                            <input id="edit_mulai" name="tahun_mulai" type="number" required
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        </div>
                        <div>
                            <label for="edit_selesai"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Tahun Selesai') }}</label>
                            <input id="edit_selesai" name="tahun_selesai" type="number" required
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_semester"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Semester') }}</label>
                        <input id="edit_semester" name="semester" type="text"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input id="edit_active" name="is_active" type="checkbox" value="1"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="edit_active"
                            class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Tandai sebagai aktif') }}</label>
                    </div>
                </div>
                <div>
                    <label for="edit_keterangan"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Keterangan') }}</label>
                    <textarea id="edit_keterangan" name="keterangan" rows="3"
                        class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
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
