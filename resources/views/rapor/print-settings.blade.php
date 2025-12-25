<x-layouts.app>
    <div class="mb-8 flex flex-col gap-2">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">Pengaturan Cetak Rapor</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Atur lokasi, tanggal cetak, dan watermark yang dipakai
            saat mencetak rapor maupun ledger.</p>
    </div>

    <form action="{{ route('rapor.print-settings.update') }}" method="POST"
        class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @csrf
        <div class="space-y-2">
            <label for="tempat_cetak" class="text-sm font-semibold text-gray-800 dark:text-gray-100">Tempat
                Cetak</label>
            <input type="text" id="tempat_cetak" name="tempat_cetak"
                value="{{ old('tempat_cetak', optional($setting)->tempat_cetak) }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                placeholder="Tempat Cetak">
            <p class="text-xs text-gray-500 dark:text-gray-400">Isi dengan Nama Kabupaten atau kecamatan.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label for="tanggal_cetak" class="text-sm font-semibold text-gray-800 dark:text-gray-100">Tanggal
                    Cetak</label>
                <input type="date" id="tanggal_cetak" name="tanggal_cetak"
                    value="{{ old('tanggal_cetak', optional(optional($setting)->tanggal_cetak)->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
            <div class="space-y-2">
                <label for="tanggal_cetak_rapor" class="text-sm font-semibold text-gray-800 dark:text-gray-100">Tanggal
                    Cetak Rapor</label>
                <input type="date" id="tanggal_cetak_rapor" name="tanggal_cetak_rapor"
                    value="{{ old('tanggal_cetak_rapor', optional(optional($setting)->tanggal_cetak_rapor)->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </div>
        </div>

        <div class="space-y-2">
            <label for="watermark" class="text-sm font-semibold text-gray-800 dark:text-gray-100">Watermark</label>
            <input type="text" id="watermark" name="watermark"
                value="{{ old('watermark', optional($setting)->watermark) }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                placeholder="Watermark">
            <p class="text-xs text-gray-500 dark:text-gray-400">Kosongkan jika tidak menggunakan watermark di rapor.</p>
        </div>

        <div class="pt-2">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                Simpan
            </button>
        </div>
    </form>
</x-layouts.app>
