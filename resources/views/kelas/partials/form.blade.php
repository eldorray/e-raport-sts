@php
    $prefix = $mode === 'edit' ? 'edit_' : 'create_';
    // Isian lama hanya dipakai untuk modal yang gagal disimpan, karena form
    // tambah dan edit berada di halaman yang sama dengan nama field yang sama.
    $modalGagal = $errors->any() ? (string) old('_modal', '') : '';
    $pakaiIsianLama = $mode === 'edit' ? str_starts_with($modalGagal, 'edit-') : $modalGagal === 'create';
    $nilai = fn (string $field): mixed => $pakaiIsianLama ? old($field) : null;
@endphp
<input type="hidden" name="_modal" value="{{ $pakaiIsianLama ? $modalGagal : ($mode === 'create' ? 'create' : '') }}">
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="{{ $prefix }}nama_kelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
            Kelas</label>
        <input id="{{ $prefix }}nama_kelas" name="nama" type="text" value="{{ $nilai('nama') }}" required
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}tingkat"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat</label>
        <input id="{{ $prefix }}tingkat" name="tingkat" type="text" value="{{ $nilai('tingkat') }}" required
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}jurusan"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jurusan</label>
        <input id="{{ $prefix }}jurusan" name="jurusan" type="text" value="{{ $nilai('jurusan') }}"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}jenis"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis</label>
        <input id="{{ $prefix }}jenis" name="jenis" type="text" value="{{ $nilai('jenis') }}"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}guru_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wali
            Kelas
        </label>
        <select id="{{ $prefix }}guru_id" name="guru_id"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 max-md:min-h-11 max-md:text-base">
            <option value="">-Pilih Guru-</option>
            @foreach ($gurus as $guru)
                <option value="{{ $guru->id }}" @selected((string) $nilai('guru_id') === (string) $guru->id)>{{ $guru->nama }} ({{ $guru->nip }})</option>
            @endforeach
        </select>
    </div>
</div>
{{-- HP: bilah tombol menempel di bawah lembar modal agar selalu terjangkau --}}
<div class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 bg-white pt-4 dark:border-gray-700 dark:bg-gray-900 max-md:sticky max-md:bottom-0 max-md:z-10 max-md:-mx-4 max-md:-mb-4 max-md:grid max-md:grid-cols-2 max-md:px-4 max-md:pb-[max(1rem,env(safe-area-inset-bottom))]">
    <button type="button"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300 max-md:min-h-11"
        data-close-modal>{{ __('Batal') }}</button>
    <button type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 max-md:min-h-11 max-md:justify-center">
        {{ $mode === 'create' ? __('Simpan Data') : __('Perbarui Data') }}
    </button>
</div>
