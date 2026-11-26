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
    <div class="grid gap-3">
        <div>
            <label for="{{ $prefix }}nis"
                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">NIS</label>
            <input id="{{ $prefix }}nis" name="nis" type="text" value="{{ $nilai('nis') }}" required
                class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
        </div>
        <div>
            <label for="{{ $prefix }}nisn"
                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">NISN</label>
            <input id="{{ $prefix }}nisn" name="nisn" type="text" value="{{ $nilai('nisn') }}"
                class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
        </div>
        <div>
            <label for="{{ $prefix }}nama"
                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama</label>
            <input id="{{ $prefix }}nama" name="nama" type="text" value="{{ $nilai('nama') }}" required
                class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}gender"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Jenis Kelamin</label>
                <select id="{{ $prefix }}gender" name="jenis_kelamin" required
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
                    <option value="">-Pilih-</option>
                    <option value="L" @selected($nilai('jenis_kelamin') === 'L')>L</option>
                    <option value="P" @selected($nilai('jenis_kelamin') === 'P')>P</option>
                </select>
            </div>
            <div>
                <label for="{{ $prefix }}tempat"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tempat Lahir</label>
                <input id="{{ $prefix }}tempat" name="tempat_lahir" type="text" value="{{ $nilai('tempat_lahir') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}tanggal_lahir"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tanggal Lahir</label>
                <input id="{{ $prefix }}tanggal_lahir" name="tanggal_lahir" type="date" value="{{ $nilai('tanggal_lahir') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
            <div>
                <label for="{{ $prefix }}agama"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Agama</label>
                <input id="{{ $prefix }}agama" name="agama" type="text" value="{{ $nilai('agama') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}status_keluarga"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status Keluarga</label>
                <input id="{{ $prefix }}status_keluarga" name="status_keluarga" type="text" value="{{ $nilai('status_keluarga') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
            <div>
                <label for="{{ $prefix }}anak_ke"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Anak Ke</label>
                <input id="{{ $prefix }}anak_ke" name="anak_ke" type="number" value="{{ $nilai('anak_ke') }}" min="1"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}telpon"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Telpon Siswa</label>
                <input id="{{ $prefix }}telpon" name="telpon" type="text" value="{{ $nilai('telpon') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
            <div>
                <label for="{{ $prefix }}alamat"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Alamat Siswa</label>
                <textarea id="{{ $prefix }}alamat" name="alamat" rows="2"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">{{ $nilai('alamat') }}</textarea>
            </div>
        </div>
    </div>
    <div class="grid gap-3">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}sekolah_asal"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Sekolah Nama Asal</label>
                <input id="{{ $prefix }}sekolah_asal" name="sekolah_asal" type="text" value="{{ $nilai('sekolah_asal') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
            <div>
                <label for="{{ $prefix }}tanggal_diterima"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tanggal Diterima</label>
                <input id="{{ $prefix }}tanggal_diterima" name="tanggal_diterima" type="date" value="{{ $nilai('tanggal_diterima') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
        </div>
        <div>
            <label for="{{ $prefix }}kelas_diterima"
                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Terima di kelas</label>
            <input id="{{ $prefix }}kelas_diterima" name="kelas_diterima" type="text" value="{{ $nilai('kelas_diterima') }}"
                class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}nama_ayah"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama Ayah</label>
                <input id="{{ $prefix }}nama_ayah" name="nama_ayah" type="text" value="{{ $nilai('nama_ayah') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
            <div>
                <label for="{{ $prefix }}nama_ibu"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama Ibu</label>
                <input id="{{ $prefix }}nama_ibu" name="nama_ibu" type="text" value="{{ $nilai('nama_ibu') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}pekerjaan_ayah"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pekerjaan Ayah</label>
                <input id="{{ $prefix }}pekerjaan_ayah" name="pekerjaan_ayah" type="text" value="{{ $nilai('pekerjaan_ayah') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
            <div>
                <label for="{{ $prefix }}pekerjaan_ibu"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pekerjaan Ibu</label>
                <input id="{{ $prefix }}pekerjaan_ibu" name="pekerjaan_ibu" type="text" value="{{ $nilai('pekerjaan_ibu') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
        </div>
        <div>
            <label for="{{ $prefix }}alamat_orang_tua"
                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Alamat Orang Tua</label>
            <textarea id="{{ $prefix }}alamat_orang_tua" name="alamat_orang_tua" rows="2"
                class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">{{ $nilai('alamat_orang_tua') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}nama_wali"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama Wali</label>
                <input id="{{ $prefix }}nama_wali" name="nama_wali" type="text" value="{{ $nilai('nama_wali') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
            <div>
                <label for="{{ $prefix }}pekerjaan_wali"
                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pekerjaan Wali</label>
                <input id="{{ $prefix }}pekerjaan_wali" name="pekerjaan_wali" type="text" value="{{ $nilai('pekerjaan_wali') }}"
                    class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">
            </div>
        </div>
        <div>
            <label for="{{ $prefix }}alamat_wali"
                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Alamat Wali</label>
            <textarea id="{{ $prefix }}alamat_wali" name="alamat_wali" rows="2"
                class="isian mt-1 w-full px-3 py-2 text-sm max-md:min-h-11 max-md:text-base">{{ $nilai('alamat_wali') }}</textarea>
        </div>
        <div>
            <label for="{{ $prefix }}photo"
                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Foto</label>
            <input id="{{ $prefix }}photo" name="photo" type="file" accept="image/*"
                class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-accent focus:outline-none focus:ring-4 focus:ring-accent/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 max-md:min-h-11 max-md:text-base">
            @if ($mode === 'edit')
                <p class="mt-1 text-xs text-zinc-600">Kosongkan jika tidak mengubah foto.</p>
            @endif
        </div>
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
