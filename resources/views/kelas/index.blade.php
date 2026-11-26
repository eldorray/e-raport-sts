<x-layouts.app>
    @php
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tahun_mulai')->get();
        $currentTahunId = session('selected_tahun_ajaran_id');
    @endphp

    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">{{ __('Data Kelas') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Kelola daftar kelas dan wali kelas.') }}</p>
        </div>
        <div class="grid grid-cols-2 gap-2 md:flex md:flex-wrap">
            <button type="button" onclick="document.getElementById('copyKelasModal').classList.remove('hidden')"
                class="tombol tombol-netral gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11 max-md:px-2">
                <i class="fas fa-copy"></i>
                {{ __('Salin dari TA Lain') }}
            </button>
            <button type="button" id="openCreateKelas"
                class="tombol tombol-aksen gap-2 px-4 py-2 text-sm font-semibold max-md:order-first max-md:min-h-11">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah') }}
            </button>
        </div>
    </div>

    {{-- HP: daftar kartu; tabel (DataTables) hanya untuk layar md ke atas --}}
    <div class="space-y-3 md:hidden">
        @forelse ($kelasList as $kelas)
            <article
                class="kartu p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="break-words font-semibold text-zinc-900 dark:text-zinc-100">{{ $kelas->nama }}</p>
                        <p class="mt-0.5 break-words text-sm text-zinc-600 dark:text-zinc-300">
                            <i class="fas fa-user-tie mr-1 text-xs text-zinc-400"></i>{{ optional($kelas->guru)->nama ?? __('Wali kelas belum diatur') }}
                        </p>
                        <p class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">
                            {{ __('Tingkat') }} {{ $kelas->tingkat }}@if ($kelas->jurusan) · {{ $kelas->jurusan }}@endif @if ($kelas->jenis) · {{ $kelas->jenis }}@endif
                        </p>
                    </div>
                    <span
                        class="pil pil-info shrink-0 gap-1 px-2.5 py-1">
                        <i class="fas fa-users text-[10px]"></i> {{ $kelas->siswas_count }}
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-700">
                    <button type="button"
                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-indigo-50 px-2 text-sm font-semibold text-indigo-700 transition active:scale-[0.98] dark:bg-indigo-900/40 dark:text-indigo-200"
                        data-action="edit-kelas" data-update-url="{{ route('kelas.update', $kelas) }}"
                        data-modal="edit-{{ $kelas->id }}"
                        data-nama="{{ $kelas->nama }}" data-tingkat="{{ $kelas->tingkat }}"
                        data-jurusan="{{ $kelas->jurusan }}" data-jenis="{{ $kelas->jenis }}"
                        data-guru-id="{{ $kelas->guru_id }}">
                        <i class="fas fa-pen text-xs"></i> {{ __('Edit') }}
                    </button>
                    @if ($kelas->penilaians_count > 0)
                        <x-hapus-nilai-terkunci :jumlah="$kelas->penilaians_count"
                            class="inline-flex min-h-11 w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-xl bg-zinc-100 px-2 text-sm font-semibold text-zinc-400 dark:bg-zinc-700 dark:text-zinc-400" />
                    @else
                        <form action="{{ route('kelas.destroy', $kelas) }}" method="POST"
                            onsubmit="return confirm('{{ __('Hapus kelas ini?') }}');">
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
                {{ __('Belum ada data kelas.') }}
            </div>
        @endforelse
    </div>

    <div
        class="kartu hidden overflow-hidden md:block">
        <div class="px-6 pb-6 overflow-x-auto">
            <table id="kelas-table"
                class="tabel min-w-full text-left text-sm text-zinc-700 dark:text-zinc-200">
                <thead
                   >
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
                <tbody>
                    @foreach ($kelasList as $index => $kelas)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ $kelas->nama }}
                            </td>
                            <td class="px-3 py-3">{{ $kelas->siswas_count }}</td>
                            <td class="px-3 py-3">{{ optional($kelas->guru)->nama ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $kelas->tingkat }}</td>
                            <td class="px-3 py-3">{{ $kelas->jurusan ?? '—' }}</td>
                            <td class="px-3 py-3">{{ $kelas->jenis ?? '—' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-1.5 whitespace-nowrap">
                                    <button type="button"
                                        class="pil-aksi"
                                        data-action="edit-kelas" data-update-url="{{ route('kelas.update', $kelas) }}"
                                        data-modal="edit-{{ $kelas->id }}"
                                        data-nama="{{ $kelas->nama }}" data-tingkat="{{ $kelas->tingkat }}"
                                        data-jurusan="{{ $kelas->jurusan }}" data-jenis="{{ $kelas->jenis }}"
                                        data-guru-id="{{ $kelas->guru_id }}">
                                        {{ __('Edit') }}
                                    </button>
                                    @if ($kelas->penilaians_count > 0)
                                        <x-hapus-nilai-terkunci :jumlah="$kelas->penilaians_count" />
                                    @else
                                        <form action="{{ route('kelas.destroy', $kelas) }}" method="POST"
                                            onsubmit="return confirm('{{ __('Hapus kelas ini?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="pil-aksi pil-bahaya">
                                                {{ __('Hapus') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($kelasList->isEmpty())
            <div class="px-4 py-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Belum ada data kelas.') }}
            </div>
        @endif
    </div>

    <div id="kelasModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0"
        data-open-modal="{{ $errors->any() ? old('_modal') : '' }}">
        <div id="createKelasModal"
            class="permukaan modal-card hidden w-full max-w-3xl overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:top-0 max-md:z-10 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Tambah Kelas') }}</h3>
            </div>
            <form action="{{ route('kelas.store') }}" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:py-4">
                @csrf
                @include('kelas.partials.form', ['mode' => 'create'])
            </form>
        </div>

        <div id="editKelasModal"
            class="permukaan modal-card hidden w-full max-w-3xl overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:top-0 max-md:z-10 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Edit Kelas') }}</h3>
            </div>
            <form id="editKelasForm" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:py-4">
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
                    editForm.querySelector('input[name="_modal"]').value = button.getAttribute('data-modal');
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

            // Buka ulang modal yang gagal disimpan; isian lamanya sudah dirender server.
            const modalGagal = modalOverlay.dataset.openModal;
            if (modalGagal === 'create') {
                openModal(createModal);
            } else if (modalGagal) {
                const tombolEdit = Array.from(document.querySelectorAll('[data-action="edit-kelas"]'))
                    .find((button) => button.getAttribute('data-modal') === modalGagal);
                if (tombolEdit) {
                    editForm.setAttribute('action', tombolEdit.getAttribute('data-update-url'));
                    editForm.querySelector('input[name="_modal"]').value = modalGagal;
                    openModal(editModal);
                }
            }
        })();
    </script>

    {{-- Copy Kelas Modal --}}
    <div id="copyKelasModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm" onclick="document.getElementById('copyKelasModal').classList.add('hidden')"></div>
            
            <div class="permukaan relative w-full max-w-md p-6 max-md:p-5 rounded-3xl bg-white shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        <i class="fas fa-copy mr-2 text-emerald-600"></i>
                        {{ __('Salin Kelas') }}
                    </h3>
                    <button type="button" onclick="document.getElementById('copyKelasModal').classList.add('hidden')"
                        aria-label="{{ __('Tutup') }}"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 max-md:-mr-2 max-md:inline-flex max-md:h-11 max-md:w-11 max-md:shrink-0 max-md:items-center max-md:justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('kelas.copy') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            {{ __('Salin dari Tahun Ajaran:') }}
                        </label>
                        <select name="source_tahun_ajaran_id" required
                            class="isian w-full px-3 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                            <option value="">-- {{ __('Pilih Tahun Ajaran Sumber') }} --</option>
                            @foreach ($tahunAjarans as $tahun)
                                @if ($tahun->id != $currentTahunId)
                                    <option value="{{ $tahun->id }}">
                                        {{ $tahun->nama }} - {{ $tahun->semester }}
                                        @if ($tahun->is_active) (Aktif) @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-700 dark:bg-amber-900/50 dark:text-amber-200">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('Kelas yang sudah ada tidak akan di-overwrite. Wali kelas harus di-assign manual.') }}
                    </div>

                    <div class="flex justify-end gap-3 max-md:grid max-md:grid-cols-2">
                        <button type="button" onclick="document.getElementById('copyKelasModal').classList.add('hidden')"
                            class="tombol tombol-netral px-4 py-2 text-sm font-medium max-md:min-h-11">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11">
                            <i class="fas fa-copy mr-1"></i> {{ __('Salin Kelas') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
