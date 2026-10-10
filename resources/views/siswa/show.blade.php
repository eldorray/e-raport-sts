<x-layouts.app>
    <div class="mb-6 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="break-words text-2xl font-bold text-gray-800 dark:text-gray-100 max-md:text-xl">{{ $siswa->nama }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Detail Siswa') }}</p>
        </div>
        <a href="{{ route('siswa.index') }}"
            class="shrink-0 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:text-gray-900 dark:border-gray-700 dark:text-gray-200 max-md:inline-flex max-md:min-h-11 max-md:items-center">{{ __('Kembali') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3 max-md:gap-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 max-md:p-5">
            <div class="flex flex-col items-center gap-4">
                <img src="{{ $siswa->photo_url }}" alt="Foto {{ $siswa->nama }}"
                    class="h-40 w-40 rounded-full object-cover border border-gray-200 dark:border-gray-700 max-md:h-28 max-md:w-28">
                <div class="text-center">
                    <p class="break-words text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->nama }}</p>
                    <p class="text-sm text-gray-500">{{ $siswa->nis }}{{ $siswa->nisn ? ' · ' . $siswa->nisn : '' }}
                    </p>
                </div>
            </div>
        </div>
        <div
            class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 max-md:p-5">
            {{-- HP: satu kolom agar label dan isi tidak berdesakan --}}
            <div class="grid grid-cols-1 gap-4 break-words text-sm text-gray-800 sm:grid-cols-2 dark:text-gray-100 max-md:gap-3">
                <div><span class="text-gray-500">{{ __('Jenis Kelamin') }}:</span> {{ $siswa->jenis_kelamin }}</div>
                <div><span class="text-gray-500">TTL:</span>
                    {{ $siswa->tempat_lahir }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}
                </div>
                <div><span class="text-gray-500">Kelas:</span> {{ optional($siswa->kelas)->nama ?? '—' }}</div>
                <div><span class="text-gray-500">Agama:</span> {{ $siswa->agama ?? '—' }}</div>
                <div><span class="text-gray-500">Status Keluarga:</span> {{ $siswa->status_keluarga ?? '—' }}</div>
                <div><span class="text-gray-500">Anak Ke:</span> {{ $siswa->anak_ke ?? '—' }}</div>
                <div><span class="text-gray-500">Telpon:</span> {{ $siswa->telpon ?? '—' }}</div>
                <div class="sm:col-span-2"><span class="text-gray-500">Alamat:</span> {{ $siswa->alamat ?? '—' }}</div>
                <div><span class="text-gray-500">Sekolah Asal:</span> {{ $siswa->sekolah_asal ?? '—' }}</div>
                <div><span class="text-gray-500">Tanggal Diterima:</span>
                    {{ $siswa->tanggal_diterima ? $siswa->tanggal_diterima->translatedFormat('d F Y') : '—' }}</div>
                <div><span class="text-gray-500">Terima di Kelas:</span> {{ $siswa->kelas_diterima ?? '—' }}</div>
                <div><span class="text-gray-500">Nama Ayah:</span> {{ $siswa->nama_ayah ?? '—' }}</div>
                <div><span class="text-gray-500">Nama Ibu:</span> {{ $siswa->nama_ibu ?? '—' }}</div>
                <div><span class="text-gray-500">Pekerjaan Ayah:</span> {{ $siswa->pekerjaan_ayah ?? '—' }}</div>
                <div><span class="text-gray-500">Pekerjaan Ibu:</span> {{ $siswa->pekerjaan_ibu ?? '—' }}</div>
                <div class="sm:col-span-2"><span class="text-gray-500">Alamat Orang Tua:</span>
                    {{ $siswa->alamat_orang_tua ?? '—' }}</div>
                <div><span class="text-gray-500">Nama Wali:</span> {{ $siswa->nama_wali ?? '—' }}</div>
                <div><span class="text-gray-500">Pekerjaan Wali:</span> {{ $siswa->pekerjaan_wali ?? '—' }}</div>
                <div class="sm:col-span-2"><span class="text-gray-500">Alamat Wali:</span>
                    {{ $siswa->alamat_wali ?? '—' }}</div>
            </div>
        </div>
    </div>
</x-layouts.app>
