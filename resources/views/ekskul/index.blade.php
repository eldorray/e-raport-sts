<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">Data Ekstrakurikuler</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Kelola nama ekskul dan pembina (guru).</p>
        </div>
        <button type="button" id="openCreateModal"
            class="tombol tombol-aksen gap-2 px-4 py-2 text-sm font-semibold max-md:min-h-11">
            <i class="fa-solid fa-plus text-xs"></i> Tambah
        </button>
    </div>

    {{-- HP: daftar kartu; tabel hanya untuk layar md ke atas --}}
    <div class="space-y-3 md:hidden">
        @forelse ($ekskuls as $ekskul)
            <article
                class="kartu p-4">
                <p class="break-words font-semibold text-zinc-900 dark:text-zinc-100">{{ $ekskul->nama }}</p>
                <p class="mt-0.5 break-words text-sm text-zinc-600 dark:text-zinc-300">
                    <i class="fas fa-user-tie mr-1 text-xs text-zinc-400"></i>{{ optional($ekskul->guru)->nama ?? 'Pembina belum diatur' }}
                </p>
                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-700">
                    <button type="button"
                        class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-indigo-50 px-2 text-sm font-semibold text-indigo-700 transition active:scale-[0.98] dark:bg-indigo-900/40 dark:text-indigo-200"
                        data-action="edit" data-id="{{ $ekskul->id }}" data-nama="{{ $ekskul->nama }}"
                        data-guru="{{ $ekskul->guru_id }}">
                        <i class="fa-solid fa-pen text-xs"></i> Edit
                    </button>
                    @if ($ekskul->penilaians_count > 0)
                        <x-hapus-nilai-terkunci :jumlah="$ekskul->penilaians_count"
                            class="inline-flex min-h-11 w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-xl bg-zinc-100 px-2 text-sm font-semibold text-zinc-400 dark:bg-zinc-700 dark:text-zinc-400" />
                    @else
                        <form action="{{ route('ekskul.destroy', $ekskul) }}" method="POST"
                            onsubmit="return confirm('Hapus ekskul ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-1.5 rounded-xl bg-red-50 px-2 text-sm font-semibold text-red-600 transition active:scale-[0.98] dark:bg-red-900/40 dark:text-red-300">
                                <i class="fa-solid fa-trash text-xs"></i> Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div
                class="kartu px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
                Belum ada data ekskul.
            </div>
        @endforelse
    </div>

    <div class="kartu hidden p-4 md:block">
        <div class="overflow-x-auto">
            <table
                class="tabel min-w-full text-left text-sm text-zinc-700 dark:text-zinc-200">
                <thead
                   >
                    <tr>
                        <th class="px-3 py-3 w-12">No</th>
                        <th class="px-3 py-3">Nama Ekskul</th>
                        <th class="px-3 py-3">Pembina</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ekskuls as $index => $ekskul)
                        <tr>
                            <td class="px-3 py-3">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">{{ $ekskul->nama }}</td>
                            <td class="px-3 py-3">{{ optional($ekskul->guru)->nama ?? 'Belum diatur' }}</td>
                            <td class="px-3 py-3 text-center">
                                <div class="inline-flex gap-2">
                                    <button type="button"
                                        class="tombol tombol-aksen gap-1 px-3 py-1 text-xs font-semibold"
                                        data-action="edit" data-id="{{ $ekskul->id }}" data-nama="{{ $ekskul->nama }}"
                                        data-guru="{{ $ekskul->guru_id }}">
                                        <i class="fa-solid fa-pen text-[11px]"></i> Edit
                                    </button>
                                    @if ($ekskul->penilaians_count > 0)
                                        <x-hapus-nilai-terkunci :jumlah="$ekskul->penilaians_count" :label="__('Del')" />
                                    @else
                                        <form action="{{ route('ekskul.destroy', $ekskul) }}" method="POST"
                                            onsubmit="return confirm('Hapus ekskul ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-200">
                                                <i class="fa-solid fa-trash text-[11px]"></i> Del
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-sm text-zinc-600">Belum ada data
                                ekskul.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0">
        <div id="formModal"
            class="permukaan modal-card hidden w-full max-w-xl overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none rounded-3xl bg-white dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:top-0 max-md:z-10 max-md:px-4">
                <h3 id="modalTitle" class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tambah Ekskul</h3>
            </div>
            <form id="modalForm" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:pt-4 max-md:pb-[max(1rem,env(safe-area-inset-bottom))]">
                @csrf
                <div class="space-y-1">
                    <label for="nama" class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Nama
                        Ekskul</label>
                    <input id="nama" name="nama" type="text" required
                        class="isian w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base" />
                </div>
                <div class="space-y-1">
                    <label for="guru_id" class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Pembina
                        (Guru)</label>
                    <select id="guru_id" name="guru_id"
                        class="isian w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
                        <option value="">Belum ditentukan</option>
                        @foreach ($gurus as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 max-md:grid max-md:grid-cols-2 max-md:pt-2">
                    <button type="button" data-close-modal
                        class="rounded-lg bg-zinc-100 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-200 focus:outline-none max-md:min-h-11">Batal</button>
                    <button type="submit"
                        class="tombol tombol-aksen px-4 py-2 text-sm font-semibold max-md:min-h-11">Simpan</button>
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
