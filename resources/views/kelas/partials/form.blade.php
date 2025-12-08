@php
    $prefix = $mode === 'edit' ? 'edit_' : 'create_';
@endphp
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="{{ $prefix }}nama_kelas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
            Kelas</label>
        <input id="{{ $prefix }}nama_kelas" name="nama" type="text" required
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
    </div>
    <div>
        <label for="{{ $prefix }}tingkat"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat</label>
        <input id="{{ $prefix }}tingkat" name="tingkat" type="text" required
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
    </div>
    <div>
        <label for="{{ $prefix }}jurusan"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jurusan</label>
        <input id="{{ $prefix }}jurusan" name="jurusan" type="text"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
    </div>
    <div>
        <label for="{{ $prefix }}jenis"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis</label>
        <input id="{{ $prefix }}jenis" name="jenis" type="text"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
    </div>
    <div>
        <label for="{{ $prefix }}guru_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wali
            Kelas
        </label>
        <select id="{{ $prefix }}guru_id" name="guru_id"
            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            <option value="">-Pilih Guru-</option>
            @foreach ($gurus as $guru)
                <option value="{{ $guru->id }}">{{ $guru->nama }} ({{ $guru->nip }})</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-4 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
    <button type="button"
        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300"
        data-close-modal>{{ __('Batal') }}</button>
    <button type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
        {{ $mode === 'create' ? __('Simpan Data') : __('Perbarui Data') }}
    </button>
</div>
