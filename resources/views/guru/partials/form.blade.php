@php
    $prefix = $mode === 'edit' ? 'edit_' : 'create_';
    // Isian lama hanya dipakai untuk modal yang gagal disimpan, karena form
    // tambah dan edit berada di halaman yang sama dengan nama field yang sama.
    // Password tidak pernah di-flash oleh Laravel, jadi harus diketik ulang.
    $modalGagal = $errors->any() ? (string) old('_modal', '') : '';
    $pakaiIsianLama = $mode === 'edit' ? str_starts_with($modalGagal, 'edit-') : $modalGagal === 'create';
    $nilai = fn (string $field): mixed => $pakaiIsianLama ? old($field) : null;
    $aktif = $pakaiIsianLama ? (string) old('is_active') === '1' : $mode === 'create';
@endphp
<input type="hidden" name="_modal" value="{{ $pakaiIsianLama ? $modalGagal : ($mode === 'create' ? 'create' : '') }}">
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="{{ $prefix }}nama"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama') }}</label>
        <input id="{{ $prefix }}nama" name="nama" type="text" value="{{ $nilai('nama') }}" required
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div>
            <label for="{{ $prefix }}nip"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP</label>
            <input id="{{ $prefix }}nip" name="nip" type="text" value="{{ $nilai('nip') }}" required
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
        </div>
        <div>
            <label for="{{ $prefix }}nik"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIK/NUPTK</label>
            <input id="{{ $prefix }}nik" name="nik" type="text" value="{{ $nilai('nik') }}"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
        </div>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label for="{{ $prefix }}gender"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">L/P</label>
        <select id="{{ $prefix }}gender" name="jenis_kelamin"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
            <option value="L" @selected($nilai('jenis_kelamin') === 'L')>L</option>
            <option value="P" @selected($nilai('jenis_kelamin') === 'P')>P</option>
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}tempat" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat
            Lahir</label>
        <input id="{{ $prefix }}tempat" name="tempat_lahir" type="text" value="{{ $nilai('tempat_lahir') }}"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}tanggal"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
        <input id="{{ $prefix }}tanggal" name="tanggal_lahir" type="date" value="{{ $nilai('tanggal_lahir') }}"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label for="{{ $prefix }}pendidikan"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pendidikan</label>
        <input id="{{ $prefix }}pendidikan" name="pendidikan" type="text" value="{{ $nilai('pendidikan') }}"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}wali" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wali
            Kelas</label>
        <input id="{{ $prefix }}wali" name="wali_kelas" type="text" value="{{ $nilai('wali_kelas') }}"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}jtm"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">JTM</label>
        <input id="{{ $prefix }}jtm" name="jtm" type="number" value="{{ $nilai('jtm') }}" min="0"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label for="{{ $prefix }}password"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
        <input id="{{ $prefix }}password" name="password" type="text"
            {{ $mode === 'edit' ? '' : 'required' }}
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
        @if ($mode === 'edit')
            <p class="mt-1 text-xs text-gray-500">{{ __('Kosongkan jika tidak mengubah password.') }}</p>
        @endif
    </div>
    <div class="flex items-center gap-3 pt-6 max-md:min-h-11 max-md:pt-0">
        <input type="hidden" name="is_active" value="0">
        <input id="{{ $prefix }}active" name="is_active" type="checkbox" value="1"
            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 max-md:h-5 max-md:w-5"
            @checked($aktif)>
        <label for="{{ $prefix }}active"
            class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Aktif') }}</label>
    </div>
</div>

{{-- HP: bilah tombol menempel di bawah lembar modal agar selalu terjangkau --}}
<div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-white pt-4 dark:border-gray-700 dark:bg-gray-900 max-md:sticky max-md:bottom-0 max-md:z-10 max-md:-mx-4 max-md:-mb-4 max-md:grid max-md:grid-cols-2 max-md:px-4 max-md:pb-[max(1rem,env(safe-area-inset-bottom))]">
    <button type="button"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300 max-md:min-h-11"
        data-close-modal>{{ __('Batal') }}</button>
    <button type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 max-md:min-h-11 max-md:justify-center">
        {{ $mode === 'create' ? __('Simpan Data') : __('Perbarui Data') }}
    </button>
</div>
