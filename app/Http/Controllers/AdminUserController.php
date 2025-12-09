<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        if ($selfUpdate && isset($data['is_active']) && $data['is_active'] === false) {
            return back()->withErrors(['is_active' => __('Anda tidak dapat menonaktifkan akun sendiri.')]);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_active'] = $data['is_active'] ?? false;

        $user->update($data);

        return back()->with('status', __('Pengguna berhasil diperbarui.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['user' => __('Anda tidak dapat menghapus akun sendiri.')]);
        }

        $user->delete();

        return back()->with('status', __('Pengguna berhasil dihapus.'));
    }
}
