@php
    $prefix = $mode === 'edit' ? 'edit_' : 'create_';
@endphp
<div class="grid gap-4 md:grid-cols-2">
    <div class="grid gap-3">
        <div>
            <label for="{{ $prefix }}nis"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIS</label>
            <input id="{{ $prefix }}nis" name="nis" type="text" required
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
        </div>
        <div>
            <label for="{{ $prefix }}nisn"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">NISN</label>
            <input id="{{ $prefix }}nisn" name="nisn" type="text"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
        </div>
        <div>
            <label for="{{ $prefix }}nama"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
            <input id="{{ $prefix }}nama" name="nama" type="text" required
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}gender"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin</label>
                <select id="{{ $prefix }}gender" name="jenis_kelamin" required
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    <option value="">-Pilih-</option>
                    <option value="L">L</option>
                    <option value="P">P</option>
                </select>
            </div>
            <div>
                <label for="{{ $prefix }}tempat"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tempat Lahir</label>
                <input id="{{ $prefix }}tempat" name="tempat_lahir" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}tanggal_lahir"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                <input id="{{ $prefix }}tanggal_lahir" name="tanggal_lahir" type="date"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
            <div>
                <label for="{{ $prefix }}agama"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agama</label>
                <input id="{{ $prefix }}agama" name="agama" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}status_keluarga"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Keluarga</label>
                <input id="{{ $prefix }}status_keluarga" name="status_keluarga" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
            <div>
                <label for="{{ $prefix }}anak_ke"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anak Ke</label>
                <input id="{{ $prefix }}anak_ke" name="anak_ke" type="number" min="1"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}telpon"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telpon Siswa</label>
                <input id="{{ $prefix }}telpon" name="telpon" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
            <div>
                <label for="{{ $prefix }}alamat"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Siswa</label>
                <textarea id="{{ $prefix }}alamat" name="alamat" rows="2"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
            </div>
        </div>
    </div>
    <div class="grid gap-3">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}sekolah_asal"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sekolah Nama Asal</label>
                <input id="{{ $prefix }}sekolah_asal" name="sekolah_asal" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
            <div>
                <label for="{{ $prefix }}tanggal_diterima"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Diterima</label>
                <input id="{{ $prefix }}tanggal_diterima" name="tanggal_diterima" type="date"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        </div>
        <div>
            <label for="{{ $prefix }}kelas_diterima"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Terima di kelas</label>
            <input id="{{ $prefix }}kelas_diterima" name="kelas_diterima" type="text"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}nama_ayah"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Ayah</label>
                <input id="{{ $prefix }}nama_ayah" name="nama_ayah" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
            <div>
                <label for="{{ $prefix }}nama_ibu"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Ibu</label>
                <input id="{{ $prefix }}nama_ibu" name="nama_ibu" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}pekerjaan_ayah"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan Ayah</label>
                <input id="{{ $prefix }}pekerjaan_ayah" name="pekerjaan_ayah" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
            <div>
                <label for="{{ $prefix }}pekerjaan_ibu"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan Ibu</label>
                <input id="{{ $prefix }}pekerjaan_ibu" name="pekerjaan_ibu" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        </div>
        <div>
            <label for="{{ $prefix }}alamat_orang_tua"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Orang Tua</label>
            <textarea id="{{ $prefix }}alamat_orang_tua" name="alamat_orang_tua" rows="2"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="{{ $prefix }}nama_wali"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Wali</label>
                <input id="{{ $prefix }}nama_wali" name="nama_wali" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
            <div>
                <label for="{{ $prefix }}pekerjaan_wali"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pekerjaan Wali</label>
                <input id="{{ $prefix }}pekerjaan_wali" name="pekerjaan_wali" type="text"
                    class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            </div>
        </div>
        <div>
            <label for="{{ $prefix }}alamat_wali"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Wali</label>
            <textarea id="{{ $prefix }}alamat_wali" name="alamat_wali" rows="2"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"></textarea>
        </div>
        <div>
            <label for="{{ $prefix }}photo"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto</label>
            <input id="{{ $prefix }}photo" name="photo" type="file" accept="image/*"
                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
            @if ($mode === 'edit')
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak mengubah foto.</p>
            @endif
        </div>
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
