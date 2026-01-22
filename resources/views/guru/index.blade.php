<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Data Guru') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Kelola data guru, wali kelas, dan akun login.') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($gurus->count() > 0)
                <form action="{{ route('guru.destroy-all') }}" method="POST"
                    onsubmit="return confirm('{{ __('Hapus semua guru? Tindakan ini tidak dapat dibatalkan.') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('Hapus Semua') }}
                    </button>
                </form>
            @endif
            <button type="button" id="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah') }}
            </button>
            <form id="importForm" action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data"
                class="inline-flex">
                @csrf
                <input id="guruFileInput" name="file" type="file" accept=".xlsx,.xls,.csv" class="hidden">
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-600 focus:outline-none focus:ring-4 focus:ring-red-500/30"
                    id="triggerImport">
                    {{ __('Upload Guru') }}
                </button>
            </form>
            <a href="{{ route('guru.template') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-200 focus:outline-none focus:ring-4 focus:ring-gray-300/60">
                {{ __('Unduh Template') }}
            </a>
            <button type="button" id="openSyncModal"
                class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {{ __('Sync API') }}
            </button>
        </div>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

        <div class="px-6 pb-6 overflow-x-auto">
            <table id="guru-table"
                class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">{{ __('NIK/NUPTK') }}</th>
                        <th class="px-3 py-3">{{ __('Nama') }}</th>
                        <th class="px-3 py-3">{{ __('L/P') }}</th>
                        <th class="px-3 py-3">{{ __('TTL') }}</th>
                        <th class="px-3 py-3">{{ __('Pendidikan') }}</th>
                        <th class="px-3 py-3">{{ __('Password') }}</th>
                        <th class="px-3 py-3">{{ __('Wali Kelas') }}</th>
                        <th class="px-3 py-3">{{ __('JTM') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($gurus as $index => $guru)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $guru->nip }}</div>
                                <div class="text-xs text-gray-500">{{ $guru->nik ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-3">{{ $guru->nama }}</td>
                            <td class="px-3 py-3">{{ $guru->jenis_kelamin }}</td>
                            <td class="px-3 py-3 text-xs">
                                {{ $guru->tempat_lahir }}{{ $guru->tanggal_lahir ? ', ' . $guru->tanggal_lahir->translatedFormat('d F Y') : '' }}
                            </td>
                            <td class="px-3 py-3">{{ $guru->pendidikan ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $guru->initial_password ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $guru->wali_kelas ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $jtmMengajar[$guru->id] ?? 0 }}</td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"
                                        data-action="edit" data-update-url="{{ route('guru.update', $guru) }}"
                                        data-nama="{{ $guru->nama }}" data-nip="{{ $guru->nip }}"
                                        data-nik="{{ $guru->nik }}" data-gender="{{ $guru->jenis_kelamin }}"
                                        data-tempat="{{ $guru->tempat_lahir }}"
                                        data-tanggal="{{ optional($guru->tanggal_lahir)->format('Y-m-d') }}"
                                        data-pendidikan="{{ $guru->pendidikan }}" data-wali="{{ $guru->wali_kelas }}"
                                        data-jtm="{{ $guru->jtm }}"
                                        data-active="{{ $guru->is_active ? '1' : '0' }}">
                                        {{ __('Edit') }}
                                    </button>
                                    <form action="{{ route('guru.destroy', $guru) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Hapus guru ini?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
                                            {{ __('Hapus') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('guru.toggle', $guru) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $guru->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $guru->is_active ? __('Non Aktifkan') : __('Aktifkan') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($gurus->isEmpty())
            <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('Belum ada data guru.') }}
            </div>
        @endif
    </div>

    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="createModal"
            class="modal-card hidden w-full max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Tambah Guru') }}</h3>
            </div>
            <form action="{{ route('guru.store') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @include('guru.partials.form', ['mode' => 'create'])
            </form>
        </div>

        <div id="editModal"
            class="modal-card hidden w-full max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit Guru') }}</h3>
            </div>
            <form id="editForm" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')
                @include('guru.partials.form', ['mode' => 'edit'])
            </form>
        </div>

        <div id="syncModal"
            class="modal-card hidden w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Sync Data Guru dari API') }}</h3>
            </div>
            <form action="{{ route('guru.sync') }}" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pilih Sumber Data') }}</label>
                    <select name="source" required class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                        <option value="">-- Pilih Sumber --</option>
                        <option value="guru-mi">Guru MI</option>
                        <option value="guru-smp">Guru SMP</option>
                    </select>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-300">
                    <p class="font-medium mb-2">{{ __('Informasi:') }}</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Data akan diambil dari: <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">{{ env('SYNC_API_BASE_URL', 'https://datainduk.ypdhalmadani.sch.id') }}/api/[source]/all</code></li>
                        <li>Guru yang sudah ada (berdasarkan NIP) akan diperbarui</li>
                        <li>Guru baru akan ditambahkan beserta akun login</li>
                        <li>Password default: sama dengan NIP</li>
                    </ul>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" data-close-modal
                        class="rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-200">{{ __('Batal') }}</button>
                    <button type="submit"
                        class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">{{ __('Sync Sekarang') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const modalOverlay = document.getElementById('modalOverlay');
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            const syncModal = document.getElementById('syncModal');
            const openCreateModalButton = document.getElementById('openCreateModal');
            const openSyncModalButton = document.getElementById('openSyncModal');
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
                syncModal?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            openCreateModalButton?.addEventListener('click', () => openModal(createModal));
            openSyncModalButton?.addEventListener('click', () => openModal(syncModal));
            modalOverlay?.addEventListener('click', (event) => {
                if (event.target === modalOverlay) closeModal();
            });
            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => closeModal());
            });

            document.querySelectorAll('[data-action="edit"]').forEach((button) => {
                button.addEventListener('click', () => {
                    editForm.setAttribute('action', button.getAttribute('data-update-url'));

                    document.getElementById('edit_nama').value = button.getAttribute('data-nama') || '';
                    document.getElementById('edit_nip').value = button.getAttribute('data-nip') || '';
                    document.getElementById('edit_nik').value = button.getAttribute('data-nik') || '';
                    document.getElementById('edit_gender').value = button.getAttribute('data-gender') ||
                        'L';
                    document.getElementById('edit_tempat').value = button.getAttribute('data-tempat') ||
                        '';
                    document.getElementById('edit_tanggal').value = button.getAttribute(
                        'data-tanggal') || '';
                    document.getElementById('edit_pendidikan').value = button.getAttribute(
                        'data-pendidikan') || '';
                    document.getElementById('edit_wali').value = button.getAttribute('data-wali') || '';
                    document.getElementById('edit_jtm').value = button.getAttribute('data-jtm') || '';
                    document.getElementById('edit_active').checked = button.getAttribute(
                        'data-active') === '1';

                    openModal(editModal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });

            const importForm = document.getElementById('importForm');
            const importButton = document.getElementById('triggerImport');
            const fileInput = document.getElementById('guruFileInput');

            importButton?.addEventListener('click', () => fileInput?.click());
            fileInput?.addEventListener('change', () => {
                if (fileInput.files.length) {
                    importForm.submit();
                }
            });
        })();
    </script>
</x-layouts.app>
