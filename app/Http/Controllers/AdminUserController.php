<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->get();

        $roleOptions = ['admin' => 'Admin', 'guru' => 'Guru'];

        return view('admin.users.index', [
            'users' => $users,
            'roleOptions' => $roleOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'guru'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $data['is_active'] ?? true;

        User::create($data);

        return back()->with('status', __('Pengguna berhasil ditambahkan.'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $selfUpdate = $request->user()->id === $user->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'guru'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Checkbox yang tidak dicentang tidak ikut terkirim, jadi baca sebagai boolean.
        $isActive = $request->boolean('is_active');

        if ($selfUpdate && ! $isActive) {
            return back()->withErrors(['is_active' => __('Anda tidak dapat menonaktifkan akun sendiri.')])->withInput();
        }

        if ($selfUpdate && $data['role'] !== $user->role) {
            return back()->withErrors(['role' => __('Anda tidak dapat mengubah peran akun sendiri.')])->withInput();
        }

        $tetapAdminAktif = $data['role'] === 'admin' && $isActive;

        if ($this->isActiveAdmin($user) && ! $tetapAdminAktif && $this->activeAdminCount() <= 1) {
            return back()->withErrors(['user' => __('Minimal harus ada satu admin aktif. Tambahkan atau aktifkan admin lain terlebih dahulu.')])->withInput();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = $isActive;

        $user->update($data);

        return back()->with('status', __('Pengguna berhasil diperbarui.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['user' => __('Anda tidak dapat menghapus akun sendiri.')]);
        }

        if ($this->isActiveAdmin($user) && $this->activeAdminCount() <= 1) {
            return back()->withErrors(['user' => __('Admin aktif terakhir tidak dapat dihapus. Tambahkan atau aktifkan admin lain terlebih dahulu.')]);
        }

        // Menghapus user ikut menghapus data guru beserta nilai ekskulnya (cascade).
        if (Guru::query()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['user' => __('Pengguna ini terhubung dengan data guru. Nonaktifkan akunnya, atau hapus guru melalui menu Guru.')]);
        }

        $user->delete();

        return back()->with('status', __('Pengguna berhasil dihapus.'));
    }

    private function isActiveAdmin(User $user): bool
    {
        return $user->role === 'admin' && $user->is_active;
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->count();
    }
}
