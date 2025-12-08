<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Data Kelas') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Kelola daftar kelas dan wali kelas.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="openCreateKelas"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah') }}
            </button>
        </div>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="px-6 pb-6 overflow-x-auto">
            <table id="kelas-table"
                class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">{{ __('Nama Kelas') }}</th>
                        <th class="px-3 py-3">{{ __('Jumlah Siswa') }}</th>
                        <th class="px-3 py-3">{{ __('Wali Kelas') }}</th>
                        <th class="px-3 py-3">{{ __('Tingkat') }}</th>
                        <th class="px-3 py-3">{{ __('Jurusan') }}</th>
                        <th class="px-3 py-3">{{ __('Jenis') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($kelasList as $index => $kelas)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3 font-semibold text-gray-900 dark:text-gray-100">{{ $kelas->nama }}
                            </td>
                            <td class="px-3 py-3">{{ $kelas->siswas_count }}</td>
                            <td class="px-3 py-3">{{ optional($kelas->guru)->nama ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $kelas->tingkat }}</td>
                            <td class="px-3 py-3">{{ $kelas->jurusan ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $kelas->jenis ?? '—' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"
                                        data-action="edit-kelas" data-update-url="{{ route('kelas.update', $kelas) }}"
                                        data-nama="{{ $kelas->nama }}" data-tingkat="{{ $kelas->tingkat }}"
                                        data-jurusan="{{ $kelas->jurusan }}" data-jenis="{{ $kelas->jenis }}"
                                        data-guru-id="{{ $kelas->guru_id }}">
                                        {{ __('Edit') }}
                                    </button>
                                    <form action="{{ route('kelas.destroy', $kelas) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Hapus kelas ini?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
                                            {{ __('Hapus') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($kelasList->isEmpty())
            <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('Belum ada data kelas.') }}
            </div>
        @endif
    </div>

    <div id="kelasModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="createKelasModal"
            class="modal-card hidden w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Tambah Kelas') }}</h3>
            </div>
            <form action="{{ route('kelas.store') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @include('kelas.partials.form', ['mode' => 'create'])
            </form>
        </div>

        <div id="editKelasModal"
            class="modal-card hidden w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit Kelas') }}</h3>
            </div>
            <form id="editKelasForm" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')
                @include('kelas.partials.form', ['mode' => 'edit'])
            </form>
        </div>
    </div>

    <script>
        (function() {
            const modalOverlay = document.getElementById('kelasModalOverlay');
            const createModal = document.getElementById('createKelasModal');
            const editModal = document.getElementById('editKelasModal');
            const openCreateButton = document.getElementById('openCreateKelas');
            const editForm = document.getElementById('editKelasForm');

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

            openCreateButton?.addEventListener('click', () => openModal(createModal));
            modalOverlay?.addEventListener('click', (event) => {
                if (event.target === modalOverlay) closeModal();
            });
            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => closeModal());
            });

            document.querySelectorAll('[data-action="edit-kelas"]').forEach((button) => {
                button.addEventListener('click', () => {
                    editForm.setAttribute('action', button.getAttribute('data-update-url'));
                    document.getElementById('edit_nama_kelas').value = button.getAttribute(
                        'data-nama') || '';
                    document.getElementById('edit_tingkat').value = button.getAttribute(
                        'data-tingkat') || '';
                    document.getElementById('edit_jurusan').value = button.getAttribute(
                        'data-jurusan') || '';
                    document.getElementById('edit_jenis').value = button.getAttribute('data-jenis') ||
                        '';
                    document.getElementById('edit_guru_id').value = button.getAttribute(
                        'data-guru-id') || '';
                    openModal(editModal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });
        })();
    </script>
</x-layouts.app>
