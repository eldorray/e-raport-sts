<x-layouts.app>
    @php
        $logoUrl = null;
        if ($schoolProfile->logo) {
            $logoValue = $schoolProfile->logo;
            $logoUrl = filter_var($logoValue, FILTER_VALIDATE_URL)
                ? $logoValue
                : \Illuminate\Support\Facades\Storage::url($logoValue);
        }

        $initials = $schoolProfile->name ? mb_strtoupper(mb_substr($schoolProfile->name, 0, 2)) : 'SP';
    @endphp

    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Profil Sekolah') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Kelola identitas madrasah Anda agar data tetap selaras dengan sistem pusat.') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Kembali ke Dashboard') }}
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[360px,1fr]">
        <div class="space-y-6">
            <div
                class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                    <div class="absolute -top-14 -right-16 h-36 w-36 rounded-full bg-blue-100/70 dark:bg-blue-900/30">
                    </div>
                    <div
                        class="absolute -bottom-16 -left-10 h-40 w-40 rounded-full bg-indigo-100/70 dark:bg-indigo-900/30">
                    </div>
                </div>
                <div class="relative p-8">
                    <div class="flex items-start gap-4">
                        <div
                            class="relative h-24 w-24 overflow-hidden rounded-xl border border-white bg-white shadow-lg ring-2 ring-white/40 dark:border-gray-700 dark:bg-gray-900 dark:ring-gray-600/30">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $schoolProfile->name ?? __('Logo Sekolah') }}"
                                    class="h-full w-full object-cover">
                            @else
                                <div
                                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-blue-500 text-3xl font-semibold text-white">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {{ __('Profil Madrasah') }}</p>
                            <h2 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $schoolProfile->name ?? __('Belum diatur') }}</h2>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                {{ $schoolProfile->address ?? __('Alamat belum diatur') }}
                            </p>
                            @if ($schoolProfile->email)
                                <div
                                    class="mt-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                    </svg>
                                    <span>{{ $schoolProfile->email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-6 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('NSM') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $schoolProfile->nsm ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('NPSN') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $schoolProfile->npsn ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Kecamatan') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $schoolProfile->district ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Kota/Kabupaten') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $schoolProfile->city ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Provinsi') }}</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $schoolProfile->province ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="relative border-t border-gray-100 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-900/40">
                    <form action="{{ route('school-profile.update') }}" method="POST" enctype="multipart/form-data"
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="intent" value="logo">
                        <div class="flex items-center gap-3">
                            <input id="logo" name="logo" type="file" accept="image/*" class="sr-only"
                                onchange="this.form.submit()">
                            <label for="logo"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus-within:outline-none focus-within:ring-4 focus-within:ring-blue-500/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 4v16m0-16l-3 3m3-3l3 3" />
                                </svg>
                                {{ __('Unggah Logo') }}
                            </label>
                            <button type="submit" name="remove_logo" value="1"
                                class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-100 dark:border-red-700/60 dark:bg-red-900/30 dark:text-red-300 dark:hover:border-red-600/60">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-3h4l1 3" />
                                </svg>
                                {{ __('Hapus') }}
                            </button>
                        </div>
                        <button type="button" title="{{ __('Segera hadir') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 transition hover:border-indigo-300 hover:bg-indigo-50 dark:border-indigo-700/60 dark:bg-gray-900 dark:text-indigo-300 dark:hover:border-indigo-600/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Sinkron Profil') }}
                        </button>
                    </form>
                    @error('logo')
                        <p class="mt-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Keamanan Akun') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Perbarui password Anda secara berkala untuk menjaga keamanan akses.') }}</p>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-900 dark:text-gray-300">{{ __('Direkomendasikan') }}</span>
                </div>
                <div class="mt-5">
                    <a href="{{ route('settings.password.edit') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3zm0 0v8m-9 0h18" />
                        </svg>
                        {{ __('Kelola Password') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <form action="{{ route('school-profile.update') }}" method="POST"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                @csrf
                @method('PUT')
                <input type="hidden" name="intent" value="identity">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900/40">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Informasi Madrasah') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Data ini akan ditampilkan pada seluruh modul sekolah dan laporan resmi.') }}</p>
                </div>
                <div class="space-y-5 px-6 py-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Sekolah') }}</label>
                            <input id="name" name="name" type="text"
                                value="{{ old('name', $schoolProfile->name) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email Resmi') }}</label>
                            <input id="email" name="email" type="email"
                                value="{{ old('email', $schoolProfile->email) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="nsm"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('NSM') }}</label>
                            <input id="nsm" name="nsm" type="text"
                                value="{{ old('nsm', $schoolProfile->nsm) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('nsm')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="npsn"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('NPSN') }}</label>
                            <input id="npsn" name="npsn" type="text"
                                value="{{ old('npsn', $schoolProfile->npsn) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('npsn')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="address"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Alamat Lengkap') }}</label>
                        <textarea id="address" name="address" rows="3"
                            class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">{{ old('address', $schoolProfile->address) }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="district"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kecamatan') }}</label>
                            <input id="district" name="district" type="text"
                                value="{{ old('district', $schoolProfile->district) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('district')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="city"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Kota/Kabupaten') }}</label>
                            <input id="city" name="city" type="text"
                                value="{{ old('city', $schoolProfile->city) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="province"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Provinsi') }}</label>
                            <input id="province" name="province" type="text"
                                value="{{ old('province', $schoolProfile->province) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('province')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div
                    class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Simpan Perubahan') }}
                    </button>
                </div>
            </form>

            <form action="{{ route('school-profile.update') }}" method="POST"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                @csrf
                @method('PUT')
                <input type="hidden" name="intent" value="leadership">
                <div class="border-b border-gray-100 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900/40">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Data Pimpinan') }}</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Masukkan informasi Kepala Madrasah untuk kebutuhan administrasi dan laporan resmi.') }}
                    </p>
                </div>
                <div class="space-y-5 px-6 py-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="headmaster"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Nama Kepala Madrasah') }}</label>
                            <input id="headmaster" name="headmaster" type="text"
                                value="{{ old('headmaster', $schoolProfile->headmaster) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('headmaster')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nip_headmaster"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('NIP Kepala Madrasah') }}</label>
                            <input id="nip_headmaster" name="nip_headmaster" type="text"
                                value="{{ old('nip_headmaster', $schoolProfile->nip_headmaster) }}"
                                class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400">
                            @error('nip_headmaster')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div
                    class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('Simpan Data Pimpinan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
