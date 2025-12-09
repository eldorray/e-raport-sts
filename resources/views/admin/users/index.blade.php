<x-layouts.app>
    <div class="mb-8 flex flex-col gap-2">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">Manajemen User</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Kelola akun dan tambahkan admin baru.</p>
    </div>

    @if (session('status'))
        <div
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
            <p class="text-sm font-semibold">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 shadow-sm dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
            <ul class="space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[380px,1fr]">
        <div
            class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tambah User</h2>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Nama</label>
                    <input name="name" type="text" value="{{ old('name') }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        required>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        required>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Password</label>
                        <input name="password" type="password" autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Konfirmasi
                            Password</label>
                        <input name="password_confirmation" type="password" autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            required>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Role</label>
                        <select name="role"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            @foreach ($roleOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active"
                            class="text-sm font-semibold text-gray-800 dark:text-gray-100">Aktif</label>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                        Simpan User
                    </button>
                </div>
            </form>
        </div>
        <div
            class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Daftar User</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Edit melalui modal untuk menjaga tampilan ringkas.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead
                        class="bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">Nama</th>
                            <th class="px-3 py-3">Email</th>
                            <th class="px-3 py-3">Role</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-3 py-3">{{ $loop->iteration }}</td>
                                <td class="px-3 py-3 font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $user->name }}</td>
                                <td class="px-3 py-3 text-gray-700 dark:text-gray-200">{{ $user->email }}</td>
                                <td class="px-3 py-3 capitalize">{{ $roleOptions[$user->role] ?? $user->role }}</td>
                                <td class="px-3 py-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-100' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <button type="button"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600"
                                            data-action="edit" data-update-url="{{ route('users.update', $user) }}"
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
                                                    class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600">
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
                                    class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="userModalOverlay" class="fixed inset-0 z-40 hidden items-center justify-center bg-gray-900/60 px-4">
        <div id="editUserModal"
            class="hidden w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit User</h3>
            </div>
            <form id="editUserForm" method="POST" class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Nama</label>
                        <input id="edit_name" name="name" type="text"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Email</label>
                        <input id="edit_email" name="email" type="email"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            required>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Role</label>
                        <select id="edit_role" name="role"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            @foreach ($roleOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input id="edit_active" name="is_active" type="checkbox" value="1"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="edit_active"
                            class="text-sm font-semibold text-gray-800 dark:text-gray-100">Aktif</label>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Password
                            (opsional)</label>
                        <input id="edit_password" name="password" type="password" autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Biarkan kosong jika tidak diubah">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-gray-800 dark:text-gray-100">Konfirmasi
                            Password</label>
                        <input id="edit_password_confirmation" name="password_confirmation" type="password"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Ulangi password">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-close-user-modal
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-3 focus:ring-gray-200/60 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-gray-700">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
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
        })();
    </script>
</x-layouts.app>
