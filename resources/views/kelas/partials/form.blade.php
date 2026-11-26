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
        <label for="{{ $prefix }}nama_kelas" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama
            Kelas</label>
        <input id="{{ $prefix }}nama_kelas" name="nama" type="text" value="{{ $nilai('nama') }}" required
            class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}tingkat"
            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tingkat</label>
        <input id="{{ $prefix }}tingkat" name="tingkat" type="text" value="{{ $nilai('tingkat') }}" required
            class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}jurusan"
            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Jurusan</label>
        <input id="{{ $prefix }}jurusan" name="jurusan" type="text" value="{{ $nilai('jurusan') }}"
            class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}jenis"
            class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Jenis</label>
        <input id="{{ $prefix }}jenis" name="jenis" type="text" value="{{ $nilai('jenis') }}"
            class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
    </div>
    <div>
        <label for="{{ $prefix }}guru_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Wali
            Kelas
        </label>
        <select id="{{ $prefix }}guru_id" name="guru_id"
            class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            <option value="">-Pilih Guru-</option>
            @foreach ($gurus as $guru)
                <option value="{{ $guru->id }}" @selected((string) $nilai('guru_id') === (string) $guru->id)>{{ $guru->nama }} ({{ $guru->nip }})</option>
            @endforeach
        </select>
    </div>
</div>
{{-- HP: bilah tombol menempel di bawah lembar modal agar selalu terjangkau --}}
<div class="mt-4 flex items-center justify-end gap-3 border-t border-zinc-100 bg-white pt-4 dark:border-zinc-700 dark:bg-zinc-900 max-md:sticky max-md:bottom-0 max-md:z-10 max-md:-mx-4 max-md:-mb-4 max-md:grid max-md:grid-cols-2 max-md:px-4 max-md:pb-[max(1rem,env(safe-area-inset-bottom))]">
    <button type="button"
        class="tombol tombol-netral px-4 py-2 text-sm font-semibold max-md:min-h-11"
        data-close-modal>{{ __('Batal') }}</button>
    <button type="submit"
        class="tombol tombol-aksen gap-2 px-5 py-2.5 text-sm font-semibold max-md:min-h-11">
        {{ $mode === 'create' ? __('Simpan Data') : __('Perbarui Data') }}
    </button>
</div>
