<x-layouts.app>
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('wali-kelas.siswa.index') }}"
                        class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ __('Kembali ke Daftar Siswa') }}
                    </a>
                </li>
            </ol>
        </nav>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ __('Detail Siswa') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $siswa->nama }} - {{ $kelas->nama }}</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Foto Siswa --}}
                <div class="flex flex-col items-center">
                    <div class="w-48 h-48 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-md">
                        <img src="{{ $siswa->photo_url }}" alt="{{ $siswa->nama }}" class="w-full h-full object-cover">
                    </div>
                    <div class="mt-4 text-center">
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $siswa->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-100' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $siswa->is_active ? __('Aktif') : __('Nonaktif') }}
                        </span>
                    </div>
                </div>

                {{-- Data Utama --}}
                <div class="md:col-span-2 space-y-6">
                    {{-- Identitas --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">
                            {{ __('Identitas Siswa') }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">NIS</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->nis }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">NISN</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->nisn ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Nama Lengkap') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->nama }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Jenis Kelamin') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Tempat Lahir') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->tempat_lahir ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Tanggal Lahir') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Agama') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->agama ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Status Keluarga') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->status_keluarga ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Anak Ke') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->anak_ke ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Telpon') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->telpon ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">
                            {{ __('Alamat') }}</h3>
                        <p class="text-sm text-gray-800 dark:text-gray-100">{{ $siswa->alamat ?? '-' }}</p>
                    </div>

                    {{-- Sekolah Asal --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">
                            {{ __('Informasi Penerimaan') }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Sekolah Asal') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->sekolah_asal ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Tanggal Diterima') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->tanggal_diterima?->translatedFormat('d F Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Diterima di Kelas') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->kelas_diterima ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Data Orang Tua --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">
                            {{ __('Data Orang Tua') }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Nama Ayah') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->nama_ayah ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Pekerjaan Ayah') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->pekerjaan_ayah ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Nama Ibu') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->nama_ibu ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Pekerjaan Ibu') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->pekerjaan_ibu ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Alamat Orang Tua') }}</span>
                            <p class="font-medium text-gray-800 dark:text-gray-100">
                                {{ $siswa->alamat_orang_tua ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Data Wali --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">
                            {{ __('Data Wali') }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Nama Wali') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->nama_wali ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Pekerjaan Wali') }}</span>
                                <p class="font-medium text-gray-800 dark:text-gray-100">
                                    {{ $siswa->pekerjaan_wali ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Alamat Wali') }}</span>
                            <p class="font-medium text-gray-800 dark:text-gray-100">{{ $siswa->alamat_wali ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Aksi --}}
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
                <a href="{{ route('rapor.print', ['siswa' => $siswa, 'tahun_ajaran_id' => session('selected_tahun_ajaran_id'), 'semester' => session('selected_semester')]) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    {{ __('Cetak Rapor') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
