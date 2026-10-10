<x-layouts.app>
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Siswa Kelas') }} {{ $kelas->nama }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Kelola data siswa di kelas Anda sebagai wali kelas.') }}
            </p>
            <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-1">
                {{ __('Wali Kelas:') }} {{ $guru->nama }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2 max-md:grid max-md:grid-cols-2">
            <a href="{{ route('wali-kelas.siswa.unassigned') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 max-md:h-11 max-md:justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                {{ __('Claim Siswa') }}
            </a>
            <button type="button" id="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 max-md:h-11 max-md:justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Tambah Siswa') }}
            </button>
        </div>
    </div>

    @php
        // Atribut data untuk tombol Edit (dipakai tabel dan kartu HP); dibaca script pengisi modal edit.
        $dataEdit = fn ($siswa): array => [
            'update-url' => route('wali-kelas.siswa.update', $siswa),
            'modal' => 'edit-' . $siswa->id,
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'nama' => $siswa->nama,
            'gender' => $siswa->jenis_kelamin,
            'tempat' => $siswa->tempat_lahir,
            'tanggal' => optional($siswa->tanggal_lahir)->format('Y-m-d'),
            'agama' => $siswa->agama,
            'status' => $siswa->status_keluarga,
            'anak_ke' => $siswa->anak_ke,
            'telpon' => $siswa->telpon,
            'alamat' => $siswa->alamat,
            'sekolah' => $siswa->sekolah_asal,
            'diterima' => optional($siswa->tanggal_diterima)->format('Y-m-d'),
            'kelas' => $siswa->kelas_diterima,
            'ayah' => $siswa->nama_ayah,
            'ibu' => $siswa->nama_ibu,
            'pekerjaan-ayah' => $siswa->pekerjaan_ayah,
            'pekerjaan-ibu' => $siswa->pekerjaan_ibu,
            'alamat-orang-tua' => $siswa->alamat_orang_tua,
            'wali' => $siswa->nama_wali,
            'pekerjaan-wali' => $siswa->pekerjaan_wali,
            'alamat-wali' => $siswa->alamat_wali,
        ];
        // Teks pencarian kartu HP.
        $teksCari = fn ($siswa): string => mb_strtolower($siswa->nama . ' ' . $siswa->nis . ' ' . ($siswa->nisn ?? ''));
    @endphp

    {{-- HP: kartu per siswa (tabel DataTables hanya tampil di layar lebar) --}}
    <div class="md:hidden" x-data="{
        cari: '',
        semua: @js($siswas->map($teksCari)->values()),
        cocok(teks) {
            const kata = this.cari.trim().toLowerCase();
            return kata === '' || teks.includes(kata);
        },
    }">
        @if ($siswas->isEmpty())
            <div
                class="rounded-2xl border border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                {{ __('Belum ada data siswa di kelas ini.') }}
            </div>
        @else
            <div class="relative mb-3">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"
                    aria-hidden="true"></i>
                <input type="search" x-model="cari" autocomplete="off" aria-label="{{ __('Cari siswa') }}"
                    placeholder="{{ __('Cari nama, NIS, NISN...') }}"
                    class="h-11 w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3 text-base text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400">
            </div>

            <ul class="space-y-3">
                @foreach ($siswas as $siswa)
                    <li x-data="{ lainnya: false }"
                        x-show="cocok($el.dataset.cari)" data-cari="{{ $teksCari($siswa) }}"
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <a href="{{ route('wali-kelas.siswa.show', $siswa) }}"
                            class="flex items-start gap-3 px-4 py-3 active:bg-gray-50 dark:active:bg-gray-700/50">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->nama }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    NIS {{ $siswa->nis }} &bull; NISN {{ $siswa->nisn ?? '—' }} &bull;
                                    {{ $siswa->jenis_kelamin }}
                                </p>
                                @if ($siswa->tempat_lahir || $siswa->tanggal_lahir)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                                    </p>
                                @endif
                            </div>
                            <span
                                class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $siswa->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-100' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $siswa->is_active ? __('Aktif') : __('Nonaktif') }}
                            </span>
                            <i class="fa-solid fa-chevron-right mt-1.5 shrink-0 text-xs text-gray-400" aria-hidden="true"></i>
                        </a>
                        <div class="grid grid-cols-3 gap-2 border-t border-gray-100 px-4 py-3 dark:border-gray-700">
                            <a href="{{ route('rapor.print', ['siswa' => $siswa, 'tahun_ajaran_id' => session('selected_tahun_ajaran_id'), 'semester' => session('selected_semester')]) }}"
                                target="_blank"
                                class="inline-flex h-11 items-center justify-center gap-1.5 rounded-xl bg-emerald-50 text-sm font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100">
                                <i class="fa-solid fa-print text-xs" aria-hidden="true"></i>{{ __('Rapor') }}
                            </a>
                            <button type="button" data-action="edit"
                                @foreach ($dataEdit($siswa) as $kunci => $isi) data-{{ $kunci }}="{{ $isi }}" @endforeach
                                class="inline-flex h-11 items-center justify-center gap-1.5 rounded-xl bg-indigo-50 text-sm font-semibold text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-200">
                                <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>{{ __('Edit') }}
                            </button>
                            <button type="button" @click="lainnya = !lainnya" :aria-expanded="lainnya.toString()"
                                class="inline-flex h-11 items-center justify-center gap-1.5 rounded-xl bg-gray-100 text-sm font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                {{ __('Lainnya') }}
                                <i class="fa-solid fa-chevron-down text-xs transition-transform"
                                    :class="lainnya && 'rotate-180'" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div x-show="lainnya" x-cloak class="grid grid-cols-2 gap-2 px-4 pb-3 [&[x-cloak]]:hidden">
                            <form action="{{ route('wali-kelas.siswa.toggle', $siswa) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex h-11 w-full items-center justify-center rounded-xl px-3 text-sm font-semibold {{ $siswa->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-100' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200' }}">
                                    {{ $siswa->is_active ? __('Non Aktifkan') : __('Aktifkan') }}
                                </button>
                            </form>
                            <form action="{{ route('wali-kelas.siswa.destroy', $siswa) }}" method="POST"
                                onsubmit="return confirm(@js(__('Keluarkan :nama dari kelas ini? Data siswa tidak dihapus dan bisa di-claim kembali.', ['nama' => $siswa->nama])));">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-red-50 px-3 text-sm font-semibold leading-tight text-red-600 dark:bg-red-900/40 dark:text-red-200">
                                    {{ __('Keluarkan dari kelas') }}
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
            <p x-cloak x-show="! semua.some((teks) => cocok(teks))"
                class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400 [&[x-cloak]]:hidden">
                {{ __('Tidak ada siswa yang cocok dengan pencarian.') }}
            </p>
        @endif
    </div>

    <div
        class="px-6 pb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm max-md:hidden dark:border-gray-700 dark:bg-gray-800">
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
                        <th class="px-3 py-3">{{ __('Jenis Kelamin') }}</th>
                        <th class="px-3 py-3">TTL</th>
                        <th class="px-3 py-3">Status</th>
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
                            <td class="px-3 py-3">{{ $siswa->jenis_kelamin }}</td>
                            <td class="px-3 py-3 text-xs">
                                {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                            </td>
                            <td class="px-3 py-3">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $siswa->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-100' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $siswa->is_active ? __('Aktif') : __('Nonaktif') }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap items-center gap-2 justify-center">
                                    <a href="{{ route('wali-kelas.siswa.show', $siswa) }}"
                                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-100">
                                        {{ __('Detail') }}
                                    </a>
                                    <a href="{{ route('rapor.print', ['siswa' => $siswa, 'tahun_ajaran_id' => session('selected_tahun_ajaran_id'), 'semester' => session('selected_semester')]) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100">
                                        {{ __('Cetak Rapor') }}
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"
                                        data-action="edit"
                                        @foreach ($dataEdit($siswa) as $kunci => $isi) data-{{ $kunci }}="{{ $isi }}" @endforeach>
                                        {{ __('Edit') }}
                                    </button>
                                    <form action="{{ route('wali-kelas.siswa.destroy', $siswa) }}" method="POST"
                                        onsubmit="return confirm(@js(__('Keluarkan :nama dari kelas ini? Data siswa tidak dihapus dan bisa di-claim kembali.', ['nama' => $siswa->nama])));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
                                            {{ __('Keluarkan dari kelas') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('wali-kelas.siswa.toggle', $siswa) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold {{ $siswa->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $siswa->is_active ? __('Non Aktifkan') : __('Aktifkan') }}
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
                {{ __('Belum ada data siswa di kelas ini.') }}
            </div>
        @endif

    </div>

    <div id="modalOverlay"
        class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4 max-md:items-end max-md:px-0"
        data-open-modal="{{ $errors->any() ? old('_modal') : '' }}">
        <div id="createModal"
            class="modal-card hidden w-full max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl max-md:rounded-b-none max-md:border-x-0 max-md:border-b-0 dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 max-md:px-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Tambah Siswa') }}</h3>
            </div>
            <form action="{{ route('wali-kelas.siswa.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4 px-6 py-6 max-h-[70vh] overflow-y-auto max-md:max-h-[85dvh] max-md:px-4 max-md:pb-0">
                @csrf
                @include('guru.wali-kelas.siswa.partials.form', ['mode' => 'create'])
            </form>
        </div>

        <div id="editModal"
            class="modal-card hidden w-full max-w-5xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl max-md:rounded-b-none max-md:border-x-0 max-md:border-b-0 dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 max-md:px-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Edit Siswa') }}</h3>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data"
                class="space-y-4 px-6 py-6 max-h-[70vh] overflow-y-auto max-md:max-h-[85dvh] max-md:px-4 max-md:pb-0">
                @csrf
                @method('PUT')
                @include('guru.wali-kelas.siswa.partials.form', ['mode' => 'edit'])
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
                    editForm.querySelector('input[name="_modal"]').value = button.getAttribute('data-modal');

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

            // Buka ulang modal yang gagal disimpan; isian lamanya sudah dirender server.
            const modalGagal = modalOverlay.dataset.openModal;
            if (modalGagal === 'create') {
                openModal(createModal);
            } else if (modalGagal) {
                const tombolEdit = Array.from(document.querySelectorAll('[data-action="edit"]'))
                    .find((button) => button.getAttribute('data-modal') === modalGagal);
                if (tombolEdit) {
                    editForm.setAttribute('action', tombolEdit.getAttribute('data-update-url'));
                    editForm.querySelector('input[name="_modal"]').value = modalGagal;
                    openModal(editModal);
                }
            }
        })();
    </script>
</x-layouts.app>
