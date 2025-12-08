<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Data Ekstrakurikuler</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola nama ekskul dan pembina (guru).</p>
        </div>
        <button type="button" id="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
            <i class="fa-solid fa-plus text-xs"></i> Tambah
        </button>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table
                class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3 w-12">No</th>
                        <th class="px-3 py-3">Nama Ekskul</th>
                        <th class="px-3 py-3">Pembina</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($ekskuls as $index => $ekskul)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $ekskul->nama }}</td>
                            <td class="px-3 py-3">{{ optional($ekskul->guru)->nama ?? 'Belum diatur' }}</td>
                            <td class="px-3 py-3 text-center">
                                <div class="inline-flex gap-2">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-blue-700"
                                        data-action="edit" data-id="{{ $ekskul->id }}" data-nama="{{ $ekskul->nama }}"
                                        data-guru="{{ $ekskul->guru_id }}">
                                        <i class="fa-solid fa-pen text-[11px]"></i> Edit
                                    </button>
                                    <form action="{{ route('ekskul.destroy', $ekskul) }}" method="POST"
                                        onsubmit="return confirm('Hapus ekskul ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-200">
                                            <i class="fa-solid fa-trash text-[11px]"></i> Del
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">Belum ada data
                                ekskul.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="formModal"
            class="modal-card hidden w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tambah Ekskul</h3>
            </div>
            <form id="modalForm" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div class="space-y-1">
                    <label for="nama" class="text-sm font-semibold text-gray-800 dark:text-gray-200">Nama
                        Ekskul</label>
                    <input id="nama" name="nama" type="text" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                </div>
                <div class="space-y-1">
                    <label for="guru_id" class="text-sm font-semibold text-gray-800 dark:text-gray-200">Pembina
                        (Guru)</label>
                    <select id="guru_id" name="guru_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">Belum ditentukan</option>
                        @foreach ($gurus as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" data-close-modal
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 focus:outline-none">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const modalOverlay = document.getElementById('modalOverlay');
            const formModal = document.getElementById('formModal');
            const openCreateModal = document.getElementById('openCreateModal');
            const modalForm = document.getElementById('modalForm');
            const modalTitle = document.getElementById('modalTitle');
            const namaInput = document.getElementById('nama');
            const guruSelect = document.getElementById('guru_id');

            function openModal() {
                modalOverlay.classList.remove('hidden');
                modalOverlay.classList.add('flex');
                formModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                modalOverlay.classList.remove('flex');
                modalOverlay.classList.add('hidden');
                formModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openCreateModal?.addEventListener('click', () => {
                modalForm.action = '{{ route('ekskul.store') }}';
                modalForm.reset();
                modalTitle.textContent = 'Tambah Ekskul';
                const existingMethod = document.getElementById('methodField');
                if (existingMethod) existingMethod.remove();
                openModal();
            });

            document.querySelectorAll('[data-action="edit"]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-id');
                    const nama = button.getAttribute('data-nama');
                    const guru = button.getAttribute('data-guru');

                    modalForm.action = '{{ url('ekskul') }}' + '/' + id;
                    const existingMethod = document.getElementById('methodField');
                    if (existingMethod) {
                        existingMethod.value = 'PUT';
                    } else {
                        modalForm.insertAdjacentHTML('afterbegin',
                            '<input type="hidden" name="_method" value="PUT" id="methodField">');
                    }

                    namaInput.value = nama || '';
                    guruSelect.value = guru || '';
                    modalTitle.textContent = 'Edit Ekskul';
                    openModal();
                });
            });

            modalOverlay?.addEventListener('click', (event) => {
                if (event.target === modalOverlay) closeModal();
            });
            document.querySelectorAll('[data-close-modal]').forEach((btn) => btn.addEventListener('click', closeModal));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });
        })();
    </script>
</x-layouts.app>
