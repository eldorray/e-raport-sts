<x-layouts.app>
    @php
        // Form tambah dan modal edit memakai nama field yang sama, jadi isian
        // lama hanya dikembalikan ke form yang gagal disimpan.
        $modalGagal = $errors->any() ? (string) old('_modal', '') : '';
        $gagalTambah = $modalGagal === 'create';
        $gagalEdit = str_starts_with($modalGagal, 'edit-');
    @endphp
    <div class="mb-8 flex flex-col gap-2">
        <h1 class="text-[28px] font-semibold leading-tight tracking-[-0.025em] text-zinc-900 max-md:text-2xl dark:text-zinc-50">Manajemen User</h1>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Kelola akun dan tambahkan admin baru.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
        {{-- HP: form tambah dilipat; ketuk tombol + untuk membuka --}}
        <div x-data="{ buka: @js($gagalTambah) }"
            class="kartu flex flex-col gap-4 p-6 max-md:p-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Tambah User</h2>
                <button type="button" @click="buka = ! buka" :aria-expanded="buka.toString()"
                    aria-label="Buka atau tutup form tambah user"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent/10 text-accent-strong transition active:scale-95 md:hidden dark:bg-blue-900/40 dark:text-blue-200">
                    <i class="fas fa-plus transition-transform" :class="buka && 'rotate-45'"></i>
                </button>
            </div>
            <form action="{{ route('users.store') }}" method="POST"
                class="space-y-4 {{ $gagalTambah ? '' : 'max-md:hidden' }}" :class="{ 'max-md:hidden': ! buka }">
                @csrf
                <input type="hidden" name="_modal" value="create">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Nama</label>
                    <input name="name" type="text" value="{{ $gagalTambah ? old('name') : '' }}"
                        class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                        required>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Email</label>
                    <input name="email" type="email" value="{{ $gagalTambah ? old('email') : '' }}"
                        class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                        required>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Password</label>
                        <input name="password" type="password" autocomplete="new-password"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                            required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Konfirmasi
                            Password</label>
                        <input name="password_confirmation" type="password" autocomplete="new-password"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                            required>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Role</label>
                        <select name="role"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                            @foreach ($roleOptions as $value => $label)
                                <option value="{{ $value }}" @selected($gagalTambah && old('role') === $value)>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6 max-md:min-h-11 max-md:pt-0">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                            class="accent-accent h-4 w-4 max-md:h-5 max-md:w-5"
                            @checked($gagalTambah ? (bool) old('is_active') : true)>
                        <label for="is_active"
                            class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Aktif</label>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit"
                        class="tombol tombol-aksen gap-2 px-4 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:w-full">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                        Simpan User
                    </button>
                </div>
            </form>
        </div>
        {{-- HP: daftar kartu + pencarian cepat; tabel hanya untuk layar md ke atas --}}
        <div class="space-y-3 md:hidden"
            x-data="{
                q: '',
                kunci() { return this.q.trim().toLowerCase(); },
                cocok(teks) { return this.kunci() === '' || teks.includes(this.kunci()); },
                get kosong() {
                    const k = this.kunci();
                    return k !== '' && ! [...this.$root.querySelectorAll('[data-cari]')].some((el) => el.dataset.cari.includes(k));
                },
            }">
            <div class="flex items-baseline justify-between gap-3 px-1">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Daftar User</h2>
                <span class="text-xs text-zinc-600 dark:text-zinc-400">{{ $users->count() }} akun</span>
            </div>
            @if ($users->isNotEmpty())
                <div class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-zinc-400"></i>
                    <input type="search" x-model="q" placeholder="Cari nama, email, atau role"
                        aria-label="Cari user"
                        class="isian min-h-11 w-full py-2 pl-10 pr-3 text-base">
                </div>
            @endif

            @forelse ($users as $user)
                <article data-cari="{{ mb_strtolower($user->name.' '.$user->email.' '.($roleOptions[$user->role] ?? $user->role)) }}"
                    x-show="cocok($el.dataset.cari)"
                    class="kartu p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="break-words font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                            <p class="mt-0.5 break-all text-sm text-zinc-600 dark:text-zinc-300">{{ $user->email }}</p>
                            <p class="mt-0.5 text-xs capitalize text-zinc-600 dark:text-zinc-400">
                                {{ $roleOptions[$user->role] ?? $user->role }}
                                @if (auth()->id() === $user->id)
                                    · <span class="normal-case">akun Anda</span>
                                @endif
                            </p>
                        </div>
                        <span
                            class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-100' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-700">
                        <button type="button"
                            class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-indigo-50 px-2 text-sm font-semibold text-indigo-700 transition active:scale-[0.98] dark:bg-indigo-900/40 dark:text-indigo-200 {{ auth()->id() === $user->id ? 'col-span-2' : '' }}"
                            data-action="edit" data-update-url="{{ route('users.update', $user) }}"
                            data-modal="edit-{{ $user->id }}"
                            data-name="{{ $user->name }}" data-email="{{ $user->email }}"
                            data-role="{{ $user->role }}"
                            data-active="{{ $user->is_active ? '1' : '0' }}">
                            <i class="fas fa-pen text-xs"></i> Edit
                        </button>
                        @if (auth()->id() !== $user->id)
                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                onsubmit="return confirm('Hapus user ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex min-h-11 w-full items-center justify-center gap-1.5 rounded-xl bg-red-50 px-2 text-sm font-semibold text-red-600 transition active:scale-[0.98] dark:bg-red-900/40 dark:text-red-300">
                                    <i class="fas fa-trash text-xs"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div
                    class="kartu px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
                    Belum ada user.
                </div>
            @endforelse

            <p x-show="kosong" style="display: none"
                class="rounded-2xl border border-dashed border-zinc-300 px-4 py-6 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                Tidak ada user yang cocok dengan pencarian.
            </p>
        </div>

        <div
            class="kartu hidden space-y-4 p-6 md:block">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Daftar User</h2>
                <p class="text-xs text-zinc-600 dark:text-zinc-400">Edit melalui modal untuk menjaga tampilan ringkas.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="tabel min-w-full text-sm">
                    <thead
                       >
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">Nama</th>
                            <th class="px-3 py-3">Email</th>
                            <th class="px-3 py-3">Role</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-3 py-3">{{ $loop->iteration }}</td>
                                <td class="px-3 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $user->name }}</td>
                                <td class="px-3 py-3 text-zinc-700 dark:text-zinc-200">{{ $user->email }}</td>
                                <td class="px-3 py-3 capitalize">{{ $roleOptions[$user->role] ?? $user->role }}</td>
                                <td class="px-3 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-100' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                                        <button type="button"
                                            class="pil-aksi"
                                            data-action="edit" data-update-url="{{ route('users.update', $user) }}"
                                            data-modal="edit-{{ $user->id }}"
                                            data-name="{{ $user->name }}" data-email="{{ $user->email }}"
                                            data-role="{{ $user->role }}"
                                            data-active="{{ $user->is_active ? '1' : '0' }}">
                                            Edit
                                        </button>
                                        @if (auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('Hapus user ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="pil-aksi pil-bahaya">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-3 py-4 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                    Belum ada user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="userModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-zinc-900/40 backdrop-blur-sm px-4 max-md:items-end max-md:px-0"
        data-open-modal="{{ $modalGagal }}">
        <div id="editUserModal"
            class="permukaan hidden w-full max-w-3xl overflow-hidden max-md:max-h-[92dvh] max-md:overflow-y-auto max-md:overscroll-contain max-md:rounded-b-none rounded-3xl bg-white shadow-[0_24px_64px_rgba(0,0,0,.25)] animate-pop motion-reduce:animate-none dark:bg-zinc-900">
            <div class="border-b border-zinc-100 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:top-0 max-md:z-10 max-md:px-4">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Edit User</h3>
            </div>
            <form id="editUserForm" method="POST" class="space-y-4 px-6 py-6 max-md:px-4 max-md:py-4">
                @csrf
                @method('PUT')
                <input id="edit_modal" type="hidden" name="_modal" value="{{ $gagalEdit ? $modalGagal : '' }}">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Nama</label>
                        <input id="edit_name" name="name" type="text" value="{{ $gagalEdit ? old('name') : '' }}"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                            required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Email</label>
                        <input id="edit_email" name="email" type="email" value="{{ $gagalEdit ? old('email') : '' }}"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                            required>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Role</label>
                        <select id="edit_role" name="role"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base">
                            @foreach ($roleOptions as $value => $label)
                                <option value="{{ $value }}" @selected($gagalEdit && old('role') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6 max-md:min-h-11 max-md:pt-0">
                        <input id="edit_active" name="is_active" type="checkbox" value="1"
                            class="accent-accent h-4 w-4 max-md:h-5 max-md:w-5"
                            @checked($gagalEdit && (bool) old('is_active'))>
                        <label for="edit_active"
                            class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Aktif</label>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Password
                            (opsional)</label>
                        <input id="edit_password" name="password" type="password" autocomplete="new-password"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                            placeholder="Biarkan kosong jika tidak diubah">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">Konfirmasi
                            Password</label>
                        <input id="edit_password_confirmation" name="password_confirmation" type="password"
                            autocomplete="new-password"
                            class="isian w-full px-4 py-2.5 text-sm max-md:min-h-11 max-md:text-base"
                            placeholder="Ulangi password">
                    </div>
                </div>
                {{-- HP: bilah tombol menempel di bawah lembar modal agar selalu terjangkau --}}
                <div class="flex justify-end gap-3 bg-white pt-2 dark:bg-zinc-900 max-md:sticky max-md:bottom-0 max-md:z-10 max-md:-mx-4 max-md:-mb-4 max-md:grid max-md:grid-cols-2 max-md:border-t max-md:border-zinc-100 max-md:px-4 max-md:pt-4 max-md:pb-[max(1rem,env(safe-area-inset-bottom))] max-md:dark:border-zinc-700">
                    <button type="button" data-close-user-modal
                        class="tombol tombol-netral gap-2 px-4 py-2.5 text-sm font-semibold max-md:min-h-11">
                        Batal
                    </button>
                    <button type="submit"
                        class="tombol tombol-aksen gap-2 px-4 py-2.5 text-sm font-semibold max-md:min-h-11 max-md:gap-1.5 max-md:px-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const overlay = document.getElementById('userModalOverlay');
            const modal = document.getElementById('editUserModal');
            const form = document.getElementById('editUserForm');

            function openModal() {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal() {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                form.reset();
            }

            overlay?.addEventListener('click', (e) => {
                if (e.target === overlay) closeModal();
            });
            document.querySelectorAll('[data-close-user-modal]').forEach((btn) =>
                btn.addEventListener('click', closeModal));

            document.querySelectorAll('[data-action="edit"]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const updateUrl = btn.getAttribute('data-update-url');
                    form.setAttribute('action', updateUrl);
                    document.getElementById('edit_modal').value = btn.getAttribute('data-modal');

                    document.getElementById('edit_name').value = btn.getAttribute('data-name') || '';
                    document.getElementById('edit_email').value = btn.getAttribute('data-email') || '';
                    document.getElementById('edit_role').value = btn.getAttribute('data-role') ||
                        'admin';
                    document.getElementById('edit_active').checked = btn.getAttribute('data-active') ===
                        '1';
                    document.getElementById('edit_password').value = '';
                    document.getElementById('edit_password_confirmation').value = '';

                    openModal();
                });
            });

            // Buka ulang modal edit yang gagal disimpan; isian lamanya sudah dirender server.
            const modalGagal = overlay.dataset.openModal;
            if (modalGagal && modalGagal.startsWith('edit-')) {
                const tombolEdit = Array.from(document.querySelectorAll('[data-action="edit"]'))
                    .find((btn) => btn.getAttribute('data-modal') === modalGagal);
                if (tombolEdit) {
                    form.setAttribute('action', tombolEdit.getAttribute('data-update-url'));
                    openModal();
                }
            }
        })();
    </script>
</x-layouts.app>
