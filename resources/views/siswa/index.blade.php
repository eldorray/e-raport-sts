<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Data Siswa') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Kelola data siswa, termasuk detail lengkap dan foto.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($siswas->count() > 0)
                <form action="{{ route('siswa.destroy-all') }}" method="POST"
                    onsubmit="return confirm('{{ __('Hapus semua siswa? Tindakan ini tidak dapat dibatalkan.') }}');">
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
            <form id="importForm" action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data"
                class="inline-flex">
                @csrf
                <input id="siswaFileInput" name="file" type="file" accept=".xlsx,.xls,.csv" class="hidden">
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-600 focus:outline-none focus:ring-4 focus:ring-red-500/30"
                    id="triggerImport">
                    {{ __('Upload Siswa') }}
                </button>
            </form>
            <a href="{{ route('siswa.template') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-200 focus:outline-none focus:ring-4 focus:ring-gray-300/60">
                {{ __('Unduh Template') }}
            </a>
        </div>
    </div>

    <div
        class="px-6 pb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table id="siswa-table"
                class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 dark:divide-gray-700 dark:text-gray-200">
                <thead
                    class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">NIS</th>
                        <th class="px-3 py-3">NISN</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">Kelas</th>
                        <th class="px-3 py-3">{{ __('Jenis Kelamin') }}</th>
                        <th class="px-3 py-3">TTL</th>
                        <th class="px-3 py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($siswas as $index => $siswa)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $siswa->nis }}</td>
                            <td class="px-3 py-3">{{ $siswa->nisn ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $siswa->nama }}</td>
                            <td class="px-3 py-3">{{ optional($siswa->kelas)->nama ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $siswa->jenis_kelamin }}</td>
                            <td class="px-3 py-3 text-xs">
                                {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">
                                    <a href="{{ route('siswa.show', $siswa) }}"
                                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-100">
                                        {{ __('Show') }}
                                    </a>
                                    <a href="{{ route('rapor.print', ['siswa' => $siswa, 'tahun_ajaran_id' => session('selected_tahun_ajaran_id'), 'semester' => session('selected_semester')]) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100">
                                        {{ __('Cetak Rapor') }}
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"
                                        data-action="edit" data-update-url="{{ route('siswa.update', $siswa) }}"
                                        data-nis="{{ $siswa->nis }}" data-nisn="{{ $siswa->nisn }}"
                                        data-nama="{{ $siswa->nama }}" data-gender="{{ $siswa->jenis_kelamin }}"
                                        data-tempat="{{ $siswa->tempat_lahir }}"
                                        data-tanggal="{{ optional($siswa->tanggal_lahir)->format('Y-m-d') }}"
                                        data-agama="{{ $siswa->agama }}" data-status="{{ $siswa->status_keluarga }}"
                                        data-anak_ke="{{ $siswa->anak_ke }}" data-telpon="{{ $siswa->telpon }}"
                                        data-alamat="{{ $siswa->alamat }}" data-sekolah="{{ $siswa->sekolah_asal }}"
                                        data-diterima="{{ optional($siswa->tanggal_diterima)->format('Y-m-d') }}"
                                        data-kelas="{{ $siswa->kelas_diterima }}" data-ayah="{{ $siswa->nama_ayah }}"
                                        data-ibu="{{ $siswa->nama_ibu }}"
                                        data-pekerjaan-ayah="{{ $siswa->pekerjaan_ayah }}"
                                        data-pekerjaan-ibu="{{ $siswa->pekerjaan_ibu }}"
                                        data-alamat-orang-tua="{{ $siswa->alamat_orang_tua }}"
                                        data-wali="{{ $siswa->nama_wali }}"
                                        data-pekerjaan-wali="{{ $siswa->pekerjaan_wali }}"
                                        data-alamat-wali="{{ $siswa->alamat_wali }}">
                                        {{ __('Edit') }}
                                    </button>
                                    <form action="{{ route('siswa.destroy', $siswa) }}" method="POST"
                                        onsubmit="return confirm('{{ __('Hapus siswa ini?') }}');">
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

        @if ($siswas->isEmpty())
            <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('Belum ada data siswa.') }}
            </div>
        @endif

    </div>

    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="createModal"
            class="modal-card hidden w-full max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Tambah Siswa') }}</h3>
            </div>
            <form action="{{ route('siswa.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4 px-6 py-6">
                @csrf
                @include('siswa.partials.form', ['mode' => 'create'])
            </form>
        </div>

        <div id="editModal"
            class="modal-card hidden w-full max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit Siswa') }}</h3>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')
                @include('siswa.partials.form', ['mode' => 'edit'])
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

            openCreateModalButton?.addEventListener('click', () => openModal(createModal));
            modalOverlay?.addEventListener('click', (event) => {
                if (event.target === modalOverlay) closeModal();
            });
            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => closeModal());
            });

            document.querySelectorAll('[data-action="edit"]').forEach((button) => {
                button.addEventListener('click', () => {
                    editForm.setAttribute('action', button.getAttribute('data-update-url'));

                    document.getElementById('edit_nis').value = button.getAttribute('data-nis') || '';
                    document.getElementById('edit_nisn').value = button.getAttribute('data-nisn') || '';
                    document.getElementById('edit_nama').value = button.getAttribute('data-nama') || '';
                    document.getElementById('edit_gender').value = button.getAttribute('data-gender') ||
                        '';
                    document.getElementById('edit_tempat').value = button.getAttribute('data-tempat') ||
                        '';
                    document.getElementById('edit_tanggal_lahir').value = button.getAttribute(
                        'data-tanggal') || '';
                    document.getElementById('edit_agama').value = button.getAttribute('data-agama') ||
                        '';
                    document.getElementById('edit_status_keluarga').value = button.getAttribute(
                        'data-status') || '';
                    document.getElementById('edit_anak_ke').value = button.getAttribute(
                        'data-anak_ke') || '';
                    document.getElementById('edit_telpon').value = button.getAttribute('data-telpon') ||
                        '';
                    document.getElementById('edit_alamat').value = button.getAttribute('data-alamat') ||
                        '';
                    document.getElementById('edit_sekolah_asal').value = button.getAttribute(
                        'data-sekolah') || '';
                    document.getElementById('edit_tanggal_diterima').value = button.getAttribute(
                        'data-diterima') || '';
                    document.getElementById('edit_kelas_diterima').value = button.getAttribute(
                        'data-kelas') || '';
                    document.getElementById('edit_nama_ayah').value = button.getAttribute(
                        'data-ayah') || '';
                    document.getElementById('edit_nama_ibu').value = button.getAttribute('data-ibu') ||
                        '';
                    document.getElementById('edit_pekerjaan_ayah').value = button.getAttribute(
                        'data-pekerjaan-ayah') || '';
                    document.getElementById('edit_pekerjaan_ibu').value = button.getAttribute(
                        'data-pekerjaan-ibu') || '';
                    document.getElementById('edit_alamat_orang_tua').value = button.getAttribute(
                        'data-alamat-orang-tua') || '';
                    document.getElementById('edit_nama_wali').value = button.getAttribute(
                        'data-wali') || '';
                    document.getElementById('edit_pekerjaan_wali').value = button.getAttribute(
                        'data-pekerjaan-wali') || '';
                    document.getElementById('edit_alamat_wali').value = button.getAttribute(
                        'data-alamat-wali') || '';

                    openModal(editModal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeModal();
            });

            const importForm = document.getElementById('importForm');
            const importButton = document.getElementById('triggerImport');
            const fileInput = document.getElementById('siswaFileInput');

            importButton?.addEventListener('click', () => fileInput?.click());
            fileInput?.addEventListener('change', () => {
                if (fileInput.files.length) {
                    importForm.submit();
                }
            });
        })();
    </script>
</x-layouts.app>
